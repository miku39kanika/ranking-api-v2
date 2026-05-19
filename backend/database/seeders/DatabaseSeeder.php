<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            RankingWithItemsSeeder::class,
            VotesTableSeeder::class,
            StaminasTableSeeder::class,
            CommentsTableSeeder::class,
            ItemsTableSeeder::class,
            UserItemsTableSeeder::class,
            CurrencySeeder::class,
            TagTableSeeder::class,
            AnnouncementSeeder::class,
            GiftSeeder::class,
            StarterItemsTableSeeder::class,
        ]);
    }
}
