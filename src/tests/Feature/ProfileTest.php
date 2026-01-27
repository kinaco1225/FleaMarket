<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class profileTest extends TestCase
{
  use RefreshDatabase;

  public function test_profile_edit_page_displays_initial_user_values()
  {
    /** @var \App\Models\User $user */
    $user = User::factory()->create([
        'name' => 'テストユーザー',
        'postal_code' => '100-0001',
        'address' => '東京都千代田区',
        'profile_image' => 'profiles/test.png',
    ]);

    $this->actingAs($user);
    
    $response = $this->get(route(('mypage.profile')));

    $response->assertStatus(200);

    $response->assertSee('value="テストユーザー"', false);
    $response->assertSee('value="東京都千代田区"', false);
    $response->assertSee('storage/profiles/test.png');

  }
}