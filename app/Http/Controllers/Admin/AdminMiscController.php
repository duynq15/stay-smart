<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Hotel;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminMiscController extends Controller
{
    public function reviews(Request $request): Response
    {
        $query = Review::with(['user:id,name,avatar', 'hotel:id,name']);

        if ($request->filled('rating')) {
            $query->where('rating', '>=', $request->rating);
        }

        $reviews = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $reviews->getCollection()->transform(fn ($r) => [
            'id' => $r->id,
            'rating' => $r->rating,
            'comment' => $r->comment,
            'created_at' => $r->created_at?->format('Y-m-d'),
            'user' => $r->user ? ['name' => $r->user->name, 'avatar' => $r->user->avatar] : ['name' => 'N/A', 'avatar' => null],
            'hotel' => $r->hotel ? ['name' => $r->hotel->name] : ['name' => 'N/A'],
        ]);

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['rating']),
        ]);
    }

    public function chats(Request $request): Response
    {
        $sessions = ChatSession::with('user:id,name,email')
            ->withCount('messages')
            ->orderByDesc('started_at')
            ->paginate(20);

        $sessions->getCollection()->transform(fn ($s) => [
            'id' => $s->id,
            'started_at' => $s->started_at?->format('Y-m-d H:i'),
            'message_count' => $s->messages_count,
            'user' => $s->user ? ['name' => $s->user->name, 'email' => $s->user->email] : null,
        ]);

        return Inertia::render('Admin/Chats/Index', [
            'sessions' => $sessions,
        ]);
    }

    public function chatTranscript(int $session)
    {
        $messages = ChatMessage::where('session_id', $session)
            ->orderBy('created_at')
            ->get(['id', 'sender', 'content', 'created_at']);

        $session = ChatSession::with('user:id,name,email')->findOrFail($session);

        return Inertia::render('Admin/Chats/Show', [
            'session' => [
                'id' => $session->id,
                'started_at' => $session->started_at?->format('Y-m-d H:i'),
                'user' => $session->user ? ['name' => $session->user->name, 'email' => $session->user->email] : null,
                'messages' => $messages->map(fn ($m) => [
                    'id' => $m->id,
                    'sender' => $m->sender,
                    'content' => $m->content,
                    'time' => Carbon::parse($m->created_at)->format('H:i'),
                ]),
            ],
        ]);
    }

    public function settings(): Response
    {
        $defaultModels = [
            'gemini' => 'gemini-1.5-flash',
            'groq' => 'llama-3.3-70b-versatile',
            'openrouter' => 'meta-llama/llama-3.3-70b-instruct:free',
            'ollama' => 'llama3.2',
        ];

        $provider = config('services.chatbot.provider', 'gemini');
        $effectiveModel = config('services.chatbot.model') ?: ($defaultModels[$provider] ?? '—');

        return Inertia::render('Admin/Settings', [
            'config' => [
                'use_ai' => (bool) config('services.chatbot.use_ai'),
                'provider' => $provider,
                'model' => $effectiveModel,
                'has_gemini_key' => filled(config('services.chatbot.gemini_key')),
                'has_groq_key' => filled(config('services.chatbot.groq_key')),
                'has_openrouter_key' => filled(config('services.chatbot.openrouter_key')),
                'ollama_url' => config('services.chatbot.ollama_url'),
            ],
            'env' => [
                'app_env' => config('app.env'),
                'mail_mailer' => config('mail.default'),
                'db_connection' => config('database.default'),
            ],
            'stats' => [
                'total_sessions' => ChatSession::count(),
                'total_messages' => ChatMessage::count(),
                'today_sessions' => ChatSession::whereDate('started_at', today())->count(),
            ],
        ]);
    }

    public function analytics(Request $request): Response
    {
        // Revenue by month for last 6 months
        $revenueMonthly = collect();
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $sum = (int) Booking::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_amount');
            $revenueMonthly->push([
                'label' => $start->format('m/Y'),
                'value' => $sum,
            ]);
        }

        // Top hotels
        $topHotels = Booking::select('hotel_id', DB::raw('count(*) as bookings_count'), DB::raw('sum(total_amount) as revenue'))
            ->where('status', '!=', 'cancelled')
            ->groupBy('hotel_id')
            ->orderByDesc('bookings_count')
            ->limit(8)
            ->with('hotel:id,name,district')
            ->get()
            ->map(fn ($r) => [
                'hotel' => $r->hotel ? ['name' => $r->hotel->name, 'district' => $r->hotel->district] : null,
                'bookings_count' => $r->bookings_count,
                'revenue' => (int) $r->revenue,
            ]);

        // Status distribution
        $statusBreakdown = Booking::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($r) => ['status' => $r->status, 'count' => $r->count]);

        return Inertia::render('Admin/Analytics', [
            'revenueMonthly' => $revenueMonthly,
            'topHotels' => $topHotels,
            'statusBreakdown' => $statusBreakdown,
        ]);
    }
}
