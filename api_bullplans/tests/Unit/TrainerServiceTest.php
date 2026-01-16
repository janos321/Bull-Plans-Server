<?php

namespace Tests\Unit;

use App\Modules\Trainer\TrainerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainerServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrainerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrainerService();
    }

    private function seedTrainer(array $overrides = []): void
    {
        DB::table('Trainer')->insert(array_merge([
            'name'         => 'Trainer One',
            'email'        => 't1@test.com',
            'date'         => '2025-01-01',
            'password'     => 'x',
            'login'        => 1,
            'profile_data' => json_encode(['a' => 'b'], JSON_UNESCAPED_UNICODE),
            'valid_data'   => json_encode(['datas' => []], JSON_UNESCAPED_UNICODE),
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $overrides));
    }

    public function test_get_returns_null_when_missing(): void
    {
        $res = $this->service->get('missing@test.com');
        $this->assertNull($res);
    }

    public function test_get_returns_trainer_array_when_exists(): void
    {
        $this->seedTrainer();

        $res = $this->service->get('t1@test.com');

        $this->assertIsArray($res);
        $this->assertSame('Trainer One', $res['name']);
        $this->assertSame('t1@test.com', $res['email']);
        $this->assertSame('2025-01-01', $res['date']);
        $this->assertIsArray($res['profile_data']);
        $this->assertTrue(is_array($res['valid_data']) || is_object($res['valid_data']));
    }

    public function test_update_profile_data_updates_row(): void
    {
        $this->seedTrainer();

        $ok = $this->service->updateProfileData('t1@test.com', ['x' => 2]);
        $this->assertTrue((bool) $ok);

        $row = DB::table('Trainer')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(['x' => 2], json_decode($row->profile_data, true));
    }

    public function test_logout_sets_login_zero_and_updates_profile_data(): void
    {
        $this->seedTrainer(['login' => 1]);

        $ok = $this->service->logout('t1@test.com', ['bye' => true]);
        $this->assertTrue((bool) $ok);

        $row = DB::table('Trainer')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(0, (int) $row->login);
        $this->assertSame(['bye' => true], json_decode($row->profile_data, true));
    }
}
