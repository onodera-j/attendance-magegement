<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\UserSeeder;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    //2 ログイン認証（一般ユーザー）
     public function test_login_メールアドレス必須バリデーション()
     {
        $response = $this->post('/login',[
            'email' => '',
            'password' => '00000000',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $response->assertSessionHaserrors(['email' => 'メールアドレスを入力してください']);

    }

    public function test_login_パスワード必須バリデーション()
     {
        $response = $this->post('/login',[
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
        $response->assertSessionHaserrors(['password' => 'パスワードを入力してください']);

    }

    public function test_login_パスワード不一致バリデーション()
     {
        $response = $this->post('/login',[
            'email' => 'test@example.com',
            'password' => '11111111',
        ]);

        $response->assertStatus(302);
        $errors = Session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first());
    }
}
