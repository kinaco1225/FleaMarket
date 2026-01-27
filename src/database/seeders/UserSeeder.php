<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name'        => 'テスト太郎',
            'email'       => 'test1@example.com',
            'password'    => Hash::make('password'),
            'postal_code' => '100-0001',
            'address'     => '東京都千代田区千代田1-1',
            'building'    => 'テストマンション101',
            'email_verified_at' => now(),
            'is_profile_completed' => true,
        ]);

        User::create([
            'name'        => 'テスト花子',
            'email'       => 'test2@example.com',
            'password'    => Hash::make('password'),
            'postal_code' => '150-0001',
            'address'     => '東京都渋谷区神宮前1-1-1',
            'building'    => 'サンプルビル2F',
            'email_verified_at' => now(),
            'is_profile_completed' => true,
        ]);
    }
}
