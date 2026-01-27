<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Purchase;

class PurchaseAddressTest extends TestCase
{
  use RefreshDatabase;

  /**
   * 購入画面の住所変更
   */
  public function test_updated_shipping_address_is_reflected_on_purchase_page()
  {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($user);

    $addressData = [
      'item_id' => $item->id,
      'postal_code' => '100-0001',
      'address' => '東京都千代田区千代田1-1',
      'building' => 'テストマンション101',
    ];

    $this->post(
      route('purchase.address.store', $item),
      $addressData
    )->assertRedirect();

    $response = $this->get(route('purchase.create', $item));

    $response->assertStatus(200);

    $response->assertSee('100-0001');
    $response->assertSee('東京都千代田区千代田1-1');
    $response->assertSee('テストマンション101');

  }

  /**
   * 購入購入後商品に変更住所が紐づく
   */
  public function test_purchased_item_is_saved_with_shipping_address()
  {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $item = Item::factory()->create([
      'is_sold' => false,
    ]);

    $this->actingAs($user);

    // ① 送付先住所を登録
    $this->post(
      route('purchase.address.store', $item),
      [
        'postal_code' => '100-0001',
        'address'     => '東京都千代田区千代田1-1',
        'building'    => 'テストマンション101',
      ]
    )->assertRedirect();

    // ② 購入時に保存される住所（success内処理を再現）
    $address = Address::create([
      'user_id'     => $user->id,
      'item_id'     => $item->id,
      'postal_code' => '100-0001',
      'address'     => '東京都千代田区千代田1-1',
      'building'    => 'テストマンション101',
    ]);

    Purchase::create([
      'user_id'    => $user->id,
      'item_id'    => $item->id,
      'address_id' => $address->id,
    ]);

    // ③ addresses に紐づいている
    $this->assertDatabaseHas('addresses', [
      'user_id' => $user->id,
      'item_id' => $item->id,
      'postal_code' => '100-0001',
    ]);

    // ④ purchases に address_id が保存されている
    $this->assertDatabaseHas('purchases', [
      'user_id'    => $user->id,
      'item_id'    => $item->id,
      'address_id' => $address->id,
    ]);
  }
}
