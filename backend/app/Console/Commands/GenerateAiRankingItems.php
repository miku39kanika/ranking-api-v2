<?php

namespace App\Console\Commands;

use App\Models\Ranking;
use App\Models\RankingItem;
use App\Services\ContentFilterService;
use App\Services\GeminiRankingGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class GenerateAiRankingItems extends Command
{
    protected $signature = 'rankings:generate-ai-items
        {--limit=20 : 対象ランキング数}
        {--batch=5 : Gemini 1回あたりに処理するランキング数。無料枠対策で3〜5推奨}
        {--per-ranking=10 : 1ランキングあたりに追加する項目数}
        {--ranking-type=0 : 0=通常ランキング, 1=公式ランキング}
        {--user= : 特定のuser_idだけ対象にする。未指定なら実在ユーザー作成ランキング全体}
        {--exclude-user=bot_user : 除外するuser_id。空文字なら除外なし}
        {--only-empty : 項目が0件のランキングだけ対象}
        {--dry-run : DB保存せず表示だけする}';

    protected $description = 'Generate AI ranking items for existing rankings created by real users using Gemini API';

    public function handle(
        GeminiRankingGeneratorService $gemini,
        ContentFilterService $filter
    ): int {
        $limit = max(1, (int)$this->option('limit'));
        $batchSize = max(1, min((int)$this->option('batch'), 10));
        $perRanking = max(1, min((int)$this->option('per-ranking'), 20));
        $rankingType = (int)$this->option('ranking-type');
        $targetUserId = $this->option('user');
        $excludeUserId = (string)$this->option('exclude-user');
        $onlyEmpty = (bool)$this->option('only-empty');
        $dryRun = (bool)$this->option('dry-run');

        $query = Ranking::query()
            ->with(['items:id,ranking_id,name'])
            ->where('ranking_type', $rankingType)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'rankings.user_id');
            })
            ->orderBy('id');

        if ($targetUserId !== null && $targetUserId !== '') {
            $query->where('user_id', (string)$targetUserId);
        }

        if ($excludeUserId !== '') {
            $query->where('user_id', '!=', $excludeUserId);
        }

        if ($onlyEmpty) {
            $query->whereDoesntHave('items');
        }

        $rankings = $query->limit($limit)->get();

        if ($rankings->isEmpty()) {
            $this->warn('No target rankings found.');
            return self::SUCCESS;
        }

        $this->info("Target rankings: {$rankings->count()}");

        $created = 0;
        $skipped = 0;

        foreach ($rankings->chunk($batchSize) as $chunk) {
            $payload = $chunk->map(function (Ranking $ranking) {
                return [
                    'id' => $ranking->id,
                    'title' => $ranking->title,
                    'existing_items' => $ranking->items->pluck('name')->values()->all(),
                ];
            })->values()->all();

            $this->info('Gemini generating items for ranking ids: '.implode(', ', array_column($payload, 'id')));

            try {
                $generatedRankings = $gemini->generateItemsForRankings($payload, $perRanking);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
                return self::FAILURE;
            }

            $generatedByRankingId = collect($generatedRankings)
                ->keyBy(fn ($row) => (int)($row['ranking_id'] ?? 0));

            foreach ($chunk as $ranking) {
                $row = $generatedByRankingId->get((int)$ranking->id);

                if (!$row) {
                    $skipped++;
                    $this->warn("skip: Gemini returned no items for ranking_id={$ranking->id}");
                    continue;
                }

                $existingNames = $ranking->items
                    ->pluck('name')
                    ->map(fn ($name) => $this->normalizeComparable((string)$name))
                    ->all();

                $items = $this->normalizeItems($row['items'] ?? [], $perRanking, $filter, $existingNames);

                if (empty($items)) {
                    $skipped++;
                    $this->warn("skip: no valid items for ranking_id={$ranking->id} {$ranking->title}");
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY RUN] ranking_id={$ranking->id} {$ranking->title}");
                    $this->line('  add: '.implode(', ', $items));
                    $created += count($items);
                    continue;
                }

                DB::transaction(function () use ($ranking, $items, &$created) {
                    foreach ($items as $itemName) {
                        RankingItem::create([
                            'ranking_id' => $ranking->id,
                            'name' => $itemName,
                            'votes' => 0,
                            'aliases' => [],
                        ]);

                        $created++;
                    }
                });

                $this->info("created items for ranking_id={$ranking->id}: ".count($items));
            }
        }

        $this->info("AI item generation completed. created_items={$created}, skipped_rankings={$skipped}");

        return self::SUCCESS;
    }

    private function normalizeItems(mixed $values, int $limit, ContentFilterService $filter, array $existingComparableNames): array
    {
        if (!is_array($values)) {
            return [];
        }

        $normalized = [];
        $seen = array_fill_keys($existingComparableNames, true);

        foreach ($values as $value) {
            $text = trim((string)$value);

            if ($text === '') {
                continue;
            }

            if (mb_strlen($text) > 100) {
                $text = mb_substr($text, 0, 100);
            }

            if ($filter->containsNgWord($text)) {
                continue;
            }

            $key = $this->normalizeComparable($text);

            if ($key === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $text;

            if (count($normalized) >= $limit) {
                break;
            }
        }

        return $normalized;
    }

    private function normalizeComparable(string $value): string
    {
        $value = mb_strtolower(trim($value));
        return preg_replace('/\s+/u', '', $value) ?? $value;
    }
}
