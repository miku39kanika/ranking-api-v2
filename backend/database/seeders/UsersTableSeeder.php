<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $iconNames = ['ast01', 'ast02', 'ast03', 'icon99'];

        $users = [];

        // user_01 ~ user_10
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'id' => sprintf('user_%02d', $i),
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => "ユーザー{$i}",
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => $iconNames[array_rand($iconNames)],
                'about_self' => "自己紹介{$i}",
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 削除ユーザー
        $users[] = [
            'id' => 'user_del',
            'public_id' => Str::random(10),
            'invite_code' => Str::random(10),
            'user_name' => '削除ユーザー',
            'device_id' => null,
            'email' => null,
            'plan_type' => 0,
            'icon_type' => 'asset',
            'icon_name' => $iconNames[array_rand($iconNames)],
            'about_self' => 'このユーザーは削除状態です',
            'is_deleted' => true,
            'banned_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // BANユーザー
        $users[] = [
            'id' => 'user_ban',
            'public_id' => Str::random(10),
            'invite_code' => Str::random(10),
            'user_name' => 'BANユーザー',
            'device_id' => null,
            'email' => null,
            'plan_type' => 0,
            'icon_type' => 'asset',
            'icon_name' => $iconNames[array_rand($iconNames)],
            'about_self' => 'このユーザーはBANされています',
            'is_deleted' => false,
            'banned_at' => Carbon::now()->subDays(3),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $users[] = [
            'id' => "user_99",
            'public_id' => 0000000000,
            'invite_code' => 0000000000,
            'user_name' => "公式太郎",
            'device_id' => null,
            'email' => null,
            'plan_type' => 0,
            'icon_type' => 'asset',
            'icon_name' => "icon99",
            'about_self' => "公式の人です",
            'is_deleted' => false,
            'banned_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('users')->insert($users);
    }
}
