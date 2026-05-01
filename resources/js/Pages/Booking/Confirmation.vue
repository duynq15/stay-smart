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
    // After 2s, dispatch event to chatbot to suggest places nearby
    setTimeout(() => {
        window.dispatchEvent(
            new CustomEvent('staysmart:post-booking', {
                detail: {
                    booking_code: props.booking.booking_code,
                    hotel_district: props.booking.hotel.district,
                    hotel_name: props.booking.hotel.name,
                },
            })
        );
    }, 1500);
});
</script>

<template>
    <Head title="Đặt phòng thành công" />
    <AppLayout>
        <section class="confirm-page active">
            <div class="check-circle">
                <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
            <h1>Đặt phòng <em>thành công</em></h1>
            <p>Email xác nhận đã được gửi tới <strong>{{ booking.guest_email }}</strong>. Smarty sẽ hỗ trợ bạn ngay sau đây.</p>

            <div class="confirm-card">
                <div class="confirm-id">
                    <div>
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
                        <label>Nhận phòng</label>
                        <strong>{{ booking.checkin_date }}</strong>
                    </div>
                    <div class="confirm-cell">
                        <label>Trả phòng</label>
                        <strong>{{ booking.checkout_date }}</strong>
                    </div>
                </div>

                <div class="confirm-row">
                    <div class="confirm-cell">
                        <label>Loại phòng</label>
                        <strong>{{ booking.room.name }}</strong>
                    </div>
                    <div class="confirm-cell">
                        <label>Khách · Số đêm</label>
                        <strong>{{ booking.guests_count }} khách · {{ booking.nights }} đêm</strong>
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
