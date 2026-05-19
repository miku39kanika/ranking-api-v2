<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Ranking;
use Illuminate\Support\Facades\DB;

class GenerateGameQuestions extends Command
{
    protected $signature = 'game:generate';

    public function handle()
    {
        // 今日の分削除
        DB::table('game_questions')->truncate();

        $count = 0;

        while ($count < 100) {

            $ranking = Ranking::with('items')
                ->inRandomOrder()
                ->first();

            if (!$ranking) break;

            $type = rand(1, 3);

            $question = null;

            if ($type === 1) {
                $question = $this->makeChoice($ranking);
            } elseif ($type === 2) {
                $question = $this->makeTop5($ranking);
            } else {
                $question = $this->makePercent($ranking);
            }

            if ($question !== null) {

                DB::table('game_questions')->insert([
                    'data' => json_encode($question),
                    'generated_date' => today(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $count++;
            }
        }

        $this->info("100問生成完了");
    }

    // ↓↓↓ここにGameControllerの中身コピペ↓↓↓

    private function makeChoice($ranking)
    {
        $items = $ranking->items->sortByDesc('votes')->values();

        if ($items->count() < 4) return null;

        // 上位からランダムに1つ選ぶ
        $baseIndex = rand(0, min(3, $items->count() - 2));

        // その近くのやつと比較
        $top = $items[$baseIndex];
        $second = $items[$baseIndex + 1];

        $isLeftCorrect = rand(0, 1);

        return [
            'id' => $ranking->id,
            'type' => 1,
            'rankingTitle' => $ranking->title,
            'leftItem' => [
                'id' => $isLeftCorrect ? $top->id : $second->id,
                'name' => $isLeftCorrect ? $top->name : $second->name
            ],
            'rightItem' => [
                'id' => $isLeftCorrect ? $second->id : $top->id,
                'name' => $isLeftCorrect ? $second->name : $top->name
            ],
            'correctAnswer' => $isLeftCorrect ? 'left' : 'right'
        ];
    }

    private function makeTop5($ranking)
    {
        $items = $ranking->items->sortByDesc('votes')->values();
        if ($items->count() < 5) return null;

        $choices = $items->take(10)->shuffle()->values();
        $correctIds = $items->take(5)->pluck('id');

        return [
            'id' => $ranking->id,
            'type' => 2,
            'rankingTitle' => $ranking->title,
            'choices' => $choices->map(fn($i) => [
                'id' => $i->id,
                'name' => $i->name
            ])->values(),
            'correctIds' => $correctIds
        ];
    }

    private function makePercent($ranking)
    {
        $items = $ranking->items;
        $totalVotes = $items->sum('votes');

        if ($items->count() < 5 || $totalVotes == 0) return null;

        $item = $items->random();
        $rate = $item->votes / $totalVotes;

        // 👇 ヒント生成
        $hints = [];

        // ① 順位ベース（rateから擬似順位を出す）
        $sorted = $items->sortByDesc('votes')->values();
        $rank = $sorted->search(fn($i) => $i->id === $item->id) + 1;

        if ($rank <= 2) {
            $hints[] = "上位に入りやすい項目";
        } elseif ($rank <= 4) {
            $hints[] = "中間あたりに位置することが多い";
        } else {
            $hints[] = "下位にいきやすい項目";
        }

        // ② 票の偏り（rateをぼかす）
        if ($rate > 0.3) {
            $hints[] = "票が集まりやすい傾向がある";
        } elseif ($rate > 0.15) {
            $hints[] = "安定して選ばれることが多い";
        } else {
            $hints[] = "人によって評価が分かれる";
        }

        // ③ ジャンル感（ランダムでもOK）
        $genreHints = [
            "イベント系の思い出",
            "日常寄りの出来事",
            "人によって印象が変わるタイプ",
            "定番と言われやすい項目",
            "話題に上がりやすい"
        ];
        $hints[] = $genreHints[array_rand($genreHints)];


        // ④ 全体との関係
        if ($items->count() >= 10) {
            $hints[] = "選択肢が多く競争が激しい";
        } else {
            $hints[] = "比較的シンプルなランキング";
        }


        // ⑤ ミスリード気味ヒント
        $trickyHints = [
            "知名度は高い",
            "意外と選ばれないこともある",
            "好きな人は強く支持する",
            "印象には残りやすい",
            "一部で人気が高い"
        ];
        $hints[] = $trickyHints[array_rand($trickyHints)];


        // 念のためシャッフル
        shuffle($hints);

        // ランキング全体の特徴ヒント
        $hints[] = "全体アイテム数: " . $items->count();

        return [
            'id' => $ranking->id,
            'type' => 3,
            'rankingTitle' => $ranking->title,
            'percentItems' => [[
                'id' => $item->id,
                'name' => $item->name,
                'voteRate' => $rate
            ]],
            'threshold' => rand(10, 30) / 100,
            'hints' => $hints // 👈 追加
        ];
    }
}
