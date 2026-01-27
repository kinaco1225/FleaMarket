<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;

class ItemIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 全商品が一覧に表示される
     */
    public function test_all_items_are_displayed_on_top_page()
    {
        $items = Item::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertStatus(200);

        foreach ($items as $item) {
            $response->assertSee($item->name);
        }
    }

    /* *
     * 購入済み商品には「Sold」と表示される
     */
    public function test_sold_item_has_sold_label()
    {
        Item::factory()->create([
            'name' => '未販売商品',
            'is_sold' => false,
        ]);

        Item::factory()->create([
            'name' => '売却済み商品',
            'is_sold' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertSee('売却済み商品');

        $response->assertSee('SOLD');

        $response->assertSee('未販売商品');
    }

    /**
     * 自分が出品した商品は一覧に表示されない
     */
    public function test_own_items_are_not_displayed()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_profile_completed' => true,
        ]);

        $ownItem = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '自分の商品',
        ]);

        $otherItem = Item::factory()->create([
            'name' => '他人の商品',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertDontSee($ownItem->name);

        $response->assertSee($otherItem->name);
    } 
}
