<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/user';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
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

    public function test_get_requires_email_and_existing_user(): void
    {
        $res = $this->postAuthJson($this->base . '/get');
        $this->assertApiValidation($res);

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'missing@test.com',
        ]);
        $this->assertApiValidation($res);
    }

    public function test_get_returns_user_when_exists(): void
    {
        $this->seedUser();

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'u1@test.com',
        ]);

        $this->assertApiSuccess($res, 'User loaded.');

        $res->assertJsonStructure([
            'success',
            'message',
            'data' => ['email', 'profile_data', 'valid_data'],
        ]);

        $data = $res->json('data');
        $this->assertSame('u1@test.com', $data['email']);
    }

    public function test_logout_sets_login_zero_and_saves_profile_data(): void
    {
        $this->seedUser(['login' => 1]);

        $res = $this->putAuthJson($this->base . '/logout', [
            'email' => 'u1@test.com',
            'profile_data' => ['x' => 2],
        ]);

        $this->assertApiSuccess($res, 'User logged out.');

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(0, (int) $row->login);
        $this->assertSame(['x' => 2], json_decode($row->profile_data, true));
    }

    public function test_get_valid_data_requires_email_and_existing_user(): void
    {
        $res = $this->postAuthJson($this->base . '/get/validData');
        $this->assertApiValidation($res);

        $res = $this->postAuthJson($this->base . '/get/validData', [
            'email' => 'missing@test.com',
        ]);
        $this->assertApiValidation($res);
    }

    public function test_get_valid_data_returns_object_shapes(): void
    {
        $this->seedUser([
            'valid_data' => json_encode([
                'trainingDays' => [],
                'datas' => [],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $res = $this->postAuthJson($this->base . '/get/validData', [
            'email' => 'u1@test.com',
        ]);

        $this->assertApiSuccess($res, 'Valid data loaded.');

        $res->assertJsonStructure([
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

        $res = $this->putAuthJson($this->base . '/update/validData', [
            'email' => 'u1@test.com',
        ]);

        $this->assertApiValidation($res);
    }

    public function test_update_valid_data_updates_db_and_preserves_required_keys(): void
    {
        $this->seedUser();

        $payload = [
            'x' => 1,
        ];

        $res = $this->putAuthJson($this->base . '/update/validData', [
            'email' => 'u1@test.com',
            'valid_data' => $payload,
        ]);

        $this->assertApiSuccess($res, 'Valid data updated.');

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertNotNull($row);

        $stored = json_decode($row->valid_data, true);

        $this->assertSame(1, $stored['x']);
        $this->assertArrayHasKey('trainingDays', $stored);
        $this->assertArrayHasKey('datas', $stored);
    }

    public function test_update_profile_data_requires_payload(): void
    {
        $this->seedUser();

        $res = $this->putAuthJson($this->base . '/update/profileData', [
            'email' => 'u1@test.com',
        ]);

        $this->assertApiValidation($res);
    }

    public function test_update_profile_data_updates_db(): void
    {
        $this->seedUser();

        $res = $this->putAuthJson($this->base . '/update/profileData', [
            'email' => 'u1@test.com',
            'profile_data' => ['a' => 'b'],
        ]);

        $this->assertApiSuccess($res, 'Profile data updated.');

        $row = DB::table('Users')->where('email', 'u1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(['a' => 'b'], json_decode($row->profile_data, true));
    }
}
