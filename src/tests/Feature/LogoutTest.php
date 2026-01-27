<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    
    use RefreshDatabase;

    public function test_user_can_logout()
    {
        /** @var \App\Models\User $user */
        // ① ログイン済みユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_profile_completed' => true,
        ]);

        // ② ログイン状態にする
        $this->actingAs($user);

        // ③ ログアウトボタン押下（POST /logout）
        $response = $this->post('/logout');

        // ④ ログアウトされていること
        $this->assertGuest();

        // ⑤ ログアウト後の遷移先（実装に合わせる）
        $response->assertRedirect('/');
    }
}
