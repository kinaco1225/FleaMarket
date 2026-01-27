<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MypageTest extends TestCase
{
  use RefreshDatabase;

  /**
   * プロフィールページに必要な情報が表示される
   */
  public function test_mypage_displays_user_profile_and_items()
  {
    /** @var \App\Models\User $user */

    $user = User::factory()->create([
      'name' => 'テストユーザー',
      'profile_image' => 'profiles/test.png',
    ]);

    $sellItem = Item::factory()->create([
      'user_id' => $user->id,
      'name' => '出品商品',
    ]);

    $buyItem = Item::factory()->create([
      'name' => '購入商品',
    ]);

    Purchase::factory()->create([
      'user_id' => $user->id,
      'item_id' => $buyItem->id,
    ]);

    $this->actingAs($user);

    // ① 出品ページ
    $response = $this->get(route('mypage'));
    $response->assertStatus(200);
    $response->assertSee('テストユーザー');
    $response->assertSee('storage/profiles/test.png');
    $response->assertSee($sellItem->name);
    $response->assertDontSee($buyItem->name);

    // ② 購入ページ
    $buyResponse = $this->get(route('mypage', ['page' => 'buy']));
    $buyResponse->assertStatus(200);
    $buyResponse->assertSee($buyItem->name);
    $buyResponse->assertDontSee($sellItem->name);

  }
}