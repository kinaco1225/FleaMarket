<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ItemStoreTest extends TestCase
{
  use RefreshDatabase;

  /**
   * 商品出品保存
   */
  public function test_user_can_register_item_with_required_information()
  {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    // storage を fake
    Storage::fake('public');

    $category = Category::factory()->create();

    // ダミー画像（保存処理だけ通す）
    $image = UploadedFile::fake()->create(
      'test.jpg',
      100,
      'image/jpeg'
    );

    $itemData = [
      'status' => 1,
      'name' => 'テスト商品',
      'brand' => 'テストブランド',
      'description' => '商品の説明です',
      'price' => 5000,
      'category_ids' => [$category->id],
      'image' => $image,
    ];

    $response = $this->post(route('items.store'), $itemData);

    // 出品後はリダイレクト
    $response->assertRedirect();

    // items テーブル確認
    $this->assertDatabaseHas('items', [
      'user_id' => $user->id,
      'name' => 'テスト商品',
      'brand' => 'テストブランド',
      'description' => '商品の説明です',
      'price' => 5000,
    ]);

    // 中間テーブル確認
    $this->assertDatabaseHas('category_item', [
      'category_id' => $category->id,
    ]);
  }
}
