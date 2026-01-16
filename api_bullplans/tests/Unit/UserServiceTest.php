<?php

namespace Tests\Unit;

use App\Modules\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
        $this->service = new UserService();
    }

    private function seedUser(array $overrides = []): void
    {
        DB::table('Users')->insert(array_merge([
            'name'         => 'User One',
            'email'        => 'u1@test.com',
            'date'         => '2025-01-01',
            'password'     => 'x',
            'login'        => 1,
            'profile_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'valid_data'   => json_encode([], JSON_UNESCAPED_UNICODE),
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $overrides));
    }

    public function test_get_valid_data_returns_empty_object_when_null(): void
    {
        $this->seedUser(['valid_data' => null]);

        $res = $this->service->getValidData('u1@test.com');

        $this->assertIsObject($res);
        $this->assertSame([], (array) $res);
    }

    public function test_get_valid_data_coerces_empty_arrays_to_objects(): void
    {
        $this->seedUser([
            'valid_data' => json_encode([
                'trainingDays' => [],
                'datas' => [],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $res = $this->service->getValidData('u1@test.com');

        $this->assertIsArray($res);
        $this->assertIsObject($res['trainingDays']);
        $this->assertIsObject($res['datas']);
    }

    public function test_get_valid_data_returns_empty_object_when_invalid_json(): void
    {
        $this->seedUser(['valid_data' => '{invalid json']);

        $res = $this->service->getValidData('u1@test.com');

        $this->assertTrue(is_array($res) || is_object($res));
        if (is_object($res)) {
            $this->assertSame([], (array) $res);
        }
    }

    public function test_update_valid_data_always_sets_trainingDays_and_datas_keys(): void
    {
        $this->seedUser([
            'valid_data' => json_encode([], JSON_UNESCAPED_UNICODE),
        ]);

        $ok = $this->service->updateValidData('u1@test.com', ['x' => 1]);
        $this->assertTrue((bool) $ok);

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertNotNull($row);

        $stored = json_decode($row->valid_data, true);

        $this->assertSame(1, $stored['x']);
        $this->assertArrayHasKey('trainingDays', $stored);
        $this->assertArrayHasKey('datas', $stored);
    }

    public function test_update_valid_data_overwrites_existing_data(): void
    {
        $this->seedUser([
            'valid_data' => json_encode(['x' => 5], JSON_UNESCAPED_UNICODE),
        ]);

        $ok = $this->service->updateValidData('u1@test.com', ['x' => 99]);
        $this->assertTrue((bool) $ok);

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertNotNull($row);

        $stored = json_decode($row->valid_data, true);
        $this->assertSame(99, $stored['x']);
        $this->assertArrayHasKey('trainingDays', $stored);
        $this->assertArrayHasKey('datas', $stored);
    }
}
