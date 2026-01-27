<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\User;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品名で部分一致検索ができる
     */
    public function test_items_can_be_searched_by_partial_name()
    {
        $item1 = Item::factory()->create(['name' => 'iPhone 15']);
        $item2 = Item::factory()->create(['name' => 'iPhone ケース']);
        $item3 = Item::factory()->create(['name' => 'Android']);

        $response = $this->get('/?keyword=iPhone');

        $response->assertStatus(200);
        $response->assertSee($item1->name);
        $response->assertSee($item2->name);
        $response->assertDontSee($item3->name);
        $response->assertSee('name="keyword"', false);
        $response->assertSee('value="iPhone"', false);
    }

    public function test_search_keyword_is_kept_when_moving_to_mylist_tab()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create();

        // 他人の商品（検索ヒット）
        $hit = Item::factory()->create([
            'name' => 'iPhone 15',
        ]);

        // 他人の商品（検索ヒットしない）
        $miss = Item::factory()->create([
            'name' => 'Android',
        ]);

        // マイリスト登録
        $user->likedItems()->attach($hit->id);

        $this->actingAs($user);

        $keyword = 'iPhone';

        // ① ホームで検索
        $response = $this->get('/?tab=recommend&keyword=' . urlencode($keyword));
        $response->assertStatus(200);
        $response->assertSee($hit->name);
        $response->assertDontSee($miss->name);

        // ② マイリストへ遷移（keyword を保持）
        $mylistResponse = $this->get('/?tab=mylist&keyword=' . urlencode($keyword));
        $mylistResponse->assertStatus(200);

        // ③ マイリストでも検索条件が有効
        $mylistResponse->assertSee($hit->name);
        $mylistResponse->assertDontSee($miss->name);

        // ④ 検索キーワードが input に保持されている
        $mylistResponse->assertSee('name="keyword"', false);
        $mylistResponse->assertSee('value="' . e($keyword) . '"', false);
    }
}
