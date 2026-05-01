<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    faqs: { type: Array, required: true },
    contacts: { type: Array, required: true },
});

const openIndex = ref(0);

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

function submit() {
    form.post(route('support.submit'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function openChat() {
    window.dispatchEvent(new CustomEvent('staysmart:open-chat'));
}
</script>

<template>
    <Head title="Hỗ trợ · STAY-SMART" />
    <AppLayout>
        <section class="support-page">
            <div class="support-hero">
                <span class="hero-eyebrow">Hỗ trợ · 24/7</span>
                <h1>Smarty và đội ngũ <em>luôn sẵn sàng</em><br />giúp bạn.</h1>
                <p>Tra cứu câu hỏi thường gặp hoặc liên hệ trực tiếp — chúng tôi phản hồi trong 24h.</p>
                <button class="btn btn-emerald" @click="openChat">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>
                    Chat với Smarty ngay
                </button>
            </div>

            <div class="support-grid">
                <div class="faq-section">
                    <h2 class="block-title">Câu hỏi <em>thường gặp</em></h2>
                    <div class="faq-list">
                        <div
                            v-for="(faq, i) in faqs"
                            :key="i"
                            class="faq-item"
                            :class="{ open: openIndex === i }"
                        >
                            <button class="faq-q" @click="openIndex = openIndex === i ? -1 : i">
                                <span>{{ faq.q }}</span>
                                <svg :class="{ rotated: openIndex === i }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6" /></svg>
                            </button>
                            <div v-if="openIndex === i" class="faq-a">{{ faq.a }}</div>
                        </div>
                    </div>
                </div>

                <div class="side-panel">
                    <div class="contact-block">
                        <h3>Liên hệ trực tiếp</h3>
                        <div class="contact-list">
                            <div v-for="(c, i) in contacts" :key="i" class="contact-row">
                                <span class="contact-icon">{{ c.icon }}</span>
                                <div>
                                    <small>{{ c.label }}</small>
                                    <strong>{{ c.value }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="contact-form-block">
                        <h3>Gửi yêu cầu hỗ trợ</h3>
                        <form @submit.prevent="submit">
                            <div class="ff">
                                <label>Họ tên</label>
                                <input v-model="form.name" type="text" required />
                                <small v-if="form.errors.name" class="err">{{ form.errors.name }}</small>
                            </div>
                            <div class="ff">
                                <label>Email</label>
                                <input v-model="form.email" type="email" required />
                                <small v-if="form.errors.email" class="err">{{ form.errors.email }}</small>
                            </div>
                            <div class="ff">
                                <label>Chủ đề</label>
                                <input v-model="form.subject" type="text" required />
                                <small v-if="form.errors.subject" class="err">{{ form.errors.subject }}</small>
                            </div>
                            <div class="ff">
                                <label>Nội dung</label>
                                <textarea v-model="form.message" rows="4" required></textarea>
                                <small v-if="form.errors.message" class="err">{{ form.errors.message }}</small>
                            </div>
                            <button type="submit" class="btn btn-emerald" style="width: 100%; padding: 12px; justify-content: center;" :disabled="form.processing">
                                {{ form.processing ? 'Đang gửi...' : 'Gửi yêu cầu' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.support-page {
    max-width: 1280px;
    margin: 0 auto;
    padding: 30px 32px 60px;
}
.support-hero {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 24px;
    padding: 56px 48px;
    margin-bottom: 32px;
    text-align: center;
    box-shadow: var(--shadow-sm);
    background:
        radial-gradient(ellipse 60% 50% at 50% 0%, rgba(31, 155, 106, 0.08), transparent 70%),
        radial-gradient(ellipse 40% 30% at 90% 30%, rgba(196, 150, 90, 0.12), transparent 60%),
        var(--cream);
}
.support-hero h1 {
    font-family: var(--serif);
    font-size: clamp(30px, 4.5vw, 52px);
    font-weight: 500;
    line-height: 1.05;
    letter-spacing: -0.02em;
    margin: 16px 0 14px;
}
.support-hero h1 em {
    font-style: italic;
    color: var(--emerald-700);
}
.support-hero p {
    color: var(--ink-500);
    font-size: 15px;
    max-width: 520px;
    margin: 0 auto 24px;
}
.support-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 28px;
}
.block-title {
    font-family: var(--serif);
    font-size: 28px;
    font-weight: 500;
    margin-bottom: 22px;
    letter-spacing: -0.01em;
}
.block-title em {
    font-style: italic;
    color: var(--emerald-700);
}
.faq-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.faq-item {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 12px;
    overflow: hidden;
    transition: border-color 0.2s;
}
.faq-item.open {
    border-color: var(--emerald-300);
    box-shadow: var(--shadow-sm);
}
.faq-q {
    width: 100%;
    padding: 16px 22px;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    font-size: 15px;
    font-weight: 500;
    color: var(--ink-900);
    cursor: pointer;
    background: none;
    border: none;
}
.faq-q svg {
    flex-shrink: 0;
    color: var(--ink-500);
    transition: transform 0.2s;
}
.faq-q svg.rotated {
    transform: rotate(180deg);
    color: var(--emerald-700);
}
.faq-a {
    padding: 0 22px 18px;
    font-size: 14px;
    line-height: 1.65;
    color: var(--ink-500);
}
.side-panel {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.contact-block, .contact-form-block {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 16px;
    padding: 24px 26px;
    box-shadow: var(--shadow-sm);
}
.contact-block h3, .contact-form-block h3 {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 16px;
}
.contact-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.contact-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.contact-icon {
    font-size: 24px;
    flex-shrink: 0;
}
.contact-row small {
    display: block;
    font-size: 11px;
    color: var(--ink-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 2px;
}
.contact-row strong {
    font-size: 14px;
    font-weight: 500;
}
.contact-form-block .ff {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 12px;
}
.contact-form-block label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ink-700);
}
.contact-form-block input, .contact-form-block textarea {
    border: 1px solid rgba(11, 20, 16, 0.12);
    background: var(--paper);
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
    outline: none;
    font-family: inherit;
    transition: border-color 0.2s;
}
.contact-form-block input:focus, .contact-form-block textarea:focus {
    border-color: var(--emerald-500);
}
.err {
    color: var(--rust);
    font-size: 12px;
}

@media (max-width: 900px) {
    .support-grid { grid-template-columns: 1fr; }
}
</style>
