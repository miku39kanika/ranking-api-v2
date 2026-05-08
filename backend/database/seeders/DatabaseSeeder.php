<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
        UsersTableSeeder::class,
        // RankingsTableSeeder::class,
        // RankingItemsTableSeeder::class,
        RankingWithItemsSeeder::class,
        VotesTableSeeder::class,
        StaminasTableSeeder::class,
        CommentsTableSeeder::class,
        ItemsTableSeeder::class,
        UserItemsTableSeeder::class,
        CurrencySeeder::class,
       
]);
    }
}