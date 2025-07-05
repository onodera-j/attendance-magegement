<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_認証メールの送信()
    {
        Notification::fake();
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'emailtest@example.com',
            'password' => '00000000',
            'password_confirmation' => '00000000',
        ]);

        $user = User::where('email', 'emailtest@example.com')->first();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertRedirect('/email/verify');
    }
}
