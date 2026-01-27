<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class CommentTest extends TestCase
{
  use RefreshDatabase;

  /**
   * ログイン済みユーザーはコメントを送信できる
   */
  public function test_logged_in_user_can_post_comment()
  {
    /** @var \App\Models\User $user */

    $user = User::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($user);

    $comment = 'とても良い商品です';

    $response = $this->post(
      route('comments.store', $item),
      [
        'comment' => $comment,
      ]
    );

    $response->assertStatus(200);
    
    $this->assertDatabaseHas('comments', [
      'user_id' => $user->id,
      'item_id' => $item->id,
      'comment' => $comment,
    ]);

    $detailResponse = $this->get(route('items.show', $item));
    $detailResponse->assertSee($comment);
  }

  /**
   * ログイン前のユーザーはコメントを送信できない
   */
  public function test_guest_user_cannot_post_comment()
  {

    $item = Item::factory()->create();

    $comment = 'ゲストコメント';

    $response = $this->post(
      route('comments.store', $item),
      [
        'comment' => $comment,
      ]
    );

    $response->assertStatus(302);

    $this->assertDatabaseMissing('comments', [
      'item_id' => $item->id,
      'comment' => $comment,
    ]);
  }

  /**
   * コメント未入力の場合、バリデーションエラーになる
   */
  public function test_comment_is_required()
  {
    /** @var \App\Models\User $user */

    $user = User::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($user);

    $response = $this->post(
      route('comments.store', $item),
      ['comment' => '']
    );

    $response->assertStatus(422);

    $this->assertDatabaseCount('comments', 0);

  }

  /**
   * コメントが255文字を超える場合、バリデーションエラーになる
   */
  public function test_comment_must_not_exceed_255_characters()
  {
    /** @var \App\Models\User $user */

    $user = User::factory()->create();
    $item = Item::factory()->create();

    $this->actingAs($user);

    $longComment = str_repeat('a', 256);

    $response = $this->post(
      route('comments.store', $item),
      ['comment' => $longComment]
    );

    $response->assertStatus(422);

    $this->assertDatabaseCount('comments', 0);

  }
}
