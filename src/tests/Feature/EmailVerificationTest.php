<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class EmailVerificationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::now());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

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

    public function test_認証メールの再送信()
    {
        Notification::fake();

        $user = User::factory()->create([
        'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post('/email/resend');
        $response->assertRedirect();
        $response->assertSessionHas('resent');
    }

    public function test_メール認証が完了で勤怠画面遷移()
    {
        Notification::fake();
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user);

        $response = $this->get($verifyUrl);

        $response->assertRedirect('/attendance');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}

