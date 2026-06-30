<?php

namespace App\Console\Commands;

use App\Models\Ranking;
use App\Models\RankingItem;
use App\Models\Tag;
use App\Services\ContentFilterService;
use App\Services\GeminiRankingGeneratorService;
use App\Services\ReadingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class GenerateAiRankings extends Command
{
    protected $signature = 'rankings:generate-ai
        {--count=20 : 作成したいランキング数}
        {--batch=10 : Gemini 1回あたりの生成数。無料枠対策で5〜10推奨}
        {--theme=日本の一般ユーザーが気軽に投票できる話題 : 生成テーマ}
        {--user=bot_user : 作成者user_id}
        {--ranking-type=0 : 0=通常ランキング, 1=公式ランキング}
        {--with-reading : ReadingServiceで読み仮名も生成する}
        {--dry-run : DB保存せず表示だけする}';

    protected $description = 'Generate many public rankings and ranking items using Gemini API';

    public function handle(
        GeminiRankingGeneratorService $gemini,
        ContentFilterService $filter,
        ReadingService $readingService
    ): int {
        $targetCount = max(1, (int)$this->option('count'));
        $batchSize = max(1, min((int)$this->option('batch'), 20));
        $theme = (string)$this->option('theme');
        $userId = (string)$this->option('user');
        $rankingType = (int)$this->option('ranking-type');
        $withReading = (bool)$this->option('with-reading');
        $dryRun = (bool)$this->option('dry-run');

        $created = 0;
        $skipped = 0;

        while ($created < $targetCount) {
            $remaining = $targetCount - $created;
            $requestCount = min($batchSize, $remaining);

            $this->info("Gemini generating {$requestCount} rankings...");

            try {
                $candidates = $gemini->generate($requestCount, $theme);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
                return self::FAILURE;
            }

            foreach ($candidates as $candidate) {
                if ($created >= $targetCount) {
                    break;
                }

                $title = trim((string)($candidate['title'] ?? ''));
                $items = $this->normalizeStrings($candidate['items'] ?? [], 10, 100);
                $tags = $this->normalizeStrings($candidate['tags'] ?? [], 3, 30);

                if (!$this->isValidRanking($title, $items, $filter)) {
                    $skipped++;
                    $this->warn("skip invalid: {$title}");
                    continue;
                }

                if (Ranking::where('title', $title)->exists()) {
                    $skipped++;
                    $this->warn("skip duplicate: {$title}");
                    continue;
                }

                if ($dryRun) {
                    $created++;
                    $this->line("[DRY RUN] {$title}");
                    $this->line('  items: ' . implode(', ', $items));
                    $this->line('  tags: ' . implode(', ', $tags));
                    continue;
                }

                DB::transaction(function () use (
                    $title,
                    $items,
                    $tags,
                    $userId,
                    $rankingType,
                    $withReading,
                    $readingService,
                    &$created
                ) {
                    $ranking = Ranking::create([
                        'ranking_type' => $rankingType,
                        'title' => $title,
                        'reading' => $withReading ? $readingService->generate($title) : null,
                        'image_name' => null,
                        'image_type' => 'asset',
                        'image_path' => null,
                        'is_item_add_limited' => false,
                        'daily_vote_limit' => 1,
                        'total_vote_limit' => 10,
                        'vote_permission' => 'public_access',
                        'user_id' => $userId,
                        'invite_code' => Str::upper(Str::random(8)),
                    ]);

                    foreach ($items as $itemName) {
                        RankingItem::create([
                            'ranking_id' => $ranking->id,
                            'name' => $itemName,
                            'votes' => 0,
                            'aliases' => [],
                        ]);
                    }

                    $tagIds = [];

                    foreach ($tags as $tagName) {
                        $tag = Tag::firstOrCreate([
                            'name' => $tagName,
                        ]);

                        $tagIds[] = $tag->id;
                    }

                    $ranking->tags()->sync($tagIds);
                    $created++;
                });

                $this->info("created {$created}/{$targetCount}: {$title}");
            }

            if (empty($candidates)) {
                $this->warn('Gemini returned no candidates. Stop.');
                break;
            }
        }

        $this->info("AI ranking generation completed. created={$created}, skipped={$skipped}");

        return self::SUCCESS;
    }

    private function normalizeStrings(mixed $values, int $limit, int $maxLength): array
    {
        if (!is_array($values)) {
            return [];
        }

        $normalized = [];

        foreach ($values as $value) {
            $text = trim((string)$value);

            if ($text === '') {
                continue;
            }

            if (mb_strlen($text) > $maxLength) {
                $text = mb_substr($text, 0, $maxLength);
            }

            $normalized[] = $text;
        }

        return array_values(array_unique(array_slice($normalized, 0, $limit)));
    }

    private function isValidRanking(string $title, array $items, ContentFilterService $filter): bool
    {
        if ($title === '' || mb_strlen($title) > 30) {
            return false;
        }

        if (count($items) < 5) {
            return false;
        }

        if ($filter->containsNgWord($title)) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '' || $filter->containsNgWord($item)) {
                return false;
            }
        }

        return true;
    }
}
