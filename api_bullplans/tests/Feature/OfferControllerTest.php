<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfferControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/offers';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
    }

    private function seedOffer(string $email, array|string $offers): void
    {
        DB::table('Offers')->insert([
            'email'  => $email,
            'offers' => is_array($offers) ? json_encode($offers, JSON_UNESCAPED_UNICODE) : $offers,
        ]);
    }

    public function test_get_offers_returns_empty_array_when_no_rows(): void
    {
        $res = $this->postAuthJson($this->base . '/get');

        $this->assertApiSuccess($res, 'Offers retrieved');

        $this->assertSame([], $res->json('data'));
    }

    public function test_get_offers_returns_map_email_to_offers(): void
    {
        $this->seedOffer('a@test.com', [['id' => 1]]);
        $this->seedOffer('b@test.com', [['id' => 2]]);

        $res = $this->postAuthJson($this->base . '/get');

        $this->assertApiSuccess($res, 'Offers retrieved');

        $data = $res->json('data');

        $this->assertSame(1, $data['a@test.com'][0]['id']);
        $this->assertSame(2, $data['b@test.com'][0]['id']);
    }

    public function test_post_offers_requires_payload_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/post');

        $this->assertApiValidation($res);
    }

    public function test_post_offers_saves_to_db(): void
    {
        $res = $this->postAuthJson($this->base . '/post', [
            'email'  => 't1@test.com',
            'offers' => [
                ['id' => 10, 'name' => 'X'],
            ],
        ]);

        $this->assertApiSuccess($res, 'Offers saved');

        $row = DB::table('Offers')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);

        $decoded = json_decode($row->offers, true);
        $this->assertSame(10, $decoded[0]['id']);
    }

    public function test_post_offers_updates_existing_row(): void
    {
        $this->seedOffer('t1@test.com', [['id' => 1]]);

        $res = $this->postAuthJson($this->base . '/post', [
            'email'  => 't1@test.com',
            'offers' => [
                ['id' => 99, 'name' => 'NEW'],
            ],
        ]);

        $this->assertApiSuccess($res, 'Offers saved');

        $rows = DB::table('Offers')->where('email', 't1@test.com')->get();
        $this->assertCount(1, $rows);

        $decoded = json_decode($rows->first()->offers, true);
        $this->assertSame(99, $decoded[0]['id']);
    }

    public function test_get_trainer_offers_requires_email_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/get/trainer');

        $this->assertApiValidation($res);
    }

    public function test_get_trainer_offers_returns_empty_array_when_missing(): void
    {
        $res = $this->postAuthJson($this->base . '/get/trainer', [
            'email' => 'missing@test.com',
        ]);

        $this->assertApiSuccess($res, 'Trainer offers retrieved');

        $this->assertSame([], $res->json('data'));
    }

    public function test_get_trainer_offers_returns_empty_array_when_offers_null(): void
    {
        $this->seedOffer('t1@test.com', []);

        $res = $this->postAuthJson($this->base . '/get/trainer', [
            'email' => 't1@test.com',
        ]);

        $this->assertApiSuccess($res, 'Trainer offers retrieved');

        $this->assertSame([], $res->json('data'));
    }

    public function test_get_trainer_offers_returns_list_when_exists(): void
    {
        $this->seedOffer('t1@test.com', [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);

        $res = $this->postAuthJson($this->base . '/get/trainer', [
            'email' => 't1@test.com',
        ]);

        $this->assertApiSuccess($res, 'Trainer offers retrieved');

        $res->assertJsonPath('data.0.id', 1);
        $res->assertJsonPath('data.1.id', 2);
    }
}
