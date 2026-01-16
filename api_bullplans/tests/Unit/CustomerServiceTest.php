<?php

namespace Tests\Unit;

use App\Modules\Customer\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
        $this->service = new CustomerService();
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

    public function test_get_customers_returns_empty_object_when_no_rows(): void
    {
        $res = $this->service->getCustomers('trainer@test.com');

        $this->assertIsObject($res);
        $this->assertSame([], (array) $res);
    }

    public function test_get_customers_maps_customer_email_to_normalized_customer_data(): void
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

        $res = $this->service->getCustomers('trainer@test.com');

        $this->assertIsArray($res);
        $this->assertArrayHasKey('c1@test.com', $res);
        $this->assertArrayHasKey('c2@test.com', $res);

        $this->assertTrue($res['c1@test.com']['activeCustomer']);

        // c2: service defaultok
        $this->assertFalse($res['c2@test.com']['activeCustomer']);
        $this->assertArrayHasKey('questionAndAnswer', $res['c2@test.com']);
        $this->assertArrayHasKey('trainingDays', $res['c2@test.com']);
        $this->assertIsObject($res['c2@test.com']['questionAndAnswer']);
        $this->assertIsObject($res['c2@test.com']['trainingDays']);
    }

    public function test_get_customers_handles_invalid_json_gracefully(): void
    {
        $this->seedCustomer('trainer@test.com', 'bad@test.com', '{invalid json');

        $res = $this->service->getCustomers('trainer@test.com');

        $this->assertIsArray($res);
        $this->assertArrayHasKey('bad@test.com', $res);

        $this->assertFalse($res['bad@test.com']['activeCustomer']);
        $this->assertIsObject($res['bad@test.com']['questionAndAnswer']);
        $this->assertIsObject($res['bad@test.com']['trainingDays']);
    }

    public function test_save_customer_inserts_when_missing(): void
    {
        $ok = $this->service->saveCustomer(
            'trainer@test.com',
            'c1@test.com',
            [
                'activeCustomer' => false,
                'questionAndAnswer' => (object)[],
                'trainingDays' => (object)[],
            ]
        );

        $this->assertTrue((bool) $ok);

        $this->assertSame(1, DB::table('Customers')->count());
    }

    public function test_save_customer_updates_when_exists_and_does_not_duplicate(): void
    {
        $this->seedCustomer('trainer@test.com', 'c1@test.com', [
            'activeCustomer' => false,
            'questionAndAnswer' => [],
            'trainingDays' => [],
        ]);

        $ok = $this->service->saveCustomer(
            'trainer@test.com',
            'c1@test.com',
            [
                'activeCustomer' => true,
                'questionAndAnswer' => ['q' => ['a']],
                'trainingDays' => ['2026-01-02' => ['finish' => 1]],
            ]
        );

        $this->assertTrue((bool) $ok);

        $rows = DB::table('Customers')
            ->where('trainer_email', 'trainer@test.com')
            ->where('customer_email', 'c1@test.com')
            ->get();

        $this->assertCount(1, $rows);

        $stored = json_decode($rows->first()->customer_data, true);
        $this->assertTrue($stored['activeCustomer']);
    }
}
