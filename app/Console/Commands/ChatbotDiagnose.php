<?php

namespace App\Console\Commands;

use App\Models\ChatSession;
use App\Services\Chatbot\LlmClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ChatbotDiagnose extends Command
{
    protected $signature = 'chatbot:diagnose {message? : Câu test (tùy chọn)}';

    protected $description = 'Chẩn đoán cấu hình chatbot LLM';

    public function handle(LlmClient $llm): int
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('  STAY-SMART · Chatbot Diagnostic');
        $this->info('═══════════════════════════════════════');

        $useAi = config('services.chatbot.use_ai');
        $provider = config('services.chatbot.provider');
        $model = config('services.chatbot.model');

        $this->newLine();
        $this->line('<fg=gray>[1/5] Đọc config...</>');
        $this->table(['Key', 'Value'], [
            ['CHATBOT_USE_AI', $useAi ? '<fg=green>true</>' : '<fg=red>false</>'],
            ['CHATBOT_PROVIDER', $provider ?: '<fg=red>(empty)</>'],
            ['CHATBOT_MODEL', $model ?: '<fg=gray>(empty — sẽ dùng default)</>'],
        ]);

        if (! $useAi) {
            $this->error('❌ CHATBOT_USE_AI=false → AI bị tắt.');
            $this->warn('   → Sửa .env: CHATBOT_USE_AI=true');
            $this->warn('   → Chạy: php artisan config:clear');
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('<fg=gray>[2/5] Kiểm tra API key...</>');

        $keyMap = [
            'gemini' => ['env' => 'GEMINI_API_KEY', 'config' => 'services.chatbot.gemini_key', 'prefix' => 'AIza'],
            'groq' => ['env' => 'GROQ_API_KEY', 'config' => 'services.chatbot.groq_key', 'prefix' => 'gsk_'],
            'openrouter' => ['env' => 'OPENROUTER_API_KEY', 'config' => 'services.chatbot.openrouter_key', 'prefix' => 'sk-or-'],
            'ollama' => ['env' => 'OLLAMA_URL', 'config' => 'services.chatbot.ollama_url', 'prefix' => 'http'],
        ];

        if (! isset($keyMap[$provider])) {
            $this->error("❌ Provider '{$provider}' không hợp lệ.");
            $this->warn('   → Cho phép: gemini | groq | openrouter | ollama');
            return self::FAILURE;
        }

        $info = $keyMap[$provider];
        $key = config($info['config']);

        if (empty($key)) {
            $this->error("❌ {$info['env']} chưa được set.");
            $this->warn("   → Sửa .env, thêm dòng: {$info['env']}=...");
            $this->warn('   → Chạy: php artisan config:clear');
            return self::FAILURE;
        }

        if ($provider !== 'ollama' && ! str_starts_with($key, $info['prefix'])) {
            $this->warn("⚠ Key không bắt đầu bằng '{$info['prefix']}' — có thể sai key");
            $this->warn('   Nhận thấy: ' . substr($key, 0, 12) . '...');
        }

        $this->line('   ✓ ' . $info['env'] . ' = ' . substr($key, 0, 8) . '...' . substr($key, -4) . ' (length=' . strlen($key) . ')');

        if ($key !== trim($key)) {
            $this->warn('⚠ Key có khoảng trắng đầu/cuối! Sửa lại trong .env.');
        }

        $this->newLine();
        $this->line('<fg=gray>[3/5] LlmClient::isEnabled()...</>');
        if ($llm->isEnabled()) {
            $this->line('   ✓ <fg=green>TRUE</> — LLM sẵn sàng');
        } else {
            $this->error('   ✗ FALSE — Có vấn đề với cấu hình');
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('<fg=gray>[4/5] Gọi API thực tế...</>');

        $session = ChatSession::create(['user_id' => null, 'started_at' => now(), 'message_count' => 0]);
        $message = $this->argument('message') ?: 'Hà Nội nổi tiếng món ăn gì?';

        $this->line('   Test message: <fg=cyan>"' . $message . '"</>');
        $this->line('   Đang gọi ' . $provider . '... (timeout ~25s)');

        $start = microtime(true);
        $reply = $llm->generate($message, $session);
        $elapsed = round((microtime(true) - $start) * 1000);

        $session->delete();

        if ($reply === null) {
            $this->error('   ✗ API trả về NULL (' . $elapsed . 'ms)');
            $this->newLine();
            $this->warn('   Kiểm tra storage/logs/laravel.log để xem lỗi cụ thể');
            $this->newLine();
            $this->line('   <fg=gray>Các nguyên nhân thường gặp:</>');
            $this->line('   • Sai key (copy thiếu/thừa ký tự)');
            $this->line('   • Hết quota free tier (đợi 1 phút)');
            $this->line('   • Region bị chặn (thử bỏ VPN)');
            $this->line('   • Network/firewall chặn outbound HTTPS');
            return self::FAILURE;
        }

        $this->info('   ✓ <fg=green>API trả về thành công</> (' . $elapsed . 'ms)');
        $this->newLine();
        $this->line('<fg=gray>[5/5] Phản hồi từ LLM:</>');
        $this->line('   <fg=cyan>' . str_replace("\n", "\n   ", trim($reply)) . '</>');

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('  ✅ Chatbot AI hoạt động bình thường!');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        return self::SUCCESS;
    }
}
