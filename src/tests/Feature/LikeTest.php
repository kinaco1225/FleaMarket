<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * いいねアイコンを押すといいね登録される
     */
    public function test_user_can_like_an_item()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('likes.store',$item));

        $response->assertStatus(200);

        $this->assertDatabaseHas('likes',[
            'user_id' => $user->id,
            'item_id' => $item->id
        ]);

        $detailResponse = $this->get(route('items.show',$item));

        $detailResponse->assertSee('1');

    }

    /**
     * いいね済みの場合、いいねアイコンの状態が変わる
     */
    public function test_liked_item_icon_image_is_pink()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $this->post(route('likes.store', $item));

        $response = $this->get(route('items.show', $item));

        $response->assertSee('images/ハートロゴ_ピンク.png');

    }

    /**
     * いいね解除ができる
     */
    public function test_user_can_unlike_an_item()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user);

        $this->post(route('likes.store', $item));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->delete(route('likes.destroy', $item));

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->get(route('items.show', $item));

        $response->assertSee('images/ハートロゴ_デフォルト.png');
        
    }

}
