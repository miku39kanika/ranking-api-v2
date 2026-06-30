<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiRankingGeneratorService
{
    public function generate(int $count, string $theme = '日本の一般ユーザーが気軽に投票できる話題'): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.0-flash');

        if (!$apiKey) {
            throw new RuntimeException('GEMINI_API_KEY is not set.');
        }

        $count = max(1, min($count, 20));

        $prompt = <<<PROMPT
あなたは日本語ランキングアプリ「なんでも！ランキング！」の公式コンテンツ編集者です。
ユーザーがすぐ投票したくなるランキングを {$count} 件作ってください。

テーマ: {$theme}

条件:
- title は30文字以内
- title は質問形または自然なランキング名
- items は5〜10個
- item name は100文字以内
- tags は1〜3個
- tags は短い日本語。例: 食べ物, 生活, アニメ, ゲーム, 恋愛, 学校, 仕事, 音楽, スポーツ, 地域, ネタ
- 成人向け、差別、誹謗中傷、個人名への攻撃、政治的に荒れやすい内容、犯罪助長、医療助言、投資助言は避ける
- 実在人物ランキングは避ける
- 同じようなランキングを重複させない
- 項目同士の粒度を揃える
PROMPT;

        $schema = [
            'type' => 'object',
            'properties' => [
                'rankings' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'tags' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                            'items' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                        'required' => ['title', 'tags', 'items'],
                    ],
                ],
            ],
            'required' => ['rankings'],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(60)
            ->retry(2, 1500)
            ->post($url, [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [[
                        'text' => $prompt,
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.9,
                    'response_mime_type' => 'application/json',
                    'response_schema' => $schema,
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gemini API failed: ' . $response->status() . ' ' . $response->body());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (!$text) {
            throw new RuntimeException('Gemini API returned empty text.');
        }

        $decoded = json_decode($text, true);

        if (!is_array($decoded) || !isset($decoded['rankings']) || !is_array($decoded['rankings'])) {
            throw new RuntimeException('Gemini JSON parse failed: ' . $text);
        }

        return $decoded['rankings'];
    }
    /**
     * 既存ランキングごとに不足/追加用の項目候補を生成します。
     *
     * @param array<int, array{id:int|string,title:string,existing_items:array<int,string>}> $rankings
     * @return array<int, array{ranking_id:int|string,items:array<int,string>}>
     */
    public function generateItemsForRankings(array $rankings, int $itemsPerRanking = 10): array
    {
        if (empty($rankings)) {
            return [];
        }

        $itemsPerRanking = max(1, min($itemsPerRanking, 20));

        $rankingLines = collect($rankings)
            ->map(function (array $ranking) use ($itemsPerRanking) {
                $id = $ranking['id'];
                $title = $ranking['title'];
                $need = max(1, (int)($ranking['need'] ?? $itemsPerRanking));
                $existingItems = $ranking['existing_items'] ?? [];
                $existing = empty($existingItems)
                    ? 'なし'
                    : implode(' / ', array_slice($existingItems, 0, 50));

                return "- ranking_id: {$id}\ntitle: {$title}\n need: {$need}\n existing_items: {$existing}";
            })
            ->implode("\n");

        $prompt = <<<PROMPT
あなたは日本語ランキングアプリ「なんでも！ランキング！」の編集者です。
以下の既存ランキングに対して、ユーザーが投票しやすい追加項目を作ってください。
各ランキングには need が書かれています。
need の数だけ items を返してください。

対象ランキング:
{$rankingLines}

条件:
- 各 ranking_id に対して、need の数だけ items を返す
- 既存項目と重複しない
- 同じランキング内で項目を重複させない
- item name は100文字以内
- 項目同士の粒度を揃える
- ランキングタイトルの意図から外れない
- 日本の一般ユーザーが理解できる項目にする
- 成人向け、差別、誹謗中傷、個人名への攻撃、政治的に荒れやすい内容、犯罪助長、医療助言、投資助言は避ける
- 実在人物名、実在の個人への評価項目は避ける
PROMPT;

        $schema = [
            'type' => 'object',
            'properties' => [
                'rankings' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'ranking_id' => ['type' => 'integer'],
                            'items' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                        'required' => ['ranking_id', 'items'],
                    ],
                ],
            ],
            'required' => ['rankings'],
        ];

        $text = $this->requestJsonText($prompt, $schema, 0.7);
        $decoded = json_decode($text, true);

        if (!is_array($decoded) || !isset($decoded['rankings']) || !is_array($decoded['rankings'])) {
            throw new RuntimeException('Gemini JSON parse failed: ' . $text);
        }

        return $decoded['rankings'];
    }

    private function requestJsonText(string $prompt, array $schema, float $temperature): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (!$apiKey) {
            throw new RuntimeException('GEMINI_API_KEY is not set.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(60)
            ->retry(2, 1500)
            ->post($url, [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [[
                        'text' => $prompt,
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'response_mime_type' => 'application/json',
                    'response_schema' => $schema,
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gemini API failed: ' . $response->status() . ' ' . $response->body());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (!$text) {
            throw new RuntimeException('Gemini API returned empty text.');
        }

        return $text;
    }
}
