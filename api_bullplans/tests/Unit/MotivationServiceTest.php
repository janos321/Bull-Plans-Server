<?php

namespace Tests\Unit;

use App\Modules\Motivation\MotivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MotivationServiceTest extends TestCase
{
    use RefreshDatabase;

    private MotivationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
        $this->service = new MotivationService();
    }

    private function seedMotivation(string $date, array $translations): void
    {
        DB::table('Motivations')->insert([
            'dateTime'    => $date,
            'motivation'  => json_encode($translations, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function test_get_today_motivation_returns_null_when_table_empty(): void
    {
        $res = $this->service->getTodayMotivation('hu');
        $this->assertNull($res);
    }

    public function test_get_today_motivation_returns_today_translation_when_exists(): void
    {
        $today = now()->toDateString();
        $this->seedMotivation($today, ['hu' => 'Ma HU', 'en' => 'Today EN']);

        $res = $this->service->getTodayMotivation('hu');
        $this->assertSame('Ma HU', $res);
    }

    public function test_get_today_motivation_falls_back_to_any_translation_when_lang_missing(): void
    {
        $today = now()->toDateString();
        $this->seedMotivation($today, ['en' => 'Only EN']);

        $res = $this->service->getTodayMotivation('hu');
        $this->assertSame('Only EN', $res);
    }

    public function test_get_today_motivation_uses_latest_past_when_today_missing(): void
    {
        $yesterday = now()->subDay()->toDateString();
        $this->seedMotivation($yesterday, ['hu' => 'Tegnap HU']);

        $res = $this->service->getTodayMotivation('hu');
        $this->assertSame('Tegnap HU', $res);
    }

    public function test_get_today_motivation_uses_earliest_when_only_future_exists(): void
    {
        $tomorrow = now()->addDay()->toDateString();
        $after = now()->addDays(2)->toDateString();

        $this->seedMotivation($after, ['hu' => 'Később']);
        $this->seedMotivation($tomorrow, ['hu' => 'Holnap']);

        $res = $this->service->getTodayMotivation('hu');
        $this->assertSame('Holnap', $res);
    }

    public function test_store_new_motivation_inserts_for_today_when_empty_table(): void
    {
        $ok = $this->service->storeNewMotivation(['hu' => 'Ma']);
        $this->assertTrue((bool) $ok);

        $row = DB::table('Motivations')->orderByDesc('dateTime')->first();
        $this->assertNotNull($row);
        $this->assertSame(now()->toDateString(), $row->dateTime);

        $decoded = json_decode($row->motivation, true);
        $this->assertSame('Ma', $decoded['hu']);
    }

    public function test_store_new_motivation_inserts_for_tomorrow_when_last_is_today(): void
    {
        $today = now()->toDateString();
        $this->seedMotivation($today, ['hu' => 'Régi']);

        $ok = $this->service->storeNewMotivation(['hu' => 'Új']);
        $this->assertTrue((bool) $ok);

        $row = DB::table('Motivations')->orderByDesc('dateTime')->first();
        $this->assertSame(now()->addDay()->toDateString(), $row->dateTime);
    }

    public function test_store_new_motivation_inserts_for_last_plus_one_when_last_is_in_future(): void
    {
        $future = now()->addDays(5)->toDateString();
        $this->seedMotivation($future, ['hu' => 'Jövő']);

        $ok = $this->service->storeNewMotivation(['hu' => 'Kövi']);
        $this->assertTrue((bool) $ok);

        $row = DB::table('Motivations')->orderByDesc('dateTime')->first();
        $this->assertSame(date('Y-m-d', strtotime($future . ' +1 day')), $row->dateTime);
    }
}
