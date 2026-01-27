<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class PurchaseTest extends TestCase
{
  use RefreshDatabase;
  
  /**
   * 「購入する」ボタンを押下すると購入が完了する
   */
  public function test_user_can_purchase_item()
  {/** @var \App\Models\User $user */
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create([
      'is_sold' => false,
    ]);
    $this->actingAs($user);

    session(['purchased_item_id' => $item->id]);
    $response = $this->get(
      route('purchase.success', ['session_id' => 'test'])
    );
    $response->assertRedirect();

    $this->assertDatabaseHas('purchases', [
      'user_id' => $user->id,
      'item_id' => $item->id,
    ]);

    $this->assertDatabaseHas('items', [
      'id' => $item->id,
      'is_sold' => true,
    ]);

  }


  /**
   * 購入した商品は商品一覧画面にて「sold」と表示される
   */
  public function test_purchased_item_is_marked_as_sold()
  {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create(['is_sold' => false]);

    $this->actingAs($user);

    session(['purchased_item_id' => $item->id]);

    $this->get(route('purchase.success', ['session_id' => 'test']))
      ->assertRedirect(route('items.index'));

    $this->assertDatabaseHas('items', [
      'id' => $item->id,
      'is_sold' => true,
    ]);
  }


  /**
   * 「プロフィール/購入した商品一覧」に追加されている
   */
  public function test_purchased_item_is_added_to_profile_purchase_list()
  {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create(['is_sold' => false]);

    $this->actingAs($user);

    session(['purchased_item_id' => $item->id]);

    $this->get(route('purchase.success', ['session_id' => 'test']));

    // マイページ（購入タブ）を確認
    $response = $this->get(route('mypage', ['page' => 'buy']));

    $response->assertStatus(200);
    $response->assertSee($item->name);
  }
}
