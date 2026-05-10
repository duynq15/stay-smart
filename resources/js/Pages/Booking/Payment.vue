<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    booking: { type: Object, required: true },
});

const form = useForm({
    method: 'credit_card',
});

const methods = [
    { key: 'credit_card', icon: '💳', label: 'Thẻ tín dụng' },
    { key: 'vnpay', icon: '🏦', label: 'VNPay' },
    { key: 'momo', icon: '📱', label: 'MoMo' },
    { key: 'bank_transfer', icon: '🏧', label: 'Chuyển khoản' },
    { key: 'cash_at_hotel', icon: '💵', label: 'Trả tại KS' },
];

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

function submit() {
    form.post(route('booking.process', props.booking.booking_code));
}
</script>

<template>
    <Head :title="booking.booking_type === 'combo' ? 'Thanh toán đặt tour' : 'Thanh toán đặt phòng'" />
    <AppLayout :show-chat="false">
        <section class="results-page active" style="max-width: 1280px; margin: 0 auto; padding: 30px 32px 60px;">
            <div class="section-head">
                <div>
                    <h2>Bước 2 — <em>Thanh toán</em></h2>
                </div>
                <p>Mã đơn: <strong>{{ booking.booking_code }}</strong> · Demo, không tính phí thật.</p>
            </div>

            <form @submit.prevent="submit" class="checkout-grid">
                <div class="checkout-form">
                    <h3>Phương thức thanh toán</h3>
                    <div class="pay-methods">
                        <button
                            v-for="m in methods"
                            :key="m.key"
                            type="button"
                            class="pay-method"
                            :class="{ active: form.method === m.key }"
                            @click="form.method = m.key"
                        >
                            <div class="pay-method-icon">{{ m.icon }}</div>
                            {{ m.label }}
                        </button>
                    </div>

                    <div v-if="form.method === 'credit_card'" class="card-fields">
                        <div class="form-row" style="grid-template-columns: 1fr;">
                            <div class="form-field full">
                                <label>Số thẻ</label>
                                <input type="text" placeholder="4242 4242 4242 4242" maxlength="19" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-field">
                                <label>Hạn thẻ</label>
                                <input type="text" placeholder="MM/YY" maxlength="5" />
                            </div>
                            <div class="form-field">
                                <label>CVC</label>
                                <input type="text" placeholder="123" maxlength="4" />
                            </div>
                        </div>
                        <p style="font-size: 12px; color: var(--ink-500); margin-top: 8px;">📌 Demo · các trường thẻ chỉ trang trí, không lưu thông tin.</p>
                    </div>

                    <div v-else-if="form.method === 'vnpay' || form.method === 'momo'" class="qr-placeholder">
                        <div class="qr-box">📱</div>
                        <p>Quét mã QR bằng app {{ form.method === 'vnpay' ? 'VNPay' : 'MoMo' }} (demo)</p>
                    </div>

                    <div v-else-if="form.method === 'bank_transfer'" class="bank-info">
                        <p><strong>Ngân hàng:</strong> Vietcombank</p>
                        <p><strong>STK:</strong> 0123 4567 8901</p>
                        <p><strong>Chủ TK:</strong> CTY STAY-SMART</p>
                        <p><strong>Nội dung:</strong> {{ booking.booking_code }}</p>
                    </div>

                    <div v-else class="cash-note">
                        <p>💵 Bạn sẽ thanh toán <strong>{{ fmt(booking.total_amount) }}đ</strong> bằng tiền mặt khi nhận phòng tại khách sạn.</p>
                    </div>
                </div>

                <div class="checkout-summary">
                    <h3>Đơn của bạn</h3>
                    <div v-if="booking.booking_type === 'combo' && booking.combo" class="summary-hotel">
                        <img :src="booking.combo.image" :alt="booking.combo.title" />
                        <div>
                            <h4>{{ booking.combo.title }}</h4>
                            <small>{{ booking.combo.district }} · {{ booking.combo.duration }}</small><br>
                            <small style="color: var(--emerald-700); font-weight: 500;">Combo trọn gói</small>
                        </div>
                    </div>
                    <div v-else-if="booking.hotel" class="summary-hotel">
                        <img :src="booking.hotel.image" :alt="booking.hotel.name" />
                        <div>
                            <h4>{{ booking.hotel.name }}</h4>
                            <small>{{ booking.hotel.district }}</small><br>
                            <small v-if="booking.room" style="color: var(--emerald-700); font-weight: 500;">{{ booking.room.name }}</small>
                        </div>
                    </div>
                    <div class="summary-row">
                        <span>Khách</span>
                        <span>{{ booking.guest_name }}</span>
                    </div>
                    <div class="summary-row">
                        <span>{{ booking.booking_type === 'combo' ? 'Khởi hành / Kết thúc' : 'Nhận / Trả' }}</span>
                        <span>{{ booking.checkin_date }} → {{ booking.checkout_date }}</span>
                    </div>
                    <div class="summary-row">
                        <span>{{ booking.booking_type === 'combo' ? 'Số đêm · Khách' : 'Số đêm' }}</span>
                        <span>{{ booking.nights }}{{ booking.booking_type === 'combo' ? ` · ${booking.guests_count} khách` : '' }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span>{{ fmt(booking.subtotal) }}đ</span>
                    </div>
                    <div class="summary-row">
                        <span>Thuế & phí</span>
                        <span>{{ fmt(booking.tax) }}đ</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng</span>
                        <span>{{ fmt(booking.total_amount) }}đ</span>
                    </div>
                    <button type="submit" class="btn btn-emerald" style="width: 100%; padding: 14px; justify-content: center; margin-top: 16px;" :disabled="form.processing">
                        {{ form.processing ? 'Đang xử lý...' : `Thanh toán ${fmt(booking.total_amount)}đ` }}
                    </button>
                </div>
            </form>
        </section>
    </AppLayout>
</template>

<style scoped>
.card-fields {
    margin-top: 20px;
}
.qr-placeholder {
    margin-top: 24px;
    text-align: center;
    padding: 32px;
    background: var(--paper);
    border-radius: 12px;
    border: 1px dashed rgba(11, 20, 16, 0.15);
}
.qr-box {
    font-size: 64px;
    margin-bottom: 12px;
}
.bank-info {
    margin-top: 24px;
    padding: 20px;
    background: var(--paper);
    border-radius: 12px;
    line-height: 2;
    font-size: 14px;
}
.cash-note {
    margin-top: 24px;
    padding: 20px;
    background: rgba(196, 150, 90, 0.08);
    border-left: 3px solid var(--gold);
    border-radius: 8px;
    font-size: 14px;
}
</style>
