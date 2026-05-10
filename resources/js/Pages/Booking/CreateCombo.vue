<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    combo: { type: Object, required: true },
    hotels: { type: Array, default: () => [] },
    defaults: { type: Object, default: () => ({}) },
});

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    combo_slug: props.combo.slug,
    hotel_id: null,
    guest_name: user?.name || '',
    guest_email: user?.email || '',
    guest_phone: user?.phone || '',
    checkin_date: props.defaults.checkin_date,
    guests_count: 2,
    special_requests: '',
});

const checkoutDate = computed(() => {
    if (!form.checkin_date) return '';
    const d = new Date(form.checkin_date);
    d.setDate(d.getDate() + (props.combo.nights || 1));
    return d.toISOString().slice(0, 10);
});

const subtotal = computed(() => props.combo.from_price * form.guests_count);
const tax = computed(() => Math.round(subtotal.value * 0.1));
const total = computed(() => subtotal.value + tax.value);

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

function submit() {
    form.post(route('booking.combo.store'));
}
</script>

<template>
    <Head :title="`Đặt tour · ${combo.title}`" />
    <AppLayout :show-chat="false">
        <section class="results-page active" style="max-width: 1280px; margin: 0 auto; padding: 30px 32px 60px;">
            <div style="margin-bottom: 20px;">
                <Link :href="route('combos.show', combo.slug)" class="btn btn-ghost" style="padding-left: 0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6" /></svg>
                    Quay lại combo
                </Link>
            </div>

            <div class="section-head">
                <div>
                    <h2>Đặt tour <em>combo</em></h2>
                </div>
                <p>Bước 1/2 — Điền thông tin và chọn ngày khởi hành.</p>
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
                                <option v-for="n in 8" :key="n" :value="n">{{ n }} người</option>
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
                            <label>Ngày khởi hành</label>
                            <input type="date" v-model="form.checkin_date" required />
                            <small v-if="form.errors.checkin_date" class="err">{{ form.errors.checkin_date }}</small>
                        </div>
                        <div class="form-field">
                            <label>Ngày kết thúc (tự tính)</label>
                            <input type="date" :value="checkoutDate" disabled />
                        </div>
                    </div>

                    <h3 style="margin-top: 28px;">Khách sạn lưu trú (tuỳ chọn)</h3>
                    <p style="font-size: 13px; color: var(--ink-500); margin-bottom: 14px;">
                        Chọn 1 khách sạn trong danh sách để cố định nơi lưu trú trong combo. Để trống nếu muốn STAY-SMART tư vấn sau.
                    </p>
                    <div v-if="hotels.length" class="hotel-pick">
                        <label class="hotel-pick-item" :class="{ active: form.hotel_id === null }">
                            <input type="radio" :value="null" v-model="form.hotel_id" />
                            <div class="hotel-pick-info">
                                <strong>Để Smarty tư vấn</strong>
                                <small>Sẽ liên hệ sau khi xác nhận đơn</small>
                            </div>
                        </label>
                        <label v-for="h in hotels" :key="h.id" class="hotel-pick-item" :class="{ active: form.hotel_id === h.id }">
                            <input type="radio" :value="h.id" v-model.number="form.hotel_id" />
                            <div class="hotel-pick-info">
                                <strong>{{ h.name }}</strong>
                                <small>★ {{ h.rating }} · {{ fmt(h.base_price) }}đ/đêm</small>
                            </div>
                        </label>
                    </div>
                    <small v-if="form.errors.hotel_id" class="err">{{ form.errors.hotel_id }}</small>

                    <div class="form-row" style="margin-top: 24px;">
                        <div class="form-field full">
                            <label>Yêu cầu đặc biệt (tùy chọn)</label>
                            <textarea v-model="form.special_requests" rows="3" placeholder="Đón sân bay, ăn chay, dị ứng..." class="text-area-input"></textarea>
                        </div>
                    </div>
                </div>

                <div class="checkout-summary">
                    <h3>Tóm tắt tour</h3>
                    <div class="summary-hotel">
                        <img :src="combo.image" :alt="combo.title" />
                        <div>
                            <h4>{{ combo.title }}</h4>
                            <small>{{ combo.district }} · {{ combo.duration }}</small><br>
                            <small style="color: var(--emerald-700); font-weight: 500;">Combo trọn gói</small>
                        </div>
                    </div>
                    <ul v-if="combo.highlights?.length" class="summary-highlights">
                        <li v-for="(h, i) in combo.highlights.slice(0, 4)" :key="i">✓ {{ h }}</li>
                    </ul>
                    <div class="summary-row">
                        <span>{{ fmt(combo.from_price) }}đ × {{ form.guests_count }} khách</span>
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
                    <button type="submit" class="btn btn-emerald" style="width: 100%; padding: 14px; justify-content: center; margin-top: 16px;" :disabled="form.processing">
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

.hotel-pick {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 10px;
}
.hotel-pick-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border: 1px solid rgba(11, 20, 16, 0.12);
    border-radius: 12px;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    background: var(--paper);
}
.hotel-pick-item:hover {
    border-color: var(--emerald-500);
}
.hotel-pick-item.active {
    border-color: var(--emerald-700);
    background: rgba(31, 155, 106, 0.06);
}
.hotel-pick-item input {
    accent-color: var(--emerald-700);
}
.hotel-pick-info strong {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-900);
    line-height: 1.3;
}
.hotel-pick-info small {
    font-size: 11px;
    color: var(--ink-500);
}

.summary-highlights {
    list-style: none;
    margin: 0 0 12px;
    padding: 0;
    border-top: 1px dashed rgba(11, 20, 16, 0.12);
    border-bottom: 1px dashed rgba(11, 20, 16, 0.12);
    padding: 10px 0;
}
.summary-highlights li {
    font-size: 12px;
    color: var(--ink-700);
    padding: 3px 0;
}
</style>
