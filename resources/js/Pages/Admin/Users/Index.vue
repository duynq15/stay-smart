<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({ ...props.filters });
let debounce;
watch(filters, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.users.index'), val, { preserveState: true, replace: true, preserveScroll: true });
    }, 200);
}, { deep: true });

function changeRole(u, role) {
    if (u.role === role) return;
    if (!confirm(`Đổi vai trò ${u.name} thành ${role}?`)) return;
    router.put(route('admin.users.role', u.id), { role }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Admin · Người dùng" />
    <AdminLayout page-title="Quản lý <em>người dùng</em>" :page-subtitle="`${users.total} tài khoản`">
        <div class="filter-row">
            <div class="search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--ink-500);"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input v-model="filters.q" type="text" placeholder="Tìm theo tên, email, SĐT..." />
            </div>
            <select v-model="filters.role">
                <option value="">Tất cả vai trò</option>
                <option value="customer">Khách hàng</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="panel" style="padding: 0;">
            <div class="table-wrap" style="margin: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người dùng</th>
                            <th>Liên hệ</th>
                            <th>Đơn đặt</th>
                            <th>Đăng ký</th>
                            <th>Vai trò</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users.data" :key="u.id">
                            <td>#{{ u.id }}</td>
                            <td>
                                <div class="user-cell">
                                    <img v-if="u.avatar" :src="u.avatar" :alt="u.name" />
                                    <div v-else class="avatar-fallback">{{ u.name?.charAt(0).toUpperCase() }}</div>
                                    <strong>{{ u.name }}</strong>
                                </div>
                            </td>
                            <td>{{ u.email }}<br><small style="color: var(--ink-500)">{{ u.phone || '—' }}</small></td>
                            <td>{{ u.bookings_count }}</td>
                            <td>{{ u.created_at || '—' }}</td>
                            <td>
                                <select :value="u.role" @change="changeRole(u, $event.target.value)" class="role-select">
                                    <option value="customer">Khách hàng</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.user-cell img, .avatar-fallback {
    width: 32px; height: 32px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--emerald-700);
    color: var(--cream);
    display: grid;
    place-items: center;
    font-weight: 600;
    font-size: 13px;
}
.role-select {
    border: 1px solid rgba(11, 20, 16, 0.12);
    border-radius: 6px;
    padding: 5px 8px;
    font-size: 12px;
    cursor: pointer;
}
</style>
