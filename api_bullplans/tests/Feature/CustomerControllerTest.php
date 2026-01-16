<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/customer';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
    }

    private function seedCustomer(string $trainerEmail, string $customerEmail, array|string $customerData): void
    {
        DB::table('Customers')->insert([
            'trainer_email'  => $trainerEmail,
            'customer_email' => $customerEmail,
            'customer_data'  => is_array($customerData) ? json_encode($customerData, JSON_UNESCAPED_UNICODE) : $customerData,
            'update_time'    => now(),
        ]);
    }

    public function test_get_requires_email_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/get');

        $this->assertApiValidation($res);
    }

    public function test_get_returns_empty_array_when_no_rows(): void
    {
        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'trainer@test.com',
        ]);

        $this->assertApiSuccess($res, 'Customers retrieved');

        $this->assertSame([], $res->json('data'));
    }

    public function test_get_returns_map_customer_email_to_customer_data(): void
    {
        $this->seedCustomer('trainer@test.com', 'c1@test.com', [
            'questionAndAnswer' => ['q1' => ['a1']],
            'activeCustomer' => true,
            'trainingDays' => ['2026-01-01' => ['finish' => 1]],
        ]);

        $this->seedCustomer('trainer@test.com', 'c2@test.com', [
            'questionAndAnswer' => [],
            'trainingDays' => [],
        ]);

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'trainer@test.com',
        ]);

        $this->assertApiSuccess($res, 'Customers retrieved');

        $data = $res->json('data');

        $this->assertArrayHasKey('c1@test.com', $data);
        $this->assertArrayHasKey('c2@test.com', $data);

        $this->assertTrue($data['c1@test.com']['activeCustomer']);

        $this->assertFalse($data['c2@test.com']['activeCustomer']);
        $this->assertIsArray($data['c2@test.com']);
        $this->assertArrayHasKey('questionAndAnswer', $data['c2@test.com']);
        $this->assertArrayHasKey('trainingDays', $data['c2@test.com']);
    }

    public function test_put_requires_payload_validation(): void
    {
        $res = $this->putAuthJson($this->base . '/put');

        $this->assertApiValidation($res);
    }

    public function test_put_inserts_new_customer_row(): void
    {
        $payload = [
            'questionAndAnswer' => (object)[],
            'trainingDays' => (object)[],
            'activeCustomer' => false,
        ];

        $res = $this->putAuthJson($this->base . '/put', [
            'trainer_email' => 'trainer@test.com',
            'customer_email' => 'c1@test.com',
            'customer_data' => $payload,
        ]);

        $this->assertApiSuccess($res, 'Customer saved');

        $this->assertSame(1, DB::table('Customers')->count());
    }

    public function test_put_updates_existing_customer_row(): void
    {
        $this->seedCustomer('trainer@test.com', 'c1@test.com', [
            'activeCustomer' => false,
            'questionAndAnswer' => [],
            'trainingDays' => [],
        ]);

        $res = $this->putAuthJson($this->base . '/put', [
            'trainer_email' => 'trainer@test.com',
            'customer_email' => 'c1@test.com',
            'customer_data' => [
                'activeCustomer' => true,
                'questionAndAnswer' => ['q' => ['a']],
                'trainingDays' => ['2026-01-02' => ['finish' => 1]],
            ],
        ]);

        $this->assertApiSuccess($res, 'Customer saved');

        $rows = DB::table('Customers')
            ->where('trainer_email', 'trainer@test.com')
            ->where('customer_email', 'c1@test.com')
            ->get();

        $this->assertCount(1, $rows);

        $stored = json_decode($rows->first()->customer_data, true);
        $this->assertTrue($stored['activeCustomer']);
        $this->assertSame('a', $stored['questionAndAnswer']['q'][0]);
    }
}
