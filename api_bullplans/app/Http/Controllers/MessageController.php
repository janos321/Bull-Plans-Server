<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function getConversations(Request $request)
    {
        $email = $request->input('email');
        return response()->json([
            'success' => true,
            'message' => 'Conversation list returned',
            'data' => ['email' => $email]
        ]);
    }

    public function putConversation(Request $request)
    {
        $from = $request->input('from');
        $emails = $request->input('emails');
        $text = $request->input('text');

        return response()->json([
            'success' => true,
            'message' => 'Message saved',
            'data' => compact('from', 'emails', 'text')
        ]);
    }
}
