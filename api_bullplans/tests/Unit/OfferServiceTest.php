<?php

namespace Tests\Unit;

use App\Modules\Offer\OfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfferServiceTest extends TestCase
{
    use RefreshDatabase;

    private OfferService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
        $this->service = new OfferService();
    }

    private function seedOffer(string $email, array|string $offers): void
    {
        DB::table('Offers')->insert([
            'email'  => $email,
            'offers' => is_array($offers) ? json_encode($offers, JSON_UNESCAPED_UNICODE) : $offers,
        ]);
    }

    public function test_get_offers_returns_empty_object_when_table_empty(): void
    {
        $res = $this->service->getOffers();

        $this->assertIsObject($res);
        $this->assertSame([], (array) $res);
    }

    public function test_save_offers_inserts_and_get_by_email_returns_decoded_array(): void
    {
        $offers = [
            ['id' => 1, 'name' => 'Offer1'],
            ['id' => 2, 'name' => 'Offer2'],
        ];

        $ok = $this->service->saveOffers('t1@test.com', $offers);
        $this->assertTrue((bool) $ok);

        $loaded = $this->service->getOffersByEmail('t1@test.com');

        $this->assertIsArray($loaded);
        $this->assertCount(2, $loaded);
        $this->assertSame('Offer1', $loaded[0]['name']);
    }

    public function test_save_offers_updates_existing_row(): void
    {
        $this->seedOffer('t1@test.com', [['id' => 1]]);

        $new = [['id' => 99, 'name' => 'NEW']];
        $ok = $this->service->saveOffers('t1@test.com', $new);

        $this->assertTrue((bool) $ok);

        $row = DB::table('Offers')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);

        $decoded = json_decode($row->offers, true);
        $this->assertSame(99, $decoded[0]['id']);
    }

    public function test_get_offers_maps_email_to_offers_array(): void
    {
        $this->seedOffer('a@test.com', [['id' => 1]]);
        $this->seedOffer('b@test.com', [['id' => 2]]);

        $res = $this->service->getOffers();

        $this->assertIsArray($res);

        $this->assertSame(1, $res['a@test.com'][0]['id']);
        $this->assertSame(2, $res['b@test.com'][0]['id']);
    }

    public function test_get_offers_by_email_returns_empty_array_when_missing(): void
    {
        $res = $this->service->getOffersByEmail('missing@test.com');
        $this->assertSame([], $res);
    }

    public function test_get_offers_by_email_returns_empty_array_when_offers_empty_json(): void
    {
        $this->seedOffer('x@test.com', '[]');

        $res = $this->service->getOffersByEmail('x@test.com');
        $this->assertSame([], $res);
    }

    public function test_get_offers_handles_invalid_json_gracefully(): void
    {
        // Előfordulhat adatkorrupt / kézi DB módosítás után
        $this->seedOffer('bad@test.com', '{invalid json');

        $res = $this->service->getOffers();

        // a service logikád szerint empty() -> (object)[]
        $this->assertIsArray($res);
        $this->assertArrayHasKey('bad@test.com', $res);
        $this->assertIsObject($res['bad@test.com']);
        $this->assertSame([], (array) $res['bad@test.com']);

        $byEmail = $this->service->getOffersByEmail('bad@test.com');
        $this->assertSame([], $byEmail);
    }
}
