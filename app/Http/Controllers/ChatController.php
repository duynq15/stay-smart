<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatbotService $bot) {}

    public function start(Request $request): JsonResponse
    {
        $session = $this->bot->startSession($request->user()?->id);

        return response()->json([
            'session_id' => $session->id,
            'greeting' => "Chào bạn! Tôi là Smarty 👋 Tôi giúp bạn tìm khách sạn ở Hà Nội theo mong muốn — vị trí, ngân sách, view, tiện nghi… Bạn cần gì nào?",
            'quick_replies' => [
                'Khách sạn 5 sao Hoàn Kiếm',
                'View Hồ Tây dưới 3tr',
                'Có bể bơi và spa',
                'Tìm khách sạn này ở đâu?',
            ],
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'integer', 'exists:chat_sessions,id'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $session = ChatSession::findOrFail($validated['session_id']);
        $reply = $this->bot->reply($session, $validated['message']);

        return response()->json($reply);
    }

    public function places(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'integer', 'exists:chat_sessions,id'],
            'district' => ['required', 'string', 'max:100'],
            'hotel_name' => ['required', 'string', 'max:200'],
            'wants' => ['required', 'in:yes,no'],
        ]);

        $session = ChatSession::findOrFail($validated['session_id']);

        if ($validated['wants'] === 'yes') {
            $reply = $this->bot->suggestPlaces($session, $validated['district'], $validated['hotel_name']);
        } else {
            $reply = $this->bot->suggestDeclined($session, $validated['hotel_name']);
        }

        return response()->json($reply);
    }
}
