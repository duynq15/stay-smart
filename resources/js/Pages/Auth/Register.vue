<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const clientErrors = ref({});
const touched = ref({});

function markTouched(field) {
    touched.value[field] = true;
}

function errorOf(field) {
    return clientErrors.value[field] || form.errors[field] || '';
}

function validate() {
    const errs = {};
    if (!(form.name || '').trim()) errs.name = 'Vui lòng nhập họ tên';
    if (!form.email) errs.email = 'Vui lòng nhập email';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errs.email = 'Email không hợp lệ';
    if (form.phone && !/^[0-9+\-\s().]{6,20}$/.test(form.phone)) errs.phone = 'Số điện thoại không hợp lệ';
    if (!form.password) errs.password = 'Vui lòng nhập mật khẩu';
    else if (form.password.length < 6) errs.password = 'Mật khẩu phải tối thiểu 6 ký tự';
    if (!form.password_confirmation) errs.password_confirmation = 'Vui lòng nhập lại mật khẩu';
    else if (form.password !== form.password_confirmation) errs.password_confirmation = 'Mật khẩu nhập lại không khớp';

    clientErrors.value = errs;
    Object.keys(errs).forEach((k) => (touched.value[k] = true));
    return Object.keys(errs).length === 0;
}

function submit() {
    if (!validate()) {
        setTimeout(() => {
            const firstErr = document.querySelector('.form-field.has-error');
            firstErr?.querySelector('input')?.focus();
        }, 50);
        return;
    }
    form.post(route('register'), {
        onError: () => Object.keys(form.errors).forEach((k) => (touched.value[k] = true)),
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Đăng ký" />
    <AppLayout :show-chat="false">
        <section class="auth-section">
            <div class="auth-card">
                <div class="auth-head">
                    <span class="hero-eyebrow">Tham gia STAY-SMART</span>
                    <h1>Tạo <em>tài khoản</em></h1>
                    <p>Đăng ký miễn phí để Smarty ghi nhớ sở thích và đề xuất chính xác hơn.</p>
                </div>

                <form @submit.prevent="submit" class="auth-form" novalidate>
                    <div class="form-field full" :class="{ 'has-error': touched.name && errorOf('name') }">
                        <label>Họ và tên <span class="req">*</span></label>
                        <input type="text" v-model="form.name" @blur="markTouched('name')" autofocus />
                        <small v-if="touched.name && errorOf('name')" class="err">⚠ {{ errorOf('name') }}</small>
                    </div>
                    <div class="form-row">
                        <div class="form-field" :class="{ 'has-error': touched.email && errorOf('email') }">
                            <label>Email <span class="req">*</span></label>
                            <input type="email" v-model="form.email" @blur="markTouched('email')" autocomplete="email" />
                            <small v-if="touched.email && errorOf('email')" class="err">⚠ {{ errorOf('email') }}</small>
                        </div>
                        <div class="form-field" :class="{ 'has-error': touched.phone && errorOf('phone') }">
                            <label>Điện thoại</label>
                            <input type="tel" v-model="form.phone" @blur="markTouched('phone')" />
                            <small v-if="touched.phone && errorOf('phone')" class="err">⚠ {{ errorOf('phone') }}</small>
                        </div>
                    </div>
                    <div class="form-field full" :class="{ 'has-error': touched.password && errorOf('password') }">
                        <label>Mật khẩu <span class="req">*</span></label>
                        <input type="password" v-model="form.password" @blur="markTouched('password')" autocomplete="new-password" />
                        <small v-if="touched.password && errorOf('password')" class="err">⚠ {{ errorOf('password') }}</small>
                    </div>
                    <div class="form-field full" :class="{ 'has-error': touched.password_confirmation && errorOf('password_confirmation') }">
                        <label>Nhập lại mật khẩu <span class="req">*</span></label>
                        <input type="password" v-model="form.password_confirmation" @blur="markTouched('password_confirmation')" autocomplete="new-password" />
                        <small v-if="touched.password_confirmation && errorOf('password_confirmation')" class="err">⚠ {{ errorOf('password_confirmation') }}</small>
                    </div>

                    <button type="submit" class="btn btn-emerald auth-submit" :disabled="form.processing">
                        {{ form.processing ? 'Đang xử lý...' : 'Tạo tài khoản' }}
                    </button>

                    <p class="auth-footer">
                        Đã có tài khoản?
                        <Link :href="route('login')">Đăng nhập</Link>
                    </p>
                </form>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.auth-section {
    max-width: 1080px;
    margin: 0 auto;
    padding: 60px 32px;
    display: grid;
    place-items: center;
    min-height: 70vh;
}
.auth-card {
    background: var(--cream);
    border-radius: 20px;
    padding: 44px;
    width: 100%;
    max-width: 520px;
    border: 1px solid rgba(11, 20, 16, 0.06);
    box-shadow: var(--shadow-md);
}
.auth-head {
    margin-bottom: 28px;
}
.auth-head h1 {
    font-family: var(--serif);
    font-size: 38px;
    font-weight: 500;
    letter-spacing: -0.02em;
    margin: 12px 0 8px;
}
.auth-head h1 em {
    color: var(--emerald-700);
    font-style: italic;
}
.auth-head p {
    color: var(--ink-500);
    font-size: 14px;
}
.auth-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.auth-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.auth-form .form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.auth-form .form-field label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ink-700);
}
.auth-form .form-field input {
    border: 1px solid rgba(11, 20, 16, 0.12);
    background: var(--paper);
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}
.auth-form .form-field input:focus {
    border-color: var(--emerald-500);
}
.auth-form .err {
    color: var(--rust);
    font-size: 12px;
    font-weight: 500;
}
.auth-form .req { color: var(--rust); font-weight: 600; }
.auth-form .form-field.has-error label { color: var(--rust); }
.auth-form .form-field.has-error input {
    border-color: var(--rust);
    background: rgba(184, 92, 60, 0.04);
}
.auth-form .form-field.has-error input:focus {
    border-color: var(--rust);
    box-shadow: 0 0 0 3px rgba(184, 92, 60, 0.15);
}
.auth-submit {
    margin-top: 8px;
    padding: 14px;
    justify-content: center;
}
.auth-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.auth-footer {
    text-align: center;
    font-size: 13px;
    color: var(--ink-500);
    margin-top: 8px;
}
.auth-footer :deep(a) {
    color: var(--emerald-700);
    font-weight: 500;
}
@media (max-width: 600px) {
    .auth-form .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
