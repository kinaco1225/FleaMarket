<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{

    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションエラーが表示される
     */
    public function test_email_is_required()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * パスワードが未入力の場合、バリデーションエラーが表示される
     */
    public function test_password_is_required()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * ログイン情報が間違っている場合、エラーメッセージが表示される
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    /**
     * 正しい情報でログインした場合、トップ画面に遷移する
     */
    public function test_user_can_login_and_redirect_to_top_page()
    {
        $password = 'password123';

        $user = User::factory()->create([
            'password' => bcrypt($password),
            'email_verified_at' => now(),        // ← メール認証済み
            'is_profile_completed' => true,      // ← プロフィール設定済み
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        // ログイン成功
        $this->assertAuthenticatedAs($user);

        // items.index（トップ）へ遷移
        $response->assertRedirect(route('items.index'));
    }
}
