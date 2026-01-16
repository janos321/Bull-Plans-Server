<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainingControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/training';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
    }

    private function seedTrainingRow(string $email, array|string|null $data): void
    {
        DB::table('TrainingData')->insert([
            'email' => $email,
            'training_data' => is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data,
            'updated_at' => now(),
        ]);
    }

    public function test_get_requires_email_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/get');

        $this->assertApiValidation($res);
    }

    public function test_get_returns_empty_object_when_missing_row(): void
    {
        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'missing@test.com',
        ]);

        $this->assertApiSuccess($res, 'Training data retrieved');

        $data = $res->json('data');
        $this->assertIsArray($data);
        $this->assertSame([], $data);
    }

    public function test_get_returns_empty_object_when_training_data_null(): void
    {
        $this->seedTrainingRow('u1@test.com', null);

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'u1@test.com',
        ]);

        $this->assertApiSuccess($res, 'Training data retrieved');
        $this->assertSame([], $res->json('data'));
    }

    public function test_get_returns_map_and_coerces_empty_values_to_objects(): void
    {
        $this->seedTrainingRow('u1@test.com', [
            'a' => [],
            'b' => ['x' => 1],
        ]);

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'u1@test.com',
        ]);

        $this->assertApiSuccess($res, 'Training data retrieved');

        $data = $res->json('data');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('a', $data);
        $this->assertArrayHasKey('b', $data);

        $this->assertSame([], $data['a']);
        $this->assertSame(1, $data['b']['x']);
    }

    public function test_put_requires_payload_validation(): void
    {
        $res = $this->putAuthJson($this->base . '/put', [
            'email' => 'u1@test.com',
        ]);

        $this->assertApiValidation($res);
    }

    public function test_put_inserts_new_row_and_returns_success(): void
    {
        $res = $this->putAuthJson($this->base . '/put', [
            'email' => 'u1@test.com',
            'training_data' => [
                'a' => ['x' => 1],
            ],
        ]);

        $this->assertApiSuccess($res, 'Training data saved');

        $this->assertSame(1, DB::table('TrainingData')->where('email', 'u1@test.com')->count());
    }

    public function test_put_updates_existing_row_and_does_not_duplicate(): void
    {
        $this->seedTrainingRow('u1@test.com', ['a' => ['x' => 1]]);

        $res = $this->putAuthJson($this->base . '/put', [
            'email' => 'u1@test.com',
            'training_data' => [
                'a' => ['x' => 99],
            ],
        ]);

        $this->assertApiSuccess($res, 'Training data saved');

        $this->assertSame(1, DB::table('TrainingData')->where('email', 'u1@test.com')->count());

        $row = DB::table('TrainingData')->where('email', 'u1@test.com')->first();
        $stored = json_decode($row->training_data, true);

        $this->assertSame(99, $stored['a']['x']);
    }
}
