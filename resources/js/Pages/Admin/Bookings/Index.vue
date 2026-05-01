<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    bookings: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({ ...props.filters });
let debounce;
watch(filters, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.bookings.index'), val, { preserveState: true, replace: true, preserveScroll: true });
    }, 200);
}, { deep: true });

const statusLabels = {
    pending: { label: 'Chờ thanh toán', class: 'st-pending' },
    confirmed: { label: 'Đã xác nhận', class: 'st-confirmed' },
    completed: { label: 'Hoàn tất', class: 'st-completed' },
    cancelled: { label: 'Đã hủy', class: 'st-cancelled' },
};

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

function changeStatus(b, newStatus) {
    if (b.status === newStatus) return;
    if (newStatus === 'cancelled' && !confirm(`Hủy đơn ${b.booking_code}?`)) return;
    router.put(route('admin.bookings.status', b.id), { status: newStatus }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Admin · Đơn đặt phòng" />
    <AdminLayout
        page-title="Quản lý <em>đơn đặt phòng</em>"
        :page-subtitle="`${bookings.total} đơn · cập nhật trạng thái thủ công`"
    >
        <div class="filter-row">
            <div class="search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--ink-500);"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input v-model="filters.q" type="text" placeholder="Tìm mã đơn, tên khách, email..." />
            </div>
            <select v-model="filters.status">
                <option value="">Mọi trạng thái</option>
                <option value="pending">Chờ thanh toán</option>
                <option value="confirmed">Đã xác nhận</option>
                <option value="completed">Hoàn tất</option>
                <option value="cancelled">Đã hủy</option>
            </select>
        </div>

        <div class="panel" style="padding: 0;">
            <div class="table-wrap" style="margin: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách</th>
                            <th>Khách sạn / Phòng</th>
                            <th>Nhận / Trả</th>
                            <th>Đêm · Khách</th>
                            <th>Tổng</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in bookings.data" :key="b.id">
                            <td><strong>{{ b.booking_code }}</strong><br><small style="color: var(--ink-500)">{{ b.created_at }}</small></td>
                            <td>{{ b.guest_name }}<br><small style="color: var(--ink-500)">{{ b.guest_email }}</small></td>
                            <td>{{ b.hotel.name }}<br><small style="color: var(--ink-500)">{{ b.room.name }}</small></td>
                            <td>{{ b.checkin_date }}<br>→ {{ b.checkout_date }}</td>
                            <td>{{ b.nights }} đêm<br>{{ b.guests_count }} khách</td>
                            <td style="font-weight: 500">{{ fmt(b.total_amount) }}đ</td>
                            <td>
                                <template v-if="b.payment">
                                    <small>{{ b.payment.method }}</small><br>
                                    <span class="status-pill" :class="b.payment.status === 'paid' ? 'st-confirmed' : 'st-pending'">{{ b.payment.status }}</span>
                                </template>
                                <small v-else style="color: var(--ink-500)">Chưa</small>
                            </td>
                            <td>
                                <select :value="b.status" @change="changeStatus(b, $event.target.value)" class="status-select" :class="statusLabels[b.status].class">
                                    <option value="pending">Chờ thanh toán</option>
                                    <option value="confirmed">Đã xác nhận</option>
                                    <option value="completed">Hoàn tất</option>
                                    <option value="cancelled">Đã hủy</option>
                                </select>
                            </td>
                        </tr>
                        <tr v-if="bookings.data.length === 0">
                            <td colspan="8" style="text-align: center; padding: 32px; color: var(--ink-500)">Không có đơn nào.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.status-pill {
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 500;
}
.st-pending { background: rgba(196, 150, 90, 0.15); color: #8b6620; }
.st-confirmed { background: rgba(31, 155, 106, 0.15); color: var(--emerald-900); }
.st-completed { background: rgba(11, 20, 16, 0.08); color: var(--ink-700); }
.st-cancelled { background: rgba(184, 92, 60, 0.15); color: var(--rust); }

.status-select {
    border: 1px solid rgba(11, 20, 16, 0.12);
    border-radius: 6px;
    padding: 5px 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
}
</style>
