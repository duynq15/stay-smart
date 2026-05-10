<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    booking: { type: Object, required: true },
});

const methodLabels = {
    credit_card: 'Thẻ tín dụng',
    vnpay: 'VNPay',
    momo: 'MoMo',
    bank_transfer: 'Chuyển khoản',
    cash_at_hotel: 'Trả tại KS',
};

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

onMounted(() => {
    // After 1.5s, dispatch event to chatbot to suggest places nearby
    setTimeout(() => {
        const district = props.booking.hotel?.district || props.booking.combo?.district || null;
        const name = props.booking.hotel?.name || props.booking.combo?.title || null;
        window.dispatchEvent(
            new CustomEvent('staysmart:post-booking', {
                detail: {
                    booking_code: props.booking.booking_code,
                    booking_type: props.booking.booking_type,
                    hotel_district: district,
                    hotel_name: name,
                },
            })
        );
    }, 1500);
});
</script>

<template>
    <Head :title="booking.booking_type === 'combo' ? 'Đặt tour thành công' : 'Đặt phòng thành công'" />
    <AppLayout>
        <section class="confirm-page active">
            <div class="check-circle">
                <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
            <h1>{{ booking.booking_type === 'combo' ? 'Đặt tour' : 'Đặt phòng' }} <em>thành công</em></h1>
            <p>Email xác nhận đã được gửi tới <strong>{{ booking.guest_email }}</strong>. Smarty sẽ hỗ trợ bạn ngay sau đây.</p>

            <div class="confirm-card">
                <div class="confirm-id">
                    <div v-if="booking.booking_type === 'combo' && booking.combo">
                        <strong>{{ booking.combo.title }}</strong>
                        <small>{{ booking.combo.district }} · {{ booking.combo.duration }}</small>
                    </div>
                    <div v-else-if="booking.hotel">
                        <strong>{{ booking.hotel.name }}</strong>
                        <small>{{ booking.hotel.address }}</small>
                    </div>
                    <div class="code">{{ booking.booking_code }}</div>
                </div>

                <div class="confirm-row">
                    <div class="confirm-cell">
                        <label>Người đặt</label>
                        <strong>{{ booking.guest_name }}</strong>
                    </div>
                    <div class="confirm-cell">
                        <label>Liên hệ</label>
                        <strong>{{ booking.guest_phone }}</strong>
                    </div>
                </div>

                <div class="confirm-row">
                    <div class="confirm-cell">
                        <label>{{ booking.booking_type === 'combo' ? 'Khởi hành' : 'Nhận phòng' }}</label>
                        <strong>{{ booking.checkin_date }}</strong>
                    </div>
                    <div class="confirm-cell">
                        <label>{{ booking.booking_type === 'combo' ? 'Kết thúc' : 'Trả phòng' }}</label>
                        <strong>{{ booking.checkout_date }}</strong>
                    </div>
                </div>

                <div class="confirm-row">
                    <div class="confirm-cell">
                        <label>{{ booking.booking_type === 'combo' ? 'Khu vực' : 'Loại phòng' }}</label>
                        <strong v-if="booking.booking_type === 'combo'">{{ booking.combo?.district || '—' }}</strong>
                        <strong v-else>{{ booking.room?.name || '—' }}</strong>
                    </div>
                    <div class="confirm-cell">
                        <label>Khách · Số đêm</label>
                        <strong>{{ booking.guests_count }} khách · {{ booking.nights }} đêm</strong>
                    </div>
                </div>

                <div v-if="booking.booking_type === 'combo' && booking.hotel" class="confirm-row">
                    <div class="confirm-cell" style="grid-column: 1 / -1;">
                        <label>Khách sạn lưu trú</label>
                        <strong>{{ booking.hotel.name }} · {{ booking.hotel.district }}</strong>
                    </div>
                </div>

                <div v-if="booking.payment" class="confirm-row">
                    <div class="confirm-cell">
                        <label>Phương thức</label>
                        <strong>{{ methodLabels[booking.payment.method] || booking.payment.method }}</strong>
                    </div>
                    <div class="confirm-cell">
                        <label>Mã giao dịch</label>
                        <strong style="font-family: monospace; font-size: 13px;">{{ booking.payment.transaction_ref }}</strong>
                    </div>
                </div>

                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed rgba(11,20,16,.15);">
                    <div class="summary-row">
                        <span>Tạm tính ({{ booking.nights }} đêm)</span>
                        <span>{{ fmt(booking.subtotal) }}đ</span>
                    </div>
                    <div class="summary-row">
                        <span>Thuế & phí</span>
                        <span>{{ fmt(booking.tax) }}đ</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng đã thanh toán</span>
                        <span>{{ fmt(booking.total_amount) }}đ</span>
                    </div>
                </div>
            </div>

            <div class="confirm-actions">
                <Link :href="route('home')" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m12 19-7-7 7-7" /><path d="M19 12H5" /></svg>
                    Về trang chủ
                </Link>
                <Link :href="route('bookings.index')" class="btn btn-emerald">
                    Xem các đơn của tôi
                </Link>
            </div>
        </section>
    </AppLayout>
</template>
