<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    session: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Admin · Chat #${session.id}`" />
    <AdminLayout
        :page-title="`Phiên chat <em>#${session.id}</em>`"
        :page-subtitle="session.user ? `${session.user.name} · ${session.started_at}` : `Khách vãng lai · ${session.started_at}`"
    >
        <template #actions>
            <Link :href="route('admin.chats.index')" class="btn btn-ghost btn-sm">← Quay lại</Link>
        </template>

        <div class="panel" style="max-width: 700px;">
            <div class="transcript">
                <div v-for="m in session.messages" :key="m.id" class="msg-line" :class="m.sender">
                    <div class="msg-bubble">{{ m.content }}</div>
                    <small class="time">{{ m.time }}</small>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.transcript {
    display: flex;
    flex-direction: column;
    gap: 14px;
    max-height: 70vh;
    overflow-y: auto;
}
.msg-line {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 75%;
}
.msg-line.user { align-self: flex-end; align-items: flex-end; }
.msg-line.bot { align-self: flex-start; }
.msg-bubble {
    padding: 12px 16px;
    border-radius: 14px;
    font-size: 14px;
    line-height: 1.5;
    white-space: pre-wrap;
}
.msg-line.user .msg-bubble {
    background: var(--emerald-700);
    color: var(--cream);
    border-bottom-right-radius: 4px;
}
.msg-line.bot .msg-bubble {
    background: var(--paper);
    color: var(--ink-900);
    border-bottom-left-radius: 4px;
}
.time { font-size: 10px; color: var(--ink-500); padding: 0 6px; }
</style>
