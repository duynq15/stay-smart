<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    sessions: { type: Object, required: true },
});
</script>

<template>
    <Head title="Admin · Lịch sử chat" />
    <AdminLayout page-title="Lịch sử <em>trò chuyện</em>" :page-subtitle="`${sessions.total} phiên chat với Smarty`">
        <div class="panel" style="padding: 0;">
            <div class="table-wrap" style="margin: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Phiên #</th>
                            <th>Người dùng</th>
                            <th>Bắt đầu</th>
                            <th>Số tin nhắn</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in sessions.data" :key="s.id">
                            <td>#{{ s.id }}</td>
                            <td>
                                <template v-if="s.user">
                                    <strong>{{ s.user.name }}</strong><br>
                                    <small style="color: var(--ink-500)">{{ s.user.email }}</small>
                                </template>
                                <small v-else style="color: var(--ink-500)">Khách vãng lai</small>
                            </td>
                            <td>{{ s.started_at }}</td>
                            <td>{{ s.message_count }}</td>
                            <td>
                                <Link :href="route('admin.chats.show', s.id)" class="row-btn">Xem</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.row-btn { padding: 5px 10px; border-radius: 6px; font-size: 12px; background: var(--paper); color: var(--ink-700); border: none; cursor: pointer; }
.row-btn:hover { background: var(--ink-100); }
</style>
