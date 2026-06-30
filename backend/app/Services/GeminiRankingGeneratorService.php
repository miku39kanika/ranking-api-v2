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
            throw new RuntimeException('Gemini API failed: '.$response->status().' '.$response->body());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (!$text) {
            throw new RuntimeException('Gemini API returned empty text.');
        }

        $decoded = json_decode($text, true);

        if (!is_array($decoded) || !isset($decoded['rankings']) || !is_array($decoded['rankings'])) {
            throw new RuntimeException('Gemini JSON parse failed: '.$text);
        }

        return $decoded['rankings'];
    }
}
