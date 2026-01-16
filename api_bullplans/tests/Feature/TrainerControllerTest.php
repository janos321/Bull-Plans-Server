<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainerControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/trainer';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
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

    public function test_get_requires_existing_email_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/get');
        $this->assertApiValidation($res);

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'missing@test.com',
        ]);
        $this->assertApiValidation($res);
    }

    public function test_get_returns_trainer_when_exists(): void
    {
        $this->seedTrainer();

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 't1@test.com',
        ]);

        $this->assertApiSuccess($res, 'Trainer loaded.');

        $res->assertJsonStructure([
            'success', 'message',
            'data' => ['name', 'email', 'date', 'profile_data', 'valid_data'],
        ]);

        $data = $res->json('data');
        $this->assertSame('t1@test.com', $data['email']);
        $this->assertIsArray($data['profile_data']);
    }

    public function test_update_profile_data_requires_payload_validation(): void
    {
        $this->seedTrainer();

        $res = $this->putAuthJson($this->base . '/update/profileData', [
            'email' => 't1@test.com',
        ]);

        $this->assertApiValidation($res);
    }

    public function test_update_profile_data_updates_db(): void
    {
        $this->seedTrainer();

        $res = $this->putAuthJson($this->base . '/update/profileData', [
            'email' => 't1@test.com',
            'profile_data' => ['x' => 2],
        ]);

        $this->assertApiSuccess($res, 'Trainer profile updated.');

        $row = DB::table('Trainer')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(['x' => 2], json_decode($row->profile_data, true));
    }

    public function test_logout_requires_payload_validation(): void
    {
        $this->seedTrainer();

        $res = $this->putAuthJson($this->base . '/logout', [
            'email' => 't1@test.com',
        ]);

        $this->assertApiValidation($res);
    }

    public function test_logout_sets_login_zero_and_saves_profile_data(): void
    {
        $this->seedTrainer(['login' => 1]);

        $res = $this->putAuthJson($this->base . '/logout', [
            'email' => 't1@test.com',
            'profile_data' => ['bye' => true],
        ]);

        $this->assertApiSuccess($res, 'Trainer logged out.');

        $row = DB::table('Trainer')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);
        $this->assertSame(0, (int) $row->login);
        $this->assertSame(['bye' => true], json_decode($row->profile_data, true));
    }
}
