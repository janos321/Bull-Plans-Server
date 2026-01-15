<?php

namespace Tests\Unit\User;

use App\Modules\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedUserWithValidData(?string $validDataJson): void
    {
        DB::table('Users')->insert([
            'name'         => 'User One',
            'email'        => 'u1@test.com',
            'date'         => '2025-01-01',
            'password'     => 'x',
            'login'        => 1,
            'profile_data' => json_encode([], JSON_UNESCAPED_UNICODE),
            'valid_data'   => $validDataJson,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    public function test_get_valid_data_returns_empty_object_when_null(): void
    {
        $this->seedUserWithValidData(null);

        $service = new UserService();
        $res = $service->getValidData('u1@test.com');

        $this->assertIsObject($res);
        $this->assertSame([], (array)$res);
    }

    public function test_get_valid_data_coerces_empty_arrays_to_objects(): void
    {
        $this->seedUserWithValidData(json_encode([
            'trainingDays' => [],
            'datas' => [],
        ], JSON_UNESCAPED_UNICODE));

        $service = new UserService();
        $res = $service->getValidData('u1@test.com');

        $this->assertIsArray($res);
        $this->assertIsObject($res['trainingDays']);
        $this->assertIsObject($res['datas']);
    }

    public function test_update_valid_data_always_sets_trainingDays_and_datas_keys(): void
    {
        $this->seedUserWithValidData(json_encode([], JSON_UNESCAPED_UNICODE));

        $service = new UserService();
        $ok = $service->updateValidData('u1@test.com', ['x' => 1]);

        $this->assertTrue($ok);

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $stored = json_decode($row->valid_data, true);

        $this->assertSame(1, $stored['x']);
        $this->assertArrayHasKey('trainingDays', $stored);
        $this->assertArrayHasKey('datas', $stored);
    }
}
