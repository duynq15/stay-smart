<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

const open = ref(false);
const fullscreen = ref(false);
const sessionId = ref(null);
const messages = ref([]);
const quickReplies = ref([]);
const inputText = ref('');
const isTyping = ref(false);
const bodyEl = ref(null);

const pendingPlacesPrompt = ref(null); // { district, hotel_name }

async function startSession() {
    try {
        const res = await axios.post(route('chat.start'));
        sessionId.value = res.data.session_id;
        pushBot(res.data.greeting);
        quickReplies.value = res.data.quick_replies || [];
    } catch (e) {
        pushBot('Xin lỗi, tôi đang gặp sự cố. Bạn thử lại sau nhé.');
    }
}

function openChat(forceFullscreen = false) {
    open.value = true;
    if (forceFullscreen) fullscreen.value = true;
    if (sessionId.value === null) startSession();
    nextTick(() => scrollToBottom());
}

function closeChat() {
    open.value = false;
    fullscreen.value = false;
}

function toggleFullscreen() {
    fullscreen.value = !fullscreen.value;
}

function pushUser(text) {
    messages.value.push({ sender: 'user', text, time: timeNow() });
    quickReplies.value = [];
    nextTick(() => scrollToBottom());
}

function pushBot(text, extras = {}) {
    messages.value.push({ sender: 'bot', text, time: timeNow(), ...extras });
    nextTick(() => scrollToBottom());
}

function timeNow() {
    const d = new Date();
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
}

function scrollToBottom() {
    if (bodyEl.value) {
        bodyEl.value.scrollTop = bodyEl.value.scrollHeight;
    }
}

async function sendMessage(textOverride = null) {
    const text = (textOverride ?? inputText.value).trim();
    if (!text || !sessionId.value) return;
    pushUser(text);
    inputText.value = '';

    isTyping.value = true;
    nextTick(() => scrollToBottom());

    try {
        const res = await axios.post(route('chat.message'), {
            session_id: sessionId.value,
            message: text,
        });
        isTyping.value = false;
        pushBot(res.data.text, {
            hotels: res.data.hotels || null,
            places: res.data.places || null,
        });
        quickReplies.value = res.data.quick_replies || [];
    } catch (e) {
        isTyping.value = false;
        pushBot('Xin lỗi, mạng đang chậm. Bạn thử lại nhé.');
    }
}

async function answerPlacesPrompt(wants) {
    if (!pendingPlacesPrompt.value || !sessionId.value) return;
    const { district, hotel_name } = pendingPlacesPrompt.value;
    pushUser(wants === 'yes' ? 'Có' : 'Không');
    pendingPlacesPrompt.value = null;
    isTyping.value = true;

    try {
        const res = await axios.post(route('chat.places'), {
            session_id: sessionId.value,
            district,
            hotel_name,
            wants,
        });
        isTyping.value = false;
        pushBot(res.data.text, { places: res.data.places || null });
    } catch (e) {
        isTyping.value = false;
        pushBot('Có lỗi rồi, bạn nhắn lại nhé.');
    }
}

function onPostBooking(detail) {
    openChat();
    setTimeout(() => {
        pushBot(`Bạn có muốn tôi gợi ý các địa điểm ăn uống, vui chơi quanh khu vực ${detail.hotel_name} (${detail.hotel_district}) không?`);
        pendingPlacesPrompt.value = { district: detail.hotel_district, hotel_name: detail.hotel_name };
    }, 800);
}

function fmt(p) {
    return new Intl.NumberFormat('vi-VN').format(p);
}

const onOpenEvt = () => openChat();
const onPostBookingEvt = (e) => onPostBooking(e.detail);
const onPrefillEvt = (e) => {
    if (!e.detail?.message) return;
    if (!sessionId.value) {
        // wait for session to start, then send
        const wait = setInterval(() => {
            if (sessionId.value) {
                clearInterval(wait);
                sendMessage(e.detail.message);
            }
        }, 200);
        setTimeout(() => clearInterval(wait), 5000);
    } else {
        sendMessage(e.detail.message);
    }
};

onMounted(() => {
    window.addEventListener('staysmart:open-chat', onOpenEvt);
    window.addEventListener('staysmart:post-booking', onPostBookingEvt);
    window.addEventListener('staysmart:prefill-chat', onPrefillEvt);
});
onBeforeUnmount(() => {
    window.removeEventListener('staysmart:open-chat', onOpenEvt);
    window.removeEventListener('staysmart:post-booking', onPostBookingEvt);
    window.removeEventListener('staysmart:prefill-chat', onPrefillEvt);
});
</script>

<template>
    <div class="chat-panel" :class="{ active: open, fullscreen }">
        <div class="chat-header">
            <div class="chat-avatar">S</div>
            <div>
                <h4>Smarty</h4>
                <small>Trực tuyến · Trợ lý đặt phòng AI</small>
            </div>
            <div class="chat-actions">
                <button class="chat-icon-btn" @click="toggleFullscreen" title="Toàn màn hình">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8V5a2 2 0 0 1 2-2h3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M21 16v3a2 2 0 0 1-2 2h-3" /></svg>
                </button>
                <button class="chat-icon-btn" @click="closeChat" title="Đóng">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div ref="bodyEl" class="chat-body">
            <div v-for="(m, i) in messages" :key="i" class="msg" :class="m.sender">
                <div class="msg-bubble">{{ m.text }}</div>

                <div v-if="m.hotels && m.hotels.length" class="chat-hotels">
                    <a
                        v-for="h in m.hotels"
                        :key="h.id"
                        :href="`/hotels/${h.slug}`"
                        class="chat-hotel-card"
                    >
                        <img :src="h.image" :alt="h.name" />
                        <div class="ch-info">
                            <div>
                                <h5>{{ h.name }}</h5>
                                <div class="ch-loc">📍 {{ h.district }}</div>
                            </div>
                            <div class="ch-meta">
                                <span class="ch-rating"><span style="color: var(--gold)">★</span> {{ h.rating }}</span>
                                <span class="ch-price">{{ fmt(h.price) }}đ</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div v-if="m.places && m.places.length" class="place-grid">
                    <div v-for="p in m.places" :key="p.id" class="place-card">
                        <img :src="p.image_url || 'https://placehold.co/200x150/14724f/fbf8f1?text=Place'" :alt="p.name" />
                        <div class="pc-info">
                            <div class="pc-tag">{{ p.tag }}</div>
                            <h6>{{ p.name }}</h6>
                        </div>
                    </div>
                </div>

                <div v-if="m === messages[messages.length - 1] && pendingPlacesPrompt" class="inline-actions">
                    <button class="inline-yes" @click="answerPlacesPrompt('yes')">Có, gợi ý cho tôi</button>
                    <button class="inline-no" @click="answerPlacesPrompt('no')">Không, cảm ơn</button>
                </div>

                <small class="time">{{ m.time }}</small>
            </div>

            <div v-if="isTyping" class="msg bot">
                <div class="typing">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        <div v-if="quickReplies.length" class="quick-replies">
            <button v-for="q in quickReplies" :key="q" class="quick-reply" @click="sendMessage(q)">{{ q }}</button>
        </div>

        <div class="chat-input-area">
            <div class="chat-input-row">
                <input
                    v-model="inputText"
                    type="text"
                    placeholder="Mô tả mong muốn của bạn..."
                    @keydown.enter="sendMessage()"
                />
                <button class="chat-send" @click="sendMessage()" aria-label="Gửi">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m22 2-7 20-4-9-9-4Z" /><path d="M22 2 11 13" /></svg>
                </button>
            </div>
        </div>
    </div>
</template>
