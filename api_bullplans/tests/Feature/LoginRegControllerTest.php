<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRegControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/loginreg';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.security_code', 'TEST_CODE');

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    private function seedAdmin(string $name, string $plainPassword): void
    {
        DB::table('administrator')->insert([
            'name' => $name,
            'password' => Hash::make($plainPassword),
        ]);
    }

    private function seedTrainer(string $email, string $plainPassword, int $login = 0): void
    {
        DB::table('Trainer')->insert([
            'name' => 'Trainer One',
            'email' => $email,
            'date' => '2025-01-01',
            'password' => Hash::make($plainPassword),
            'login' => $login,
            'profile_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'valid_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedUser(string $email, string $plainPassword, int $login = 0): void
    {
        DB::table('Users')->insert([
            'name' => 'User One',
            'email' => $email,
            'date' => '2025-01-01',
            'password' => Hash::make($plainPassword),
            'login' => $login,
            'profile_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'valid_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_login_requires_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/login');

        $this->assertApiValidation($res);
    }

    public function test_login_returns_admin_when_admin_password_ok(): void
    {
        $this->seedAdmin('admin@test.com', 'pw');

        $res = $this->postAuthJson($this->base . '/login', [
            'email' => 'admin@test.com',
            'password' => 'pw',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', 'admin');
    }

    public function test_login_returns_trainer_and_sets_login_1(): void
    {
        $this->seedTrainer('t1@test.com', 'pw', 0);

        $res = $this->postAuthJson($this->base . '/login', [
            'email' => 't1@test.com',
            'password' => 'pw',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', 'trainer');

        $row = DB::table('Trainer')->where('email', 't1@test.com')->first();
        $this->assertSame(1, (int)$row->login);
    }

    public function test_login_returns_already_logged_in_for_trainer(): void
    {
        $this->seedTrainer('t1@test.com', 'pw', 1);

        $res = $this->postAuthJson($this->base . '/login', [
            'email' => 't1@test.com',
            'password' => 'pw',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', 'already_logged_in');
    }

    public function test_login_returns_user_and_sets_login_1(): void
    {
        $this->seedUser('u1@test.com', 'pw', 0);

        $res = $this->postAuthJson($this->base . '/login', [
            'email' => 'u1@test.com',
            'password' => 'pw',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', 'user');

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertSame(1, (int)$row->login);
    }

    public function test_login_returns_already_logged_in_for_user(): void
    {
        $this->seedUser('u1@test.com', 'pw', 1);

        $res = $this->postAuthJson($this->base . '/login', [
            'email' => 'u1@test.com',
            'password' => 'pw',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', 'already_logged_in');
    }

    public function test_login_returns_invalid_when_password_wrong_or_missing_user(): void
    {
        $this->seedUser('u1@test.com', 'pw', 0);

        $res = $this->postAuthJson($this->base . '/login', [
            'email' => 'u1@test.com',
            'password' => 'WRONG',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', 'invalid');
    }

    public function test_check_email_requires_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/email/check');
        $this->assertApiValidation($res);
    }

    public function test_check_email_returns_user_exists_trainer_exists_available(): void
    {
        $this->seedUser('u1@test.com', 'pw');
        $this->seedTrainer('t1@test.com', 'pw');

        $res = $this->postAuthJson($this->base . '/email/check', ['email' => 'u1@test.com']);
        $res->assertOk()->assertJsonPath('success', true)->assertJsonPath('data', 'user_exists');

        $res = $this->postAuthJson($this->base . '/email/check', ['email' => 't1@test.com']);
        $res->assertOk()->assertJsonPath('success', true)->assertJsonPath('data', 'trainer_exists');

        $res = $this->postAuthJson($this->base . '/email/check', ['email' => 'free@test.com']);
        $res->assertOk()->assertJsonPath('success', true)->assertJsonPath('data', 'available');
    }

    public function test_update_password_requires_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/password/update');
        $this->assertApiValidation($res);
    }

    public function test_update_password_updates_user_or_trainer(): void
    {
        $this->seedUser('u1@test.com', 'old');
        $this->seedTrainer('t1@test.com', 'old');

        $res = $this->postAuthJson($this->base . '/password/update', [
            'email' => 'u1@test.com',
            'new_password' => 'new',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', true);

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertTrue(Hash::check('new', $row->password));
    }

    public function test_register_user_requires_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/user/register');
        $this->assertApiValidation($res);
    }

    public function test_register_user_inserts_row_and_sets_login_1(): void
    {
        $res = $this->postAuthJson($this->base . '/user/register', [
            'name' => 'User One',
            'email' => 'u1@test.com',
            'date' => '2025-01-01',
            'password' => 'pw',
            'profile_data' => ['any' => 'value'],
            'valid_data' => [],
        ]);

        $res->assertOk()->assertJsonPath('success', true)->assertJsonPath('data', true);

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(1, (int)$row->login);
        $this->assertTrue(Hash::check('pw', $row->password));
    }

    public function test_register_trainer_requires_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/trainer/register');
        $this->assertApiValidation($res);
    }

    public function test_register_trainer_inserts_row_and_sets_login_1(): void
    {
        $res = $this->postAuthJson($this->base . '/trainer/register', [
            'name' => 'Trainer One',
            'email' => 't1@test.com',
            'date' => '2025-01-01',
            'password' => 'pw',
            'profile_data' => ['any' => 'value'],
            'valid_data' => [],
        ]);

        $res->assertOk()->assertJsonPath('success', true)->assertJsonPath('data', true);

        $row = DB::table('Trainer')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(1, (int)$row->login);
        $this->assertTrue(Hash::check('pw', $row->password));
    }
}
