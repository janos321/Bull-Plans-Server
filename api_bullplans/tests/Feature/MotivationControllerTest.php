<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MotivationControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/motivation';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
    }

    private function seedMotivation(string $date, array $translations): void
    {
        DB::table('Motivations')->insert([
            'dateTime'    => $date,
            'motivation'  => json_encode($translations, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function test_get_requires_lang_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/get');

        $this->assertApiValidation($res);
    }

    public function test_get_returns_error_when_no_rows(): void
    {
        $res = $this->postAuthJson($this->base . '/get', [
            'lang' => 'hu',
        ]);

        $res->assertStatus(400)
            ->assertJsonStructure(['success', 'message'])
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No motivation found');
    }

    public function test_get_returns_today_translation_when_exists(): void
    {
        $today = now()->toDateString();
        $this->seedMotivation($today, ['hu' => 'Ma HU', 'en' => 'Today EN']);

        $res = $this->postAuthJson($this->base . '/get', [
            'lang' => 'hu',
        ]);

        $this->assertApiSuccess($res, 'Motivation retrieved');

        $this->assertSame('Ma HU', $res->json('data'));
    }

    public function test_get_falls_back_to_any_translation_when_lang_missing(): void
    {
        $today = now()->toDateString();
        $this->seedMotivation($today, ['en' => 'Only EN']);

        $res = $this->postAuthJson($this->base . '/get', [
            'lang' => 'hu',
        ]);

        $this->assertApiSuccess($res, 'Motivation retrieved');

        $this->assertSame('Only EN', $res->json('data'));
    }

    public function test_get_uses_latest_past_when_today_missing(): void
    {
        $yesterday = now()->subDay()->toDateString();
        $this->seedMotivation($yesterday, ['hu' => 'Tegnap HU']);

        $res = $this->postAuthJson($this->base . '/get', [
            'lang' => 'hu',
        ]);

        $this->assertApiSuccess($res, 'Motivation retrieved');

        $this->assertSame('Tegnap HU', $res->json('data'));
    }

    public function test_post_requires_translations_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/post');

        $this->assertApiValidation($res);
    }

    public function test_post_inserts_new_row_and_returns_success(): void
    {
        $res = $this->postAuthJson($this->base . '/post', [
            'translations' => ['hu' => 'Új HU', 'en' => 'New EN'],
        ]);

        $this->assertApiSuccess($res, 'Motivation saved');

        $this->assertSame(1, DB::table('Motivations')->count());
    }
}
