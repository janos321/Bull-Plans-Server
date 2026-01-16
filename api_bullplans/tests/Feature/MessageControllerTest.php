<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $base = '/api/messages';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
    }

    private function seedConversation(array $participants, array $messages, $updatedAt = null): void
    {
        sort($participants);

        DB::table('conversations')->insert([
            'participants' => json_encode($participants, JSON_UNESCAPED_UNICODE),
            'chat_data'    => json_encode(['messages' => $messages], JSON_UNESCAPED_UNICODE),
            'updated_at'   => $updatedAt ?? now(),
        ]);
    }

    public function test_get_requires_email_validation(): void
    {
        $res = $this->postAuthJson($this->base . '/get');

        $this->assertApiValidation($res);
    }

    public function test_get_returns_empty_array_when_user_has_no_conversations(): void
    {
        $this->seedConversation(['a@test.com', 'b@test.com'], [
            ['from' => 'a@test.com', 'to' => 'b@test.com', 'text' => 'hi', 'time' => now()->toIso8601String()],
        ]);

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'x@test.com',
        ]);

        $this->assertApiSuccess($res, 'Messages retrieved');
        $this->assertSame([], $res->json('data'));
    }

    public function test_get_returns_conversations_with_others_and_messages(): void
    {
        $this->seedConversation(['me@test.com', 'other@test.com'], [
            ['from' => 'me@test.com', 'to' => 'other@test.com', 'text' => 'hello', 'time' => now()->toIso8601String()],
        ]);

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'me@test.com',
        ]);

        $this->assertApiSuccess($res, 'Messages retrieved');

        $data = $res->json('data');
        $this->assertIsArray($data);
        $this->assertCount(1, $data);

        $this->assertSame(['other@test.com'], $data[0]['email']);
        $this->assertIsArray($data[0]['content']);
        $this->assertSame('hello', $data[0]['content'][0]['text']);
    }

    public function test_get_orders_by_updated_at_desc(): void
    {
        $this->seedConversation(['me@test.com', 'a@test.com'], [
            ['from' => 'a@test.com', 'to' => 'me@test.com', 'text' => 'old', 'time' => now()->toIso8601String()],
        ], now()->subDays(2));

        $this->seedConversation(['me@test.com', 'b@test.com'], [
            ['from' => 'b@test.com', 'to' => 'me@test.com', 'text' => 'new', 'time' => now()->toIso8601String()],
        ], now()->subDay());

        $res = $this->postAuthJson($this->base . '/get', [
            'email' => 'me@test.com',
        ]);

        $this->assertApiSuccess($res, 'Messages retrieved');

        $data = $res->json('data');
        $this->assertSame(['b@test.com'], $data[0]['email']);
        $this->assertSame('new', $data[0]['content'][0]['text']);
        $this->assertSame(['a@test.com'], $data[1]['email']);
    }

    public function test_put_requires_payload_validation(): void
    {
        $res = $this->putAuthJson($this->base . '/put');

        $this->assertApiValidation($res);
    }

    public function test_put_creates_new_conversation_when_missing(): void
    {
        $res = $this->putAuthJson($this->base . '/put', [
            'from' => 'me@test.com',
            'emails' => ['other@test.com'],
            'text' => 'hey',
        ]);

        $this->assertApiSuccess($res, 'Message stored');

        $this->assertSame(1, DB::table('conversations')->count());
    }

    public function test_put_appends_message_when_conversation_exists(): void
    {
        $this->seedConversation(['me@test.com', 'other@test.com'], [
            ['from' => 'me@test.com', 'to' => 'other@test.com', 'text' => 'first', 'time' => now()->toIso8601String()],
        ]);

        $res = $this->putAuthJson($this->base . '/put', [
            'from' => 'me@test.com',
            'emails' => ['other@test.com'],
            'text' => 'second',
        ]);

        $this->assertApiSuccess($res, 'Message stored');

        $row = DB::table('conversations')->first();
        $chat = json_decode($row->chat_data, true);

        $this->assertIsArray($chat);
        $this->assertIsArray($chat['messages']);
        $this->assertCount(2, $chat['messages']);
        $this->assertSame('second', $chat['messages'][1]['text']);
    }

    public function test_put_does_not_duplicate_conversation_row(): void
    {
        $this->seedConversation(['me@test.com', 'other@test.com'], [
            ['from' => 'me@test.com', 'to' => 'other@test.com', 'text' => 'first', 'time' => now()->toIso8601String()],
        ]);

        $this->putAuthJson($this->base . '/put', [
            'from' => 'me@test.com',
            'emails' => ['other@test.com'],
            'text' => 'second',
        ]);

        $this->assertSame(1, DB::table('conversations')->count());
    }
}
