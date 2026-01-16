<?php

namespace Tests\Unit;

use App\Modules\Message\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    private MessageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MessageService();
    }

    private function seedConversation(array $participants, $chatData, $updatedAt = null): void
    {
        sort($participants);

        DB::table('conversations')->insert([
            'participants' => json_encode($participants, JSON_UNESCAPED_UNICODE),
            'chat_data'    => is_string($chatData) ? $chatData : json_encode($chatData, JSON_UNESCAPED_UNICODE),
            'updated_at'   => $updatedAt ?? now(),
        ]);
    }

    public function test_get_messages_for_returns_empty_when_no_rows(): void
    {
        $res = $this->service->getMessagesFor('me@test.com');
        $this->assertSame([], $res);
    }

    public function test_get_messages_for_filters_only_conversations_that_include_user(): void
    {
        $this->seedConversation(['a@test.com', 'b@test.com'], ['messages' => []]);
        $this->seedConversation(['me@test.com', 'other@test.com'], ['messages' => [
            ['from' => 'me@test.com', 'to' => 'other@test.com', 'text' => 'hi', 'time' => now()->toIso8601String()],
        ]]);

        $res = $this->service->getMessagesFor('me@test.com');

        $this->assertCount(1, $res);
        $this->assertSame(['other@test.com'], $res[0]['email']);
        $this->assertSame('hi', $res[0]['content'][0]['text']);
    }

    public function test_get_messages_for_orders_by_updated_at_desc(): void
    {
        $this->seedConversation(['me@test.com', 'a@test.com'], ['messages' => [
            ['from' => 'a@test.com', 'to' => 'me@test.com', 'text' => 'old', 'time' => now()->toIso8601String()],
        ]], now()->subDays(2));

        $this->seedConversation(['me@test.com', 'b@test.com'], ['messages' => [
            ['from' => 'b@test.com', 'to' => 'me@test.com', 'text' => 'new', 'time' => now()->toIso8601String()],
        ]], now()->subDay());

        $res = $this->service->getMessagesFor('me@test.com');

        $this->assertSame(['b@test.com'], $res[0]['email']);
        $this->assertSame(['a@test.com'], $res[1]['email']);
    }

    public function test_get_messages_for_handles_invalid_participants_json_by_skipping_row(): void
    {
        DB::table('conversations')->insert([
            'participants' => '{invalid json',
            'chat_data'    => json_encode(['messages' => []]),
            'updated_at'   => now(),
        ]);

        $res = $this->service->getMessagesFor('me@test.com');
        $this->assertSame([], $res);
    }

    public function test_get_messages_for_handles_invalid_chat_data_as_empty_messages(): void
    {
        $this->seedConversation(['me@test.com', 'other@test.com'], '{invalid json');

        $res = $this->service->getMessagesFor('me@test.com');

        $this->assertCount(1, $res);
        $this->assertSame([[]], $res[0]['content']);
    }

    public function test_store_message_inserts_new_conversation_when_missing(): void
    {
        $ok = $this->service->storeMessage('me@test.com', ['other@test.com'], 'hello');
        $this->assertTrue((bool) $ok);

        $this->assertSame(1, DB::table('conversations')->count());

        $row = DB::table('conversations')->first();
        $participants = json_decode($row->participants, true);

        $this->assertSame(['me@test.com', 'other@test.com'], $participants);
    }

    public function test_store_message_appends_when_exists_and_does_not_duplicate_row(): void
    {
        $this->seedConversation(['me@test.com', 'other@test.com'], ['messages' => [
            ['from' => 'me@test.com', 'to' => 'other@test.com', 'text' => 'first', 'time' => now()->toIso8601String()],
        ]]);

        $ok = $this->service->storeMessage('me@test.com', ['other@test.com'], 'second');
        $this->assertTrue((bool) $ok);

        $this->assertSame(1, DB::table('conversations')->count());

        $row = DB::table('conversations')->first();
        $chat = json_decode($row->chat_data, true);

        $this->assertCount(2, $chat['messages']);
        $this->assertSame('second', $chat['messages'][1]['text']);
    }
}
