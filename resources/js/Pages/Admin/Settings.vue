<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    config: { type: Object, required: true },
    env: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
});

const providers = [
    {
        key: 'gemini',
        name: 'Google Gemini',
        tier: 'Free · ~15 RPM',
        desc: 'Hiệu năng tốt, hỗ trợ tiếng Việt xuất sắc, ổn định nhất cho chatbot.',
        signup: 'https://aistudio.google.com/app/apikey',
        envKey: 'GEMINI_API_KEY',
        defaultModel: 'gemini-1.5-flash',
        hasKeyFlag: 'has_gemini_key',
    },
    {
        key: 'groq',
        name: 'Groq Cloud',
        tier: 'Free · cực nhanh',
        desc: 'Inference siêu nhanh (Llama 3.3 70B), độ trễ ~200ms. Phù hợp chat realtime.',
        signup: 'https://console.groq.com/keys',
        envKey: 'GROQ_API_KEY',
        defaultModel: 'llama-3.3-70b-versatile',
        hasKeyFlag: 'has_groq_key',
    },
    {
        key: 'openrouter',
        name: 'OpenRouter',
        tier: 'Free models',
        desc: 'Gateway nhiều model miễn phí (Llama 3.3, Gemini, Mistral...). Chỉ 1 key dùng tất cả.',
        signup: 'https://openrouter.ai/keys',
        envKey: 'OPENROUTER_API_KEY',
        defaultModel: 'meta-llama/llama-3.3-70b-instruct:free',
        hasKeyFlag: 'has_openrouter_key',
    },
    {
        key: 'ollama',
        name: 'Ollama (Local)',
        tier: 'Self-host · 0đ',
        desc: 'Chạy LLM local trên máy bạn (cần GPU/CPU mạnh). Không cần API key, không bị rate limit.',
        signup: 'https://ollama.com',
        envKey: 'OLLAMA_URL',
        defaultModel: 'llama3.2',
        hasKeyFlag: null,
    },
];

const activeProvider = computed(() => providers.find((p) => p.key === props.config.provider));

function hasKey(p) {
    if (p.hasKeyFlag === null) return true; // ollama doesn't need key
    return props.config[p.hasKeyFlag];
}
</script>

