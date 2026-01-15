<?php

namespace Tests\Unit\Offer;

use App\Modules\Offer\OfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfferServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_offers_returns_empty_object_when_table_empty(): void
    {
        $service = new OfferService();

        $res = $service->getOffers();

        $this->assertIsObject($res);
        $this->assertSame([], (array)$res);
    }

    public function test_save_offers_inserts_and_get_by_email_returns_decoded_array(): void
    {
        $service = new OfferService();

        $offers = [
            ['id' => 1, 'name' => 'Offer1'],
            ['id' => 2, 'name' => 'Offer2'],
        ];

        $ok = $service->saveOffers('t1@test.com', $offers);
        $this->assertTrue((bool)$ok);

        $loaded = $service->getOffersByEmail('t1@test.com');
        $this->assertIsArray($loaded);
        $this->assertCount(2, $loaded);
        $this->assertSame('Offer1', $loaded[0]['name']);
    }

    public function test_save_offers_updates_existing_row(): void
    {
        $service = new OfferService();

        DB::table('Offers')->insert([
            'email'  => 't1@test.com',
            'offers' => json_encode([['id' => 1]], JSON_UNESCAPED_UNICODE),
        ]);

        $new = [['id' => 99, 'name' => 'NEW']];
        $ok = $service->saveOffers('t1@test.com', $new);

        $this->assertTrue((bool)$ok);

        $row = DB::table('Offers')->where('email', 't1@test.com')->first();
        $this->assertNotNull($row);

        $decoded = json_decode($row->offers, true);
        $this->assertSame(99, $decoded[0]['id']);
    }

    public function test_get_offers_maps_email_to_offers_array(): void
    {
        DB::table('Offers')->insert([
            'email'  => 'a@test.com',
            'offers' => json_encode([['id' => 1]], JSON_UNESCAPED_UNICODE),
        ]);

        DB::table('Offers')->insert([
            'email'  => 'b@test.com',
            'offers' => json_encode([['id' => 2]], JSON_UNESCAPED_UNICODE),
        ]);

        $service = new OfferService();
        $res = $service->getOffers();

        $this->assertIsArray($res);
        $this->assertArrayHasKey('a@test.com', $res);
        $this->assertArrayHasKey('b@test.com', $res);
        $this->assertSame(1, $res['a@test.com'][0]['id']);
        $this->assertSame(2, $res['b@test.com'][0]['id']);
    }

    public function test_get_offers_by_email_returns_empty_array_when_missing_or_null(): void
    {
        $service = new OfferService();

        $res = $service->getOffersByEmail('missing@test.com');
        $this->assertSame([], $res);

        DB::table('Offers')->insert([
            'email'  => 'x@test.com',
            'offers' => null,
        ]);

        $res = $service->getOffersByEmail('x@test.com');
        $this->assertSame([], $res);
    }
}
