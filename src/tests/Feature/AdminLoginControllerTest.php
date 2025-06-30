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
use Database\Seeders\DatabaseSeeder;


class AdminLoginControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    //3 ログイン認証機能（管理者）
     public function test_admin_login_メールアドレス必須バリデーション()
     {
        $response = $this->post('/admin/login',[
            'email' => '',
            'password' => '00000000',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $response->assertSessionHaserrors(['email' => 'メールアドレスを入力してください']);

    }

    public function test_admin_login_パスワード必須バリデーション()
     {
        $response = $this->post('/admin/login',[
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
        $response->assertSessionHaserrors(['password' => 'パスワードを入力してください']);

    }

    public function test_admin_login_パスワード不一致バリデーション()
     {
        $response = $this->post('/admin/login',[
            'email' => 'admin@example.com',
            'password' => '11111111',
        ]);

        $response->assertStatus(302);
        $errors = Session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first());
    }
}
