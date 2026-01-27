<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Category;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::orderBy('id')->take(2)->get();

        if ($users->count() < 2) {
            throw new \Exception('ユーザーが2人以上必要です');
        }

        // ステータス名 → 数値の対応
        $statusMap = [
            '良好' => 1,
            '目立った傷や汚れなし' => 2,
            'やや傷や汚れあり' => 3,
            '状態が悪い' => 4,
        ];

        $items = [
            [
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'status' => '良好',
            ],
            [
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'status' => '目立った傷や汚れなし',
            ],
            [
                'name' => '玉ねぎ3束',
                'price' => 300,
                'brand' => null,
                'description' => '新鮮な玉ねぎ3束のセット',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'status' => 'やや傷や汚れあり',
            ],
            [
                'name' => '革靴',
                'price' => 4000,
                'brand' => null,
                'description' => 'クラシックなデザインの革靴',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'status' => '状態が悪い',
            ],
            [
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => null,
                'description' => '高性能なノートパソコン',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'status' => '良好',
            ],
            [
                'name' => 'マイク',
                'price' => 8000,
                'brand' => null,
                'description' => '高音質のレコーディング用マイク',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'status' => '目立った傷や汚れなし',
            ],
            [
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => null,
                'description' => 'おしゃれなショルダーバッグ',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'status' => 'やや傷や汚れあり',
            ],
            [
                'name' => 'タンブラー',
                'price' => 500,
                'brand' => null,
                'description' => '使いやすいタンブラー',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'status' => '状態が悪い',
            ],
            [
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'status' => '良好',
            ],
            [
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => null,
                'description' => '便利なメイクアップセット',
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'status' => '目立った傷や汚れなし',
            ],
        ];

        $categoryMap = [
            '腕時計' => 'ファッション',
            'HDD' => '家電',
            '玉ねぎ3束' => 'キッチン',
            '革靴' => 'ファッション',
            'ノートPC' => '家電',
            'マイク' => '家電',
            'ショルダーバッグ' => 'ファッション',
            'タンブラー' => 'キッチン',
            'コーヒーミル' => 'キッチン',
            'メイクセット' => 'コスメ',
        ];

        $half = ceil(count($items) / 2);

        foreach ($items as $index => $item) {

            $user = $index < $half ? $users[0] : $users[1];

            $categoryName = $categoryMap[$item['name']] ?? null;
            $categoryId = $categoryName
                ? Category::where('name', $categoryName)->value('id')
                : null;

            if (empty($item['image_url'])) {
                throw new \Exception("image_url が未設定です: {$item['name']}");
            }

            $filename = Str::random(40) . '.jpg';
            $imageData = file_get_contents($item['image_url']);

            if ($imageData === false) {
                throw new \Exception("画像取得失敗: {$item['image_url']}");
            }

            Storage::disk('public')->put('items/' . $filename, $imageData);
            $imagePath = 'items/' . $filename;

            $itemModel = Item::create([
                'user_id' => $user->id,
                'status' => $statusMap[$item['status']] ?? 1,
                'name' => $item['name'],
                'brand' => $item['brand'],
                'description' => $item['description'],
                'price' => $item['price'],
                'image_path' => $imagePath,
            ]);

            if ($categoryId) {
                $itemModel->categories()->attach($categoryId);
            }
        }
    }
}
