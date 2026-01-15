<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/user';
    private array $authPayload;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.security_code', 'TEST_CODE');

        $this->authPayload = [
            'biztonsagiKod' => 'TEST_CODE',
        ];
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

    public function test_get_requires_existing_email(): void
    {
        $res = $this->postJson($this->base . '/get', $this->authPayload);
        $res->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation error');

        $res = $this->postJson($this->base . '/get', array_merge($this->authPayload, [
            'email' => 'missing@test.com',
        ]));
        $res->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_get_returns_user_when_exists(): void
    {
        $this->seedUser();

        $res = $this->postJson($this->base . '/get', array_merge($this->authPayload, [
            'email' => 'u1@test.com',
        ]));

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User loaded.')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['email', 'profile_data', 'valid_data'],
            ]);
    }

    public function test_logout_sets_login_zero_and_saves_profile_data(): void
    {
        $this->seedUser(['login' => 1]);

        $res = $this->putJson($this->base . '/logout', array_merge($this->authPayload, [
            'email' => 'u1@test.com',
            'profile_data' => ['x' => 2],
        ]));

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User logged out.');

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertSame(0, (int)$row->login);
        $this->assertSame(['x' => 2], json_decode($row->profile_data, true));
    }

    public function test_get_valid_data_requires_existing_email(): void
    {
        $res = $this->postJson($this->base . '/get/validData', $this->authPayload);
        $res->assertStatus(422)->assertJsonPath('success', false);

        $res = $this->postJson($this->base . '/get/validData', array_merge($this->authPayload, [
            'email' => 'missing@test.com',
        ]));
        $res->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_get_valid_data_returns_object_shapes(): void
    {
        $this->seedUser([
            'valid_data' => json_encode([
                'trainingDays' => [],
                'datas' => [],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $res = $this->postJson($this->base . '/get/validData', array_merge($this->authPayload, [
            'email' => 'u1@test.com',
        ]));

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Valid data loaded.')
            ->assertJsonStructure([
                'success', 'message',
                'data' => ['trainingDays', 'datas'],
            ]);

        $data = $res->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('trainingDays', $data);
        $this->assertArrayHasKey('datas', $data);
    }

    public function test_update_valid_data_requires_payload(): void
    {
        $this->seedUser();

        $res = $this->putJson($this->base . '/update/validData', $this->authPayload);
        $res->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_update_valid_data_updates_db(): void
    {
        $this->seedUser();

        $payload = [
            'trainingDays' => [],
            'datas' => [],
            'x' => 1,
        ];

        $res = $this->putJson($this->base . '/update/validData', array_merge($this->authPayload, [
            'email' => 'u1@test.com',
            'valid_data' => $payload,
        ]));

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Valid data updated.');

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $stored = json_decode($row->valid_data, true);

        $this->assertSame(1, $stored['x']);
        $this->assertArrayHasKey('trainingDays', $stored);
        $this->assertArrayHasKey('datas', $stored);
    }

    public function test_update_profile_data_requires_payload(): void
    {
        $this->seedUser();

        $res = $this->putJson($this->base . '/update/profileData', $this->authPayload);
        $res->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_update_profile_data_updates_db(): void
    {
        $this->seedUser();

        $res = $this->putJson($this->base . '/update/profileData', array_merge($this->authPayload, [
            'email' => 'u1@test.com',
            'profile_data' => ['a' => 'b'],
        ]));

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profile data updated.');

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertSame(['a' => 'b'], json_decode($row->profile_data, true));
    }

}
