<?php

namespace App\Services\Chatbot;

use App\Models\ChatSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Multi-provider LLM client supporting FREE providers:
 *   - gemini   : Google Gemini API (free tier ~15 req/min) — get key at https://aistudio.google.com
 *   - groq     : Groq Cloud (free tier, very fast) — get key at https://console.groq.com
 *   - openrouter: OpenRouter (gateway with free models) — get key at https://openrouter.ai
 *   - ollama   : Local Ollama (no key, run `ollama pull llama3.2` first) — https://ollama.com
 */
class LlmClient
{
    public function isEnabled(): bool
    {
        if (! config('services.chatbot.use_ai')) {
            return false;
        }

        $provider = config('services.chatbot.provider');

        return match ($provider) {
            'gemini' => filled(config('services.chatbot.gemini_key')),
            'groq' => filled(config('services.chatbot.groq_key')),
            'openrouter' => filled(config('services.chatbot.openrouter_key')),
            'ollama' => true, // local, no key needed
            default => false,
        };
    }

    public function generate(string $userMessage, ChatSession $session): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $provider = config('services.chatbot.provider');
        $history = $this->buildHistory($session, $userMessage);

        try {
            return match ($provider) {
                'gemini' => $this->callGemini($history),
                'groq' => $this->callGroq($history),
                'openrouter' => $this->callOpenRouter($history),
                'ollama' => $this->callOllama($history),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning('Chatbot LLM call failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function buildHistory(ChatSession $session, string $userMessage): array
    {
        return $session->messages()
            ->orderBy('created_at')
            ->limit(10)
            ->get(['sender', 'content'])
            ->map(fn ($m) => [
                'role' => $m->sender === 'user' ? 'user' : 'assistant',
                'content' => $m->content,
            ])
            ->push(['role' => 'user', 'content' => $userMessage])
            ->toArray();
    }

    /** Google Gemini API — free tier ~15 RPM */
    private function callGemini(array $history): ?string
    {
        $model = config('services.chatbot.model') ?: 'gemini-1.5-flash';
        $key = config('services.chatbot.gemini_key');

        $contents = collect($history)->map(fn ($m) => [
            'role' => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $m['content']]],
        ])->all();

        $response = Http::timeout(20)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}", [
                'contents' => $contents,
                'systemInstruction' => [
                    'parts' => [['text' => $this->systemPrompt()]],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 600,
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('Gemini error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    /** Groq Cloud — free tier, OpenAI-compatible, very fast */
    private function callGroq(array $history): ?string
    {
        $model = config('services.chatbot.model') ?: 'llama-3.3-70b-versatile';

        return $this->callOpenAiCompatible(
            url: 'https://api.groq.com/openai/v1/chat/completions',
            token: config('services.chatbot.groq_key'),
            model: $model,
            history: $history,
        );
    }

    /** OpenRouter — gateway with multiple free models */
    private function callOpenRouter(array $history): ?string
    {
        $model = config('services.chatbot.model') ?: 'meta-llama/llama-3.3-70b-instruct:free';

        return $this->callOpenAiCompatible(
            url: 'https://openrouter.ai/api/v1/chat/completions',
            token: config('services.chatbot.openrouter_key'),
            model: $model,
            history: $history,
            extraHeaders: [
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name', 'STAY-SMART'),
            ],
        );
    }

    private function callOpenAiCompatible(
        string $url,
        string $token,
        string $model,
        array $history,
        array $extraHeaders = [],
    ): ?string {
        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $history,
        );

        $response = Http::withToken($token)
            ->withHeaders($extraHeaders)
            ->timeout(25)
            ->post($url, [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 600,
                'temperature' => 0.7,
            ]);

        if (! $response->successful()) {
            Log::warning('LLM provider error', ['url' => $url, 'status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $response->json('choices.0.message.content');
    }

    /** Ollama (local) — no API key needed; user runs `ollama pull llama3.2` */
    private function callOllama(array $history): ?string
    {
        $model = config('services.chatbot.model') ?: 'llama3.2';
        $baseUrl = rtrim(config('services.chatbot.ollama_url') ?: 'http://localhost:11434', '/');

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $history,
        );

        $response = Http::timeout(60)->post("{$baseUrl}/api/chat", [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
            'options' => ['temperature' => 0.7],
        ]);

        if (! $response->successful()) {
            Log::warning('Ollama error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $response->json('message.content');
    }

    private function systemPrompt(): string
    {
        return config('services.chatbot.system_prompt')
            ?: 'Bạn là Smarty — trợ lý đặt phòng AI của STAY-SMART. '
                . 'Bạn CHỈ tư vấn khách sạn ở Hà Nội (9 quận: Hoàn Kiếm, Tây Hồ, Ba Đình, Cầu Giấy, Hai Bà Trưng, Đống Đa, Long Biên, Hà Đông, Hoàng Mai). '
                . 'Trả lời ngắn gọn (2-3 câu), thân thiện, bằng tiếng Việt. '
                . 'Khi user hỏi tìm khách sạn, hãy gợi ý họ mô tả: vị trí, ngân sách (vd 2tr/đêm), số người, tiện nghi (bể bơi, view, spa). '
                . 'Không bịa tên khách sạn cụ thể — hệ thống sẽ tự lọc database khi user mô tả đủ tiêu chí.';
    }
}
