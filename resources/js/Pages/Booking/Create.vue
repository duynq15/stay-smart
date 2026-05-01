<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    hotel: { type: Object, required: true },
    room: { type: Object, required: true },
    defaults: { type: Object, default: () => ({}) },
});

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    hotel_id: props.hotel.id,
    room_id: props.room.id,
    guest_name: user?.name || '',
    guest_email: user?.email || '',
    guest_phone: user?.phone || '',
    checkin_date: props.defaults.checkin_date,
    checkout_date: props.defaults.checkout_date,
    guests_count: 2,
    special_requests: '',
});

const nights = computed(() => {
    if (!form.checkin_date || !form.checkout_date) return 0;
    const a = new Date(form.checkin_date);
    const b = new Date(form.checkout_date);
    return Math.max(0, Math.round((b - a) / 86400000));
});
const subtotal = computed(() => props.room.price_per_night * nights.value);
const tax = computed(() => Math.round(subtotal.value * 0.1));
const total = computed(() => subtotal.value + tax.value);

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

function submit() {
    form.post(route('booking.store'));
}
</script>

<template>
    <Head :title="`Đặt phòng · ${hotel.name}`" />
    <AppLayout :show-chat="false">
        <section class="results-page active" style="max-width: 1280px; margin: 0 auto; padding: 30px 32px 60px;">
            <div style="margin-bottom: 20px;">
                <Link :href="route('hotels.show', hotel.slug)" class="btn btn-ghost" style="padding-left: 0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6" /></svg>
                    Quay lại khách sạn
                </Link>
            </div>

            <div class="section-head">
                <div>
                    <h2>Hoàn tất <em>thông tin</em></h2>
                </div>
                <p>Bước 1/2 — Điền thông tin khách lưu trú và ngày nhận phòng.</p>
            </div>

            <form @submit.prevent="submit" class="checkout-grid">
                <div class="checkout-form">
                    <h3>Thông tin khách</h3>
                    <div class="form-row">
                        <div class="form-field">
                            <label>Họ tên</label>
                            <input type="text" v-model="form.guest_name" required />
                            <small v-if="form.errors.guest_name" class="err">{{ form.errors.guest_name }}</small>
                        </div>
                        <div class="form-field">
                            <label>Số khách</label>
                            <select v-model.number="form.guests_count">
                                <option v-for="n in 6" :key="n" :value="n">{{ n }} người</option>
                            </select>
                            <small v-if="form.errors.guests_count" class="err">{{ form.errors.guests_count }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label>Email</label>
                            <input type="email" v-model="form.guest_email" required />
                            <small v-if="form.errors.guest_email" class="err">{{ form.errors.guest_email }}</small>
                        </div>
                        <div class="form-field">
                            <label>Số điện thoại</label>
                            <input type="tel" v-model="form.guest_phone" required />
                            <small v-if="form.errors.guest_phone" class="err">{{ form.errors.guest_phone }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label>Nhận phòng</label>
                            <input type="date" v-model="form.checkin_date" required />
                            <small v-if="form.errors.checkin_date" class="err">{{ form.errors.checkin_date }}</small>
                        </div>
                        <div class="form-field">
                            <label>Trả phòng</label>
                            <input type="date" v-model="form.checkout_date" required />
                            <small v-if="form.errors.checkout_date" class="err">{{ form.errors.checkout_date }}</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field full">
                            <label>Yêu cầu đặc biệt (tùy chọn)</label>
                            <textarea v-model="form.special_requests" rows="3" placeholder="Phòng tầng cao, view đẹp, đón sân bay..." class="text-area-input"></textarea>
                        </div>
                    </div>
                </div>

                <div class="checkout-summary">
                    <h3>Tóm tắt đơn</h3>
                    <div class="summary-hotel">
                        <img :src="hotel.image" :alt="hotel.name" />
                        <div>
                            <h4>{{ hotel.name }}</h4>
                            <small>{{ hotel.district }}</small><br>
                            <small style="color: var(--emerald-700); font-weight: 500;">{{ room.name }}</small>
                        </div>
                    </div>
                    <div class="summary-row">
                        <span>{{ fmt(room.price_per_night) }}đ × {{ nights }} đêm</span>
                        <span>{{ fmt(subtotal) }}đ</span>
                    </div>
                    <div class="summary-row">
                        <span>Thuế & phí (10%)</span>
                        <span>{{ fmt(tax) }}đ</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng</span>
                        <span>{{ fmt(total) }}đ</span>
                    </div>
                    <button type="submit" class="btn btn-emerald" style="width: 100%; padding: 14px; justify-content: center; margin-top: 16px;" :disabled="form.processing || nights === 0">
                        {{ form.processing ? 'Đang xử lý...' : 'Tiếp tục thanh toán →' }}
                    </button>
                    <p style="font-size: 11px; color: var(--ink-500); text-align: center; margin-top: 10px;">Demo · Không tính phí thật</p>
                </div>
            </form>
        </section>
    </AppLayout>
</template>

<style scoped>
.err {
    color: var(--rust);
    font-size: 12px;
}
.text-area-input {
    border: 1px solid rgba(11, 20, 16, 0.12);
    background: var(--paper);
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 14px;
    outline: none;
    font-family: inherit;
    resize: vertical;
}
.text-area-input:focus {
    border-color: var(--emerald-500);
}
</style>
