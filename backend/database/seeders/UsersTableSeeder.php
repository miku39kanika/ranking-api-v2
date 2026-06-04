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

        $users = [

            [
                'id' => 'user_01',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => '世界一のヒトシ',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'ast01',
                'about_self' => 'そりゃもう世界一です',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 'user_02',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => 'らぶぶLOVE部部員',
                'device_id' => null,
                'email' => null,
                'plan_type' => 1,
                'icon_type' => 'asset',
                'icon_name' => 'ast02',
                'about_self' => 'らぶぶ人気なくなって寂しい',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 'user_03',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => '板チョコに腹筋ついてんのかい！',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'ast03',
                'about_self' => '33番！',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'user_04',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => 'Σ-SIGUMA-',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'book_default',
                'about_self' => '名前探し中',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 'user_05',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => '桃の天然水',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'game_default',
                'about_self' => 'お買い得',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 'user_06',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => '林のプーりん',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'sp01',
                'about_self' => 'ハチミツ食べたし',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 'user_07',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => 'ラルク',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'sp04',
                'about_self' => '心燃ゆ',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 'user_08',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => 'すぱはか',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'sp02',
                'about_self' => '001110011011101',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 'user_09',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => 'アリエルっティ',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'sp05',
                'about_self' => 'アリエルんだなそれが',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 'user_10',
                'public_id' => Str::random(10),
                'invite_code' => Str::random(10),
                'user_name' => 'アリエンティ',
                'device_id' => null,
                'email' => null,
                'plan_type' => 0,
                'icon_type' => 'asset',
                'icon_name' => 'sp01',
                'about_self' => 'ないない、あり得ない',
                'is_deleted' => false,
                'banned_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

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
            'invite_code' => 6245624234,
            'user_name' => "公式太郎",
            'device_id' => null,
            'email' => null,
            'plan_type' => 3,
            'icon_type' => 'asset',
            'icon_name' => "icon99",
            'about_self' => "公式の人です",
            'is_deleted' => false,
            'banned_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $users[] = [
            'id' => "bot_user",
            'public_id' => 9999999999,
            'invite_code' => 6275623464,
            'user_name' => "ボッスン",
            'device_id' => null,
            'email' => null,
            'plan_type' => 0,
            'icon_type' => 'asset',
            'icon_name' => "icon01",
            'about_self' => "どもです",
            'is_deleted' => false,
            'banned_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];


        DB::table('users')->insert($users);
    }
}
