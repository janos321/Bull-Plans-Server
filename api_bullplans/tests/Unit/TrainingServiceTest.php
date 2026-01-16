<?php

namespace Tests\Unit;

use App\Modules\Training\TrainingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainingServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrainingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrainingService();
    }

    private function seedTrainingRow(string $email, array|string|null $data): void
    {
        DB::table('TrainingData')->insert([
            'email' => $email,
            'training_data' => is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data,
            'updated_at' => now(),
        ]);
    }

    public function test_get_training_data_returns_empty_object_when_missing(): void
    {
        $res = $this->service->getTrainingData('missing@test.com');

        $this->assertIsObject($res);
        $this->assertSame([], (array)$res);
    }

    public function test_get_training_data_returns_empty_object_when_null(): void
    {
        $this->seedTrainingRow('u1@test.com', null);

        $res = $this->service->getTrainingData('u1@test.com');

        $this->assertIsObject($res);
        $this->assertSame([], (array)$res);
    }

    public function test_get_training_data_returns_empty_object_when_invalid_json(): void
    {
        $this->seedTrainingRow('u1@test.com', '{invalid json');

        $res = $this->service->getTrainingData('u1@test.com');

        $this->assertIsObject($res);
        $this->assertSame([], (array)$res);
    }

    public function test_get_training_data_coerces_empty_values_to_objects(): void
    {
        $this->seedTrainingRow('u1@test.com', [
            'a' => [],
            'b' => ['x' => 1],
        ]);

        $res = $this->service->getTrainingData('u1@test.com');

        $this->assertIsArray($res);

        $this->assertArrayHasKey('a', $res);
        $this->assertArrayHasKey('b', $res);

        $this->assertIsObject($res['a']);
        $this->assertSame([], (array)$res['a']);

        $this->assertSame(1, $res['b']['x']);
    }

    public function test_save_training_data_inserts_and_updates_without_duplication(): void
    {
        $ok = $this->service->saveTrainingData('u1@test.com', [
            'a' => ['x' => 1],
        ]);
        $this->assertTrue((bool)$ok);
        $this->assertSame(1, DB::table('TrainingData')->where('email', 'u1@test.com')->count());

        $ok = $this->service->saveTrainingData('u1@test.com', [
            'a' => ['x' => 99],
        ]);
        $this->assertTrue((bool)$ok);
        $this->assertSame(1, DB::table('TrainingData')->where('email', 'u1@test.com')->count());

        $row = DB::table('TrainingData')->where('email', 'u1@test.com')->first();
        $stored = json_decode($row->training_data, true);
        $this->assertSame(99, $stored['a']['x']);
    }
}
