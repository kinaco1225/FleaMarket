<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Item;
use App\Models\user;
use App\Models\Category;
use App\Models\Comment;
use Symfony\Component\Routing\Route;

class ItemShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 商品詳細ページに必要な情報が表示される
     */
    public function test_item_detail_page_displays_required_information() 
    {
        $seller = User::factory()->create([
            'name' => '出品者ユーザー',
        ]);

        $commentUser = User::factory()->create([
            'name' => 'コメントユーザー',
        ]);

        $category = Category::factory()->create([
            'name' => '家電',
        ]);

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => 'テスト商品',
            'brand' => 'Apple',
            'price' => 10000,
            'description' => '商品の説明です',
            'status' => 1,
            'image_path' => 'items/test.png',
        ]);

        $item->categories()->attach($category->id);

        $likeUser1 = User::factory()->create();
        $likeUser2 = User::factory()->create();

        $likeUser1->likedItems()->attach($item->id);
        $likeUser2->likedItems()->attach($item->id);

        $comment = Comment::factory()->create([
            'item_id' => $item->id,
            'user_id' => $commentUser->id,
            'comment' => 'とても良い商品ですね！',
        ]);

        $response = $this->get(route('items.show', $item));

        $response->assertStatus(200);

        $response->assertSee($item->name);
        $response->assertSee($item->brand);
        $response->assertSee(number_format($item->price));
        $response->assertSee($item->description);
        $response->assertSee($item->image_path);

        $response->assertSee($category->name);

        $response->assertSee('2'); // いいね数
        $response->assertSee('1'); // コメント数

        $response->assertSee($commentUser->name);
        $response->assertSee($comment->content);

    }

    public function test_multiple_categories_are_displayed_on_item_detail_page()
    {
        // カテゴリ
        $category1 = Category::factory()->create(['name' => '家電']);
        $category2 = Category::factory()->create(['name' => 'スマホ']);

        // 商品
        $item = Item::factory()->create([
            'name' => 'テスト商品',
        ]);

        // 複数カテゴリを紐づけ
        $item->categories()->attach([
            $category1->id,
            $category2->id,
        ]);

        // 商品詳細ページを開く
        $response = $this->get(route('items.show', $item));

        $response->assertStatus(200);

        // 両方のカテゴリが表示されているか
        $response->assertSee($category1->name);
        $response->assertSee($category2->name);
    }

}
