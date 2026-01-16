<?php

namespace Tests\Unit;

use App\Modules\LoginReg\LoginRegService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRegServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoginRegService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoginRegService();
    }

    private function seedAdmin(string $name, string $plainPassword): void
    {
        DB::table('administrator')->insert([
            'name' => $name,
            'password' => Hash::make($plainPassword),
        ]);
    }

    private function seedTrainer(string $email, string $plainPassword, int $login = 0, array $validData = []): void
    {
        DB::table('Trainer')->insert([
            'name' => 'Trainer One',
            'email' => $email,
            'date' => '2025-01-01',
            'password' => Hash::make($plainPassword),
            'login' => $login,
            'profile_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'valid_data' => json_encode($validData, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedUser(string $email, string $plainPassword, int $login = 0, array $validData = []): void
    {
        DB::table('Users')->insert([
            'name' => 'User One',
            'email' => $email,
            'date' => '2025-01-01',
            'password' => Hash::make($plainPassword),
            'login' => $login,
            'profile_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'valid_data' => json_encode($validData, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_login_returns_admin_when_admin_ok(): void
    {
        $this->seedAdmin('admin@test.com', 'pw');

        $role = $this->service->login('admin@test.com', 'pw');

        $this->assertSame('admin', $role);
    }

    public function test_login_returns_trainer_and_sets_login_1(): void
    {
        $this->seedTrainer('t1@test.com', 'pw', 0);

        $role = $this->service->login('t1@test.com', 'pw');

        $this->assertSame('trainer', $role);

        $row = DB::table('Trainer')->where('email', 't1@test.com')->first();
        $this->assertSame(1, (int)$row->login);
    }

    public function test_login_returns_already_logged_in_for_trainer(): void
    {
        $this->seedTrainer('t1@test.com', 'pw', 1);

        $role = $this->service->login('t1@test.com', 'pw');

        $this->assertSame('already_logged_in', $role);
    }

    public function test_login_returns_user_and_sets_login_1(): void
    {
        $this->seedUser('u1@test.com', 'pw', 0);

        $role = $this->service->login('u1@test.com', 'pw');

        $this->assertSame('user', $role);

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertSame(1, (int)$row->login);
    }

    public function test_login_returns_invalid_when_missing_or_wrong_password(): void
    {
        $this->seedUser('u1@test.com', 'pw', 0);

        $this->assertSame('invalid', $this->service->login('u1@test.com', 'WRONG'));
        $this->assertSame('invalid', $this->service->login('missing@test.com', 'pw'));
    }

    public function test_check_email_returns_user_trainer_or_available(): void
    {
        $this->seedUser('u1@test.com', 'pw');
        $this->seedTrainer('t1@test.com', 'pw');

        $this->assertSame('user_exists', $this->service->checkEmail('u1@test.com'));
        $this->assertSame('trainer_exists', $this->service->checkEmail('t1@test.com'));
        $this->assertSame('available', $this->service->checkEmail('free@test.com'));
    }

    public function test_update_password_updates_both_tables_when_present(): void
    {
        $this->seedUser('u1@test.com', 'old');
        $this->seedTrainer('t1@test.com', 'old');

        $ok = $this->service->updatePassword('u1@test.com', 'new');
        $this->assertTrue($ok);

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertTrue(Hash::check('new', $row->password));
    }

    public function test_update_password_returns_false_when_email_missing_everywhere(): void
    {
        $ok = $this->service->updatePassword('missing@test.com', 'new');
        $this->assertFalse($ok);
    }

    public function test_register_user_inserts_and_normalizes_valid_data_keys(): void
    {
        $ok = $this->service->registerUser([
            'name' => 'User One',
            'email' => 'u1@test.com',
            'date' => '2025-01-01',
            'password' => 'pw',
            'profile_data' => [],
            'valid_data' => [],
        ]);

        $this->assertTrue($ok);

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(1, (int)$row->login);
        $this->assertTrue(Hash::check('pw', $row->password));

        $valid = json_decode($row->valid_data);
        $this->assertIsObject($valid);
        $this->assertObjectHasProperty('trainingDays', $valid);
        $this->assertObjectHasProperty('datas', $valid);
        $this->assertIsObject($valid->trainingDays);
        $this->assertIsObject($valid->datas);
    }

    public function test_register_trainer_inserts_row(): void
    {
        $ok = $this->service->registerTrainer([
            'name' => 'Trainer One',
            'email' => 't1@test.com',
            'date' => '2025-01-01',
            'password' => 'pw',
            'profile_data' => [],
            'valid_data' => [],
        ]);

        $this->assertTrue($ok);

        $row = DB::table('Trainer')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(1, (int)$row->login);
        $this->assertTrue(Hash::check('pw', $row->password));
    }
}
