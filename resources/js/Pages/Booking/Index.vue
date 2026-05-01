<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    bookings: { type: Object, required: true },
});

const statusLabel = {
    pending: { label: 'Chờ thanh toán', class: 'st-pending' },
    confirmed: { label: 'Đã xác nhận', class: 'st-confirmed' },
    completed: { label: 'Đã hoàn thành', class: 'st-completed' },
    cancelled: { label: 'Đã hủy', class: 'st-cancelled' },
};

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}
</script>

<template>
    <Head title="Đơn đặt phòng của tôi" />
    <AppLayout>
        <section class="results-page active" style="max-width: 1080px; margin: 0 auto; padding: 30px 32px 60px;">
            <div class="section-head">
                <div>
                    <h2>Đơn <em>đặt phòng</em> của tôi</h2>
                </div>
                <p>{{ bookings.total }} đơn đặt phòng</p>
            </div>

            <div v-if="bookings.data.length === 0" class="empty-bookings">
                <p>Chưa có đơn đặt phòng nào.</p>
                <Link :href="route('hotels.index')" class="btn btn-emerald" style="margin-top: 16px;">Tìm khách sạn ngay</Link>
            </div>

            <div v-else class="booking-list">
                <div v-for="b in bookings.data" :key="b.id" class="booking-card">
                    <div class="booking-main">
                        <div class="booking-header">
                            <div>
                                <h3>{{ b.hotel.name }}</h3>
                                <small>{{ b.hotel.district }} · {{ b.room.name }}</small>
                            </div>
                            <span class="status-badge" :class="statusLabel[b.status].class">{{ statusLabel[b.status].label }}</span>
                        </div>
                        <div class="booking-meta">
                            <div><label>Mã đơn</label><strong>{{ b.booking_code }}</strong></div>
                            <div><label>Nhận phòng</label><strong>{{ b.checkin_date }}</strong></div>
                            <div><label>Trả phòng</label><strong>{{ b.checkout_date }}</strong></div>
                            <div><label>Tổng</label><strong>{{ fmt(b.total_amount) }}đ</strong></div>
                        </div>
                    </div>
                    <div class="booking-actions">
                        <Link v-if="b.status === 'pending'" :href="route('booking.payment', b.booking_code)" class="btn btn-emerald">Thanh toán</Link>
                        <Link :href="route('booking.confirmation', b.booking_code)" class="btn btn-ghost">Chi tiết</Link>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.empty-bookings {
    text-align: center;
    padding: 60px 20px;
    background: var(--cream);
    border-radius: 16px;
    border: 1px dashed rgba(11, 20, 16, 0.12);
    color: var(--ink-500);
}
.booking-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.booking-card {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 16px;
    padding: 20px 24px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    align-items: center;
    box-shadow: var(--shadow-sm);
    transition: border-color 0.2s;
}
.booking-card:hover {
    border-color: var(--emerald-300);
}
.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 12px;
    margin-bottom: 14px;
}
.booking-header h3 {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 2px;
}
.booking-header small {
    font-size: 12px;
    color: var(--ink-500);
}
.status-badge {
    padding: 5px 11px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    flex-shrink: 0;
}
.st-pending { background: rgba(196, 150, 90, 0.15); color: #8b6620; }
.st-confirmed { background: rgba(31, 155, 106, 0.15); color: var(--emerald-900); }
.st-completed { background: rgba(11, 20, 16, 0.08); color: var(--ink-700); }
.st-cancelled { background: rgba(184, 92, 60, 0.15); color: var(--rust); }

.booking-meta {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.booking-meta label {
    font-size: 10px;
    color: var(--ink-500);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 3px;
}
.booking-meta strong {
    font-size: 13px;
    font-weight: 500;
}
.booking-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
@media (max-width: 700px) {
    .booking-card {
        grid-template-columns: 1fr;
    }
    .booking-meta {
        grid-template-columns: 1fr 1fr;
    }
    .booking-actions {
        flex-direction: row;
    }
}
</style>
