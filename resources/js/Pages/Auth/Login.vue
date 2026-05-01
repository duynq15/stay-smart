<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const clientErrors = ref({});
const touched = ref({});

const markTouched = (f) => (touched.value[f] = true);
const errorOf = (f) => clientErrors.value[f] || form.errors[f] || '';

function validate() {
    const errs = {};
    if (!form.email) errs.email = 'Vui lòng nhập email';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errs.email = 'Email không hợp lệ';
    if (!form.password) errs.password = 'Vui lòng nhập mật khẩu';
    clientErrors.value = errs;
    Object.keys(errs).forEach((k) => (touched.value[k] = true));
    return Object.keys(errs).length === 0;
}

function submit() {
    if (!validate()) return;
    form.post(route('login'), {
        onError: () => Object.keys(form.errors).forEach((k) => (touched.value[k] = true)),
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Đăng nhập" />
    <AppLayout :show-chat="false">
        <section class="auth-section">
            <div class="auth-card">
                <div class="auth-head">
                    <span class="hero-eyebrow">Chào mừng trở lại</span>
                    <h1>Đăng <em>nhập</em></h1>
                    <p>Truy cập tài khoản STAY-SMART để xem lịch sử đặt phòng và ưu đãi cá nhân.</p>
                </div>

                <form @submit.prevent="submit" class="auth-form" novalidate>
                    <div class="form-field full" :class="{ 'has-error': touched.email && errorOf('email') }">
                        <label>Email <span class="req">*</span></label>
                        <input type="email" v-model="form.email" @blur="markTouched('email')" autofocus autocomplete="email" />
                        <small v-if="touched.email && errorOf('email')" class="err">⚠ {{ errorOf('email') }}</small>
                    </div>
                    <div class="form-field full" :class="{ 'has-error': touched.password && errorOf('password') }">
                        <label>Mật khẩu <span class="req">*</span></label>
                        <input type="password" v-model="form.password" @blur="markTouched('password')" autocomplete="current-password" />
                        <small v-if="touched.password && errorOf('password')" class="err">⚠ {{ errorOf('password') }}</small>
                    </div>
                    <label class="remember">
                        <input type="checkbox" v-model="form.remember" />
                        <span>Ghi nhớ đăng nhập</span>
                    </label>

                    <button type="submit" class="btn btn-emerald auth-submit" :disabled="form.processing">
                        {{ form.processing ? 'Đang xử lý...' : 'Đăng nhập' }}
                    </button>

                    <p class="auth-footer">
                        Chưa có tài khoản?
                        <Link :href="route('register')">Đăng ký ngay</Link>
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
    max-width: 460px;
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
.remember {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--ink-700);
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
</style>
