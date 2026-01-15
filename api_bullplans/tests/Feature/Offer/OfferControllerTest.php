<?php

namespace Tests\Feature\Offer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfferControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/offers';
    private array $authPayload;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.security_code', 'TEST_CODE');
        $this->authPayload = [
            'biztonsagiKod' => 'TEST_CODE',
        ];
    }

    private function seedOffer(string $email, array $offers): void
    {
        DB::table('Offers')->insert([
            'email'  => $email,
            'offers' => json_encode($offers, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function test_get_offers_returns_empty_object_when_no_rows(): void
    {
        $res = $this->postJson($this->base . '/get', $this->authPayload);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Offers retrieved');

        $data = $res->json('data');
        $this->assertIsArray($data);
        $this->assertSame([], $data);
    }

    public function test_get_offers_returns_map_email_to_offers(): void
    {
        $this->seedOffer('a@test.com', [['id' => 1]]);
        $this->seedOffer('b@test.com', [['id' => 2]]);

        $res = $this->postJson($this->base . '/get', $this->authPayload);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Offers retrieved');

        $res->assertJsonPath('data.a@test.com.0.id', 1);
        $res->assertJsonPath('data.b@test.com.0.id', 2);
    }

    public function test_post_offers_requires_payload_validation(): void
    {
        $res = $this->postJson($this->base . '/post', $this->authPayload);

        $res->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation error');
    }

    public function test_post_offers_saves_to_db(): void
    {
        $payload = array_merge($this->authPayload, [
            'email'  => 't1@test.com',
            'offers' => [
                ['id' => 10, 'name' => 'X'],
            ],
        ]);

        $res = $this->postJson($this->base . '/post', $payload);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Offers saved');

        $row = DB::table('Offers')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);

        $decoded = json_decode($row->offers, true);
        $this->assertSame(10, $decoded[0]['id']);
    }

    public function test_get_trainer_offers_requires_email_validation(): void
    {
        $res = $this->postJson($this->base . '/get/trainer', $this->authPayload);

        $res->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation error');
    }

    public function test_get_trainer_offers_returns_empty_array_when_missing(): void
    {
        $res = $this->postJson($this->base . '/get/trainer', array_merge($this->authPayload, [
            'email' => 'missing@test.com',
        ]));

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Trainer offers retrieved');

        $data = $res->json('data');
        $this->assertIsArray($data);
        $this->assertSame([], $data);
    }

    public function test_get_trainer_offers_returns_list_when_exists(): void
    {
        $this->seedOffer('t1@test.com', [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);

        $res = $this->postJson($this->base . '/get/trainer', array_merge($this->authPayload, [
            'email' => 't1@test.com',
        ]));

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Trainer offers retrieved')
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.1.id', 2);
    }
}
