<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;
class TagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::insert([
    ['name' => 'ゲーム'],
    ['name' => 'アニメ'],
    ['name' => '映画'],
    ['name' => '音楽'],
    ['name' => 'スポーツ'],
]);
    }
}
