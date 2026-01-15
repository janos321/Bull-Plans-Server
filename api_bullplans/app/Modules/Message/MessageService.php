<?php

namespace App\Modules\Message;

use Illuminate\Support\Facades\DB;

class MessageService
{
    /**
     * Lekéri az összes beszélgetést egy felhasználónak
     */
   public function getMessagesFor(string $email): array
    {
        $rows = DB::table('conversations')
            ->orderByDesc('updated_at')
            ->get();
    
        $result = [];
    
        foreach ($rows as $row) {
            $participants = json_decode($row->participants, true);
            if (!is_array($participants)) {
                continue;
            }
    
            if (!in_array($email, $participants)) {
                continue;
            }
    
            $others = array_values(
                array_filter($participants, fn($p) => $p !== $email)
            );
    
            $chat = json_decode($row->chat_data, true);
            if (!is_array($chat)) {
                $chat = [];
            }
    
            $messages = $chat['messages'] ?? [];
    
            if (!is_array($messages)) {
                $messages = [$messages];
            }
    
            if (array_keys($messages) !== range(0, count($messages) - 1)) {
                $messages = [$messages];
            }
    
            $result[] = [
                'email'     => $others,
                'content'   => $messages,
                'updatedAt' => (string)$row->updated_at,
            ];
        }
    
        return $result;
    }



    /**
     * Üzenet beszúrása vagy frissítése
     */
    public function storeMessage(string $from, array $emails, string $text): bool
    {
        $to = $emails[0];

        $participants = [$from, $to];
        sort($participants);
        $participantsJson = json_encode($participants, JSON_UNESCAPED_UNICODE);

        $row = DB::table('conversations')
            ->where('participants', $participantsJson)
            ->first();

        $msg = [
            'from' => $from,
            'to'   => $to,
            'text' => $text,
            'time' => now()->toIso8601String()
        ];

        if ($row) {
            $chat = json_decode($row->chat_data, true) ?: ['messages' => []];
            $chat['messages'][] = $msg;

            return DB::table('conversations')
                ->where('id', $row->id)
                ->update([
                    'chat_data'  => json_encode($chat, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]) > 0;
        }

        $chatData = [
            'messages' => [$msg]
        ];

        return DB::table('conversations')->insert([
            'participants' => $participantsJson,
            'chat_data'    => json_encode($chatData, JSON_UNESCAPED_UNICODE),
            'updated_at'   => now(),
        ]);
    }
}
