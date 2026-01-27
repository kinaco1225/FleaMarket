<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class RegisterTest extends TestCase
{
    // 各テスト実行前にDBをリセットする（テスト用DBを常に初期状態に保つ）
    use RefreshDatabase;

    /**
     * 名前が未入力の場合、バリテーションメッセージが表示されることを確認
     */
    public function test_name_is_required()
    {
        // 登録ボタン押下（POST送信）を再現
        $response = $this->post('/register', [
            'name' => '', // 名前を未入力
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // name に対して指定したバリテーションメッセージが出ているか確認
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /**
     * 名前が20文字を超えた場合、バリテーションメッセージが表示されることを確認
     */
    public function test_name_is_max()
    {
        $response = $this->post('/register', [
            'name' => 'aaaaaaaaaaaaaaaaaaaaaa', // 21文字
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前は20文字以内で入力してください',
        ]);
    }

    /**
     * メールアドレスの形式が正しくない場合、バリテーションメッセージが表示されることを確認
     */
    public function test_email_is_email()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'testexample.com', // メール形式ではない
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスはメール形式で入力してください',
        ]);
    }

    /**
     * メールアドレスが未入力の場合、バリテーションメッセージが表示されることを確認
     */
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => '', // 未入力
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /**
     * パスワードが未入力の場合、バリテーションメッセージが表示されることを確認
     */
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '', // 未入力
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * パスワードが8文字未満の場合、バリテーションメッセージが表示されることを確認
     */
    public function test_password_is_min()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'passwor', // 7文字
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /**
     * 確認用パスワードが未入力の場合、バリテーションメッセージが表示されることを確認
     */
    public function test_confirmation_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => '', // 未入力
        ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => '確認パスワードを入力してください',
        ]);
    }

    /**
     * 確認用パスワードが8文字未満の場合、バリテーションメッセージが表示されることを確認
     */
    public function test_confirmation_is_min()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'passwor',
            'password_confirmation' => 'passwor', // 7文字
        ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => '確認用パスワードは8文字以上で入力してください',
        ]);
    }

    /**
     * パスワードと確認用パスワードが一致しない場合、バリテーションメッセージが表示されることを確認
     */
    public function test_confirmation_is_same()
    {
        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password', // 不一致
        ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
        ]);
    }

    
    public function test_user_can_register_with_valid_data()
    {
        $email = 'test' . uniqid() . '@example.com';

        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // メール認証が有効ならここに飛ぶ
        $response->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name'  => 'テスト太郎',
        ]);
    }

    /**
     * 会員登録後に認証メール（通知）が送信されることを確認
     */
    public function test_verification_email_is_sent_after_register()
    {
        // 通知を実際には送信しない
        Notification::fake();

        // 会員登録を実行
        $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録されたユーザーを取得
        $user = User::where('email', 'test@example.com')->first();

        // 認証メール（VerifyEmail通知）が送信されたことを確認
        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /**
     * メール認証誘導画面が表示できることを確認
     */
    public function test_verification_notice_page_can_be_displayed()
    {
        // メール未認証ユーザーを作成
        $user = User::factory()->unverified()->create();

        // ログイン状態でメール認証画面にアクセス
        $response = $this->actingAs($user)->get('/email/verify');

        // 画面が表示可能（200 OK）であることを確認
        $response->assertStatus(200);
    }

    /**
     * メール認証を完了するとプロフィール設定画面に遷移することを確認
     */
    public function test_user_can_verify_email_and_redirect_to_profile()
    {
        // ① メール未認証ユーザーを作成
        $user = User::factory()->unverified()->create();

        // ② メール認証用のURLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        // ③ 認証URLにアクセス（＝メール内リンクをクリック）
        $response = $this->actingAs($user)->get($verificationUrl);

        // ④ メール認証が完了していることを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // ⑤ プロフィール設定画面にリダイレクトされることを確認
        $response->assertRedirect('/mypage/profile');
    }
}