<template>
    <Head title="Admin · Cài đặt" />
    <AdminLayout page-title="<em>Cài đặt</em> hệ thống" page-subtitle="Cấu hình chatbot AI miễn phí và môi trường">
        <div v-if="stats" class="mini-stats">
            <div class="mini-stat">
                <label>Phiên chat tổng</label>
                <strong>{{ stats.total_sessions }}</strong>
            </div>
            <div class="mini-stat">
                <label>Tin nhắn tổng</label>
                <strong>{{ stats.total_messages }}</strong>
            </div>
            <div class="mini-stat">
                <label>Phiên hôm nay</label>
                <strong>{{ stats.today_sessions }}</strong>
            </div>
        </div>

        <div class="settings-grid">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-title">Smarty Chatbot</div>
                        <div class="panel-sub">Rule-based parser tiếng Việt + LLM miễn phí (tùy chọn)</div>
                    </div>
                    <span class="status-pill" :class="config.use_ai ? 'on' : 'off'">
                        {{ config.use_ai ? 'AI ĐANG BẬT' : 'AI ĐANG TẮT' }}
                    </span>
                </div>

                <div class="setting-row">
                    <div>
                        <strong>Engine cơ bản</strong>
                        <small>Luôn hoạt động. Parse tiếng Việt → query DB lọc khách sạn theo intent.</small>
                    </div>
                    <span class="status-pill on">Rule-based · Online</span>
                </div>

                <div v-if="config.use_ai && activeProvider" class="setting-row">
                    <div>
                        <strong>LLM Provider đang dùng</strong>
                        <small>Khi rule-based không match, request fallback sang LLM</small>
                    </div>
                    <div style="text-align: right;">
                        <strong style="color: var(--emerald-700);">{{ activeProvider.name }}</strong><br>
                        <code class="code-pill" style="font-size: 11px;">{{ config.model }}</code>
                    </div>
                </div>

                <div class="setting-row" style="flex-direction: column; align-items: stretch; gap: 12px;">
                    <div>
                        <strong>Provider khả dụng</strong>
                        <small>Kích hoạt 1 trong 4 LLM miễn phí dưới đây bằng cách thêm key vào .env</small>
                    </div>
                    <div class="provider-grid">
                        <div
                            v-for="p in providers"
                            :key="p.key"
                            class="provider-card"
                            :class="{ active: config.provider === p.key && config.use_ai, ready: hasKey(p) }"
                        >
                            <div class="pc-head">
                                <strong>{{ p.name }}</strong>
                                <span class="pc-tier">{{ p.tier }}</span>
                            </div>
                            <p class="pc-desc">{{ p.desc }}</p>
                            <div class="pc-meta">
                                <code class="code-mini">{{ p.defaultModel }}</code>
                                <span v-if="hasKey(p)" class="key-badge ok">✓ {{ p.envKey }} đã có</span>
                                <span v-else class="key-badge missing">⚠ thiếu {{ p.envKey }}</span>
                            </div>
                            <a :href="p.signup" target="_blank" rel="noopener" class="pc-link">Lấy key miễn phí →</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-title">Môi trường</div>
                        <div class="panel-sub">Chỉ đọc · sửa qua <code>.env</code></div>
                    </div>
                </div>

                <div class="setting-row">
                    <div><strong>APP_ENV</strong></div>
                    <code class="code-pill">{{ env.app_env }}</code>
                </div>
                <div class="setting-row">
                    <div><strong>MAIL_MAILER</strong></div>
                    <code class="code-pill">{{ env.mail_mailer }}</code>
                </div>
                <div class="setting-row">
                    <div><strong>DB_CONNECTION</strong></div>
                    <code class="code-pill">{{ env.db_connection }}</code>
                </div>
                <div class="setting-row">
                    <div><strong>Ollama URL</strong><small>Dùng khi provider=ollama</small></div>
                    <code class="code-pill" style="font-size: 10px;">{{ config.ollama_url }}</code>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <div class="panel-title">Hướng dẫn bật LLM miễn phí</div>
                    <div class="panel-sub">Bước 1 → 3 dưới đây · sau đó chạy <code>php artisan config:clear</code></div>
                </div>
            </div>
            <div class="guide-grid">
                <div class="guide-step">
                    <span class="step-num">1</span>
                    <h4>Lấy API key (miễn phí, 1 phút)</h4>
                    <p>Chọn 1 provider bất kỳ → bấm "Lấy key miễn phí →" ở card phía trên → đăng nhập Google → copy key.</p>
                </div>
                <div class="guide-step">
                    <span class="step-num">2</span>
                    <h4>Thêm vào file .env</h4>
                    <pre class="env-snippet">CHATBOT_USE_AI=true
CHATBOT_PROVIDER=gemini
GEMINI_API_KEY=AIza...your-key...

# hoặc Groq:
# CHATBOT_PROVIDER=groq
# GROQ_API_KEY=gsk_...</pre>
                </div>
                <div class="guide-step">
                    <span class="step-num">3</span>
                    <h4>Apply</h4>
                    <pre class="env-snippet">php artisan config:clear
