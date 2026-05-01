<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ChatFab from '@/Components/ChatFab.vue';
import ChatPanel from '@/Components/ChatPanel.vue';

defineProps({
    showChat: { type: Boolean, default: true },
});

const page = usePage();
const auth = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);

const userMenuOpen = ref(false);

function openChat() {
    window.dispatchEvent(new CustomEvent('staysmart:open-chat'));
}

function isActive(name) {
    try {
        const cur = (page.url || '').split('?')[0];

        if (name.endsWith('.*')) {
            const basePath = route(name.replace('.*', '.index'), undefined, false);
            return cur === basePath || cur.startsWith(basePath + '/');
        }

        const targetPath = route(name, undefined, false);
        if (cur === targetPath) return true;

        if (['admin.dashboard', 'home'].includes(name)) return false;

        return cur.startsWith(targetPath + '/');
    } catch {
        return false;
    }
}
</script>

<template>
    <div>
        <nav class="nav">
            <div class="nav-inner">
                <Link :href="route('home')" class="logo">
                    <span class="logo-mark">S</span>
                    STAY-SMART<span class="dot">.</span>
                </Link>
                <div class="nav-links">
                    <Link :href="route('home')" :class="{ 'nav-active': isActive('home') }">Trang chủ</Link>
                    <Link :href="route('hotels.index')" :class="{ 'nav-active': isActive('hotels.*') }">Khách sạn</Link>
                    <Link :href="route('combos.index')" :class="{ 'nav-active': isActive('combos.*') }">Combo Tour</Link>
                    <Link :href="route('promotions')" :class="{ 'nav-active': isActive('promotions') }">Khuyến mãi</Link>
                    <Link :href="route('support')" :class="{ 'nav-active': isActive('support') }">Hỗ trợ</Link>
                </div>
                <div class="nav-auth">
                    <template v-if="!auth?.user">
                        <Link :href="route('login')" class="btn btn-ghost">Đăng nhập</Link>
                        <Link :href="route('register')" class="btn btn-primary">Đăng ký</Link>
                    </template>
                    <template v-else>
                        <div class="user-menu" @click.stop="userMenuOpen = !userMenuOpen">
                            <img v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" class="user-avatar-img" />
                            <div v-else class="user-avatar-fallback">{{ auth.user.name?.charAt(0) || 'U' }}</div>
                            <span class="user-name">{{ auth.user.name }}</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                            <div v-if="userMenuOpen" class="user-dropdown" @click.stop>
                                <Link :href="route('bookings.index')">Đơn đặt phòng</Link>
                                <Link v-if="auth.user.is_admin" :href="route('admin.dashboard')">Trang quản trị</Link>
                                <Link :href="route('logout')" method="post" as="button" type="button" class="logout-btn">Đăng xuất</Link>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </nav>

        <div v-if="flash?.success" class="flash-toast flash-success">{{ flash.success }}</div>
        <div v-if="flash?.error" class="flash-toast flash-error">{{ flash.error }}</div>

        <main>
            <slot />
        </main>

        <ChatFab v-if="showChat" @open="openChat" />
        <ChatPanel />
    </div>
</template>

<style scoped>
:deep(.nav-links a) {
    position: relative;
    padding: 6px 4px;
    font-weight: 500;
    transition: color 0.2s;
}
:deep(.nav-links a.nav-active) {
    color: var(--emerald-700);
    font-weight: 600;
}
:deep(.nav-links a.nav-active::after) {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: -22px;
    height: 2px;
    background: var(--emerald-700);
    border-radius: 2px;
}
.user-menu {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px 6px 6px;
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s;
}
.user-menu:hover {
    background: rgba(11, 20, 16, 0.05);
}
.user-avatar-img,
.user-avatar-fallback {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--emerald-700);
    color: var(--cream);
    display: grid;
    place-items: center;
    font-weight: 600;
    font-size: 13px;
}
.user-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--ink-700);
}
.user-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.08);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    min-width: 180px;
    padding: 6px;
    display: flex;
    flex-direction: column;
    z-index: 60;
}
.user-dropdown :deep(a),
.user-dropdown .logout-btn {
    padding: 9px 12px;
    font-size: 14px;
    color: var(--ink-700);
    border-radius: 8px;
    text-align: left;
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
}
.user-dropdown :deep(a:hover),
.user-dropdown .logout-btn:hover {
    background: var(--paper);
    color: var(--emerald-900);
}
.flash-toast {
    position: fixed;
    top: 80px;
    right: 24px;
    padding: 14px 22px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: var(--shadow-md);
    z-index: 70;
    animation: slideInRight 0.3s ease;
}
.flash-success {
    background: var(--emerald-700);
    color: var(--cream);
}
.flash-error {
    background: var(--rust);
    color: var(--cream);
}
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
