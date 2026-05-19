<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [

            [
                'name' => 'アニメ',
                'tag_image_name' => 'anime_default',
            ],
            [
                'name' => 'ゲーム',
                'tag_image_name' => 'game_default',
            ],
            [
                'name' => '映画',
                'tag_image_name' => 'movie_default',
            ],
            [
                'name' => '音楽',
                'tag_image_name' => 'music_default',
            ],
            [
                'name' => 'スポーツ',
                'tag_image_name' => 'sports_default',
            ],
            [
                'name' => '食べ物',
                'tag_image_name' => 'food_default',
            ],
            [
                'name' => '動物',
                'tag_image_name' => 'animal_default',
            ],
            [
                'name' => '漫画',
                'tag_image_name' => 'manga_default',
            ],
            [
                'name' => 'キャラクター',
                'tag_image_name' => 'character_default',
            ],
            [
                'name' => 'YouTube',
                'tag_image_name' => 'youtube_default',
            ],
            [
                'name' => 'Vtuber',
                'tag_image_name' => 'vtuber_default',
            ],
            [
                'name' => 'アイドル',
                'tag_image_name' => 'idol_default',
            ],
            [
                'name' => 'お笑い',
                'tag_image_name' => 'comedy_default',
            ],
            [
                'name' => '旅行',
                'tag_image_name' => 'travel_default',
            ],
            [
                'name' => '本',
                'tag_image_name' => 'book_default',
            ],
        ];

        foreach ($tags as $tag) {

            Tag::updateOrCreate(
                ['name' => $tag['name']],
                [
                    'tag_image_name' => $tag['tag_image_name']
                ]
            );
        }
    }
}