# Khởi động lại server</pre>
                    <p style="margin-top: 8px;">Smarty sẽ dùng AI khi user hỏi câu phức tạp ngoài phạm vi rule-based (vd "thời tiết Hà Nội tháng 5 thế nào?").</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.mini-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.mini-stat {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
}
.mini-stat label {
    display: block;
    font-size: 11px;
    color: var(--ink-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}
.mini-stat strong {
    font-family: var(--serif);
    font-size: 24px;
    font-weight: 500;
    color: var(--ink-900);
}
.settings-grid {
    display: grid;
    grid-template-columns: 1.7fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.setting-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid rgba(11, 20, 16, 0.06);
    gap: 16px;
}
.setting-row:last-child { border-bottom: none; }
.setting-row strong { display: block; font-size: 14px; }
.setting-row small { display: block; color: var(--ink-500); font-size: 12px; margin-top: 3px; }
.status-pill {
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.status-pill.on { background: rgba(31, 155, 106, 0.15); color: var(--emerald-900); }
.status-pill.off { background: rgba(11, 20, 16, 0.08); color: var(--ink-700); }
.code-pill {
    background: var(--ink-900);
    color: var(--emerald-300);
    padding: 5px 11px;
    border-radius: 6px;
    font-size: 12px;
    font-family: 'Menlo', monospace;
}
.code-mini {
    background: var(--paper);
    color: var(--ink-700);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-family: 'Menlo', monospace;
}
.provider-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.provider-card {
    background: var(--paper);
    border: 1.5px solid rgba(11, 20, 16, 0.06);
    border-radius: 12px;
    padding: 14px 16px;
    transition: all 0.2s;
    position: relative;
}
.provider-card.ready {
    border-color: rgba(31, 155, 106, 0.4);
}
.provider-card.active {
    background: rgba(31, 155, 106, 0.08);
    border-color: var(--emerald-500);
    box-shadow: 0 0 0 3px rgba(31, 155, 106, 0.12);
}
.provider-card.active::before {
    content: '✓ ĐANG DÙNG';
    position: absolute;
    top: -8px;
    right: 12px;
    background: var(--emerald-700);
    color: var(--cream);
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.06em;
}
.pc-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.pc-head strong {
    font-family: var(--serif);
    font-size: 15px;
    font-weight: 500;
}
.pc-tier {
    font-size: 10px;
    color: var(--emerald-700);
    background: rgba(31, 155, 106, 0.12);
    padding: 2px 7px;
    border-radius: 999px;
    font-weight: 500;
}
.pc-desc {
    font-size: 12px;
    color: var(--ink-500);
    line-height: 1.5;
    margin-bottom: 10px;
}
.pc-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}
.key-badge {
    font-size: 10px;
    padding: 2px 7px;
    border-radius: 999px;
    font-weight: 500;
}
.key-badge.ok { background: rgba(31, 155, 106, 0.15); color: var(--emerald-900); }
.key-badge.missing { background: rgba(196, 150, 90, 0.15); color: #8b6620; }
.pc-link {
    font-size: 12px;
    color: var(--emerald-700);
    font-weight: 500;
}
.pc-link:hover { color: var(--emerald-900); text-decoration: underline; }

.guide-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.guide-step {
    background: var(--paper);
    border-radius: 12px;
    padding: 18px 20px;
    position: relative;
}
.step-num {
    position: absolute;
    top: 14px;
    right: 16px;
    width: 32px;
    height: 32px;
    background: var(--emerald-700);
    color: var(--cream);
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-family: var(--serif);
    font-weight: 600;
    font-size: 16px;
}
.guide-step h4 {
    font-family: var(--serif);
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 8px;
    padding-right: 40px;
}
.guide-step p {
    font-size: 13px;
    color: var(--ink-700);
    line-height: 1.5;
}
.env-snippet {
    background: var(--ink-900);
    color: var(--emerald-300);
    padding: 12px 14px;
    border-radius: 8px;
    font-size: 11px;
    font-family: 'Menlo', monospace;
    line-height: 1.6;
    overflow-x: auto;
    margin: 10px 0 0;
    white-space: pre;
}

@media (max-width: 1100px) {
    .settings-grid { grid-template-columns: 1fr; }
    .provider-grid { grid-template-columns: 1fr; }
    .guide-grid { grid-template-columns: 1fr; }
}
</style>
