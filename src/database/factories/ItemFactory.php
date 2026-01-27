<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),                 // 出品者
            'name' => $this->faker->word(),               // 商品名
            'description' => $this->faker->sentence(),    // 説明
            'price' => $this->faker->numberBetween(1000, 10000),
            'image_path' => 'dummy.jpg',                  // ← 必須なので固定でOK
            // status, brand, is_sold は省略可
        ];
    }
}
