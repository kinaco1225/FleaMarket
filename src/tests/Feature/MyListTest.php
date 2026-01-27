<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;

class MyListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * いいねした商品だけが表示される
     */
    public function test_only_liked_items_are_displayed()
    {
        /** @var \App\Models\User $user */
        
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // いいねした商品
        $likedItem = Item::factory()->create([
            'name' => 'いいね商品',
        ]);

        // いいねしていない商品
        $notLikedItem = Item::factory()->create([
            'name' => '未いいね商品',
        ]);

        // pivot（likes）にデータを作成
        $user->likes()->attach($likedItem->id);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertStatus(200);

        // いいねした商品は表示される
        $response->assertSee($likedItem->name);

        // いいねしていない商品は表示されない
        $response->assertDontSee($notLikedItem->name);
    }

    /**
     * 購入済み商品は SOLD と表示される
     */
    public function test_sold_item_has_sold_label_in_mylist()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $soldItem = Item::factory()->create([
            'name' => '売却済み商品',
            'is_sold' => true,
        ]);

        // いいね
        $user->likes()->attach($soldItem->id);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertStatus(200);

        $response->assertSee($soldItem->name);
        $response->assertSee('SOLD');
    }

    /**
     * 未ログインの場合は何も表示されない
     */
    public function test_guest_user_sees_nothing_in_mylist()
    {
        $item = Item::factory()->create([
            'name' => '商品A',
        ]);

        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);

        // 商品名が表示されない
        $response->assertDontSee($item->name);
    }
}
