<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import '../../css/staysmart-admin.css';

defineProps({
    pageTitle: { type: String, default: '' },
    pageSubtitle: { type: String, default: '' },
});

const page = usePage();
const auth = computed(() => page.props.auth);
const counts = computed(() => page.props.adminCounts || {});
const flash = computed(() => page.props.flash);

const navGroups = [
    {
        title: 'Tổng quan',
        items: [
            { name: 'admin.dashboard', match: 'admin.dashboard', label: 'Dashboard', icon: 'dashboard' },
            { name: 'admin.analytics', match: 'admin.analytics', label: 'Phân tích', icon: 'chart' },
        ],
    },
    {
        title: 'Quản lý',
        items: [
            { name: 'admin.hotels.index', match: 'admin.hotels.*', label: 'Khách sạn', icon: 'hotel', countKey: 'hotels' },
            { name: 'admin.rooms.index', match: 'admin.rooms.*', label: 'Phòng', icon: 'bed', countKey: 'rooms' },
            { name: 'admin.bookings.index', match: 'admin.bookings.*', label: 'Đơn đặt phòng', icon: 'calendar', countKey: 'pendingBookings' },
            { name: 'admin.users.index', match: 'admin.users.*', label: 'Người dùng', icon: 'users' },
            { name: 'admin.reviews.index', match: 'admin.reviews.*', label: 'Đánh giá', icon: 'star' },
            { name: 'admin.places.index', match: 'admin.places.*', label: 'Địa điểm', icon: 'pin' },
        ],
    },
    {
        title: 'Chatbot AI',
        items: [
            { name: 'admin.chats.index', match: 'admin.chats.*', label: 'Lịch sử trò chuyện', icon: 'chat' },
        ],
    },
    {
        title: 'Hệ thống',
        items: [
            { name: 'admin.settings', match: 'admin.settings', label: 'Cài đặt', icon: 'cog' },
        ],
    },
];

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
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">S</div>
                <div>
                    <div class="brand-name">STAY-SMART</div>
                    <div class="brand-tag">Admin Panel</div>
                </div>
            </div>

            <div v-for="group in navGroups" :key="group.title" class="nav-section">
                <div class="nav-section-title">{{ group.title }}</div>
                <Link
                    v-for="item in group.items"
                    :key="item.name"
                    :href="route(item.name)"
                    class="nav-item"
                    :class="{ active: isActive(item.match || item.name) }"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <template v-if="item.icon === 'dashboard'"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></template>
                        <template v-else-if="item.icon === 'chart'"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></template>
                        <template v-else-if="item.icon === 'hotel'"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21V12h6v9"/></template>
                        <template v-else-if="item.icon === 'bed'"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="12" x2="22" y2="12"/></template>
                        <template v-else-if="item.icon === 'calendar'"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></template>
                        <template v-else-if="item.icon === 'users'"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></template>
                        <template v-else-if="item.icon === 'star'"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></template>
                        <template v-else-if="item.icon === 'pin'"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></template>
                        <template v-else-if="item.icon === 'chat'"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></template>
                        <template v-else-if="item.icon === 'cog'"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></template>
                    </svg>
                    {{ item.label }}
                    <span v-if="item.countKey && counts[item.countKey]" class="badge">{{ counts[item.countKey] }}</span>
                </Link>
            </div>

            <div class="sidebar-footer">
                <div class="user-mini">
                    <div class="avatar">{{ auth?.user?.name?.charAt(0).toUpperCase() || 'A' }}</div>
                    <div class="user-mini-info">
                        <strong>{{ auth?.user?.name || 'Admin' }}</strong>
                        <small>{{ auth?.user?.email }}</small>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button" type="button" class="logout-mini">Đăng xuất</Link>
            </div>
        </aside>

        <main class="main">
            <div class="topbar">
                <div class="topbar-search">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--ink-500);"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="text" placeholder="Tìm kiếm khách sạn, đơn đặt, người dùng..." />
                </div>
                <div class="topbar-actions">
                    <Link :href="route('home')" class="btn btn-emerald btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Xem trang user
                    </Link>
                </div>
            </div>

            <div class="content">
                <div v-if="flash?.success" class="admin-flash success">{{ flash.success }}</div>
                <div v-if="flash?.error" class="admin-flash error">{{ flash.error }}</div>

                <div v-if="pageTitle" class="page-head">
                    <div>
                        <h1 class="page-title" v-html="pageTitle"></h1>
                        <p v-if="pageSubtitle" class="page-subtitle">{{ pageSubtitle }}</p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <slot name="actions" />
                    </div>
                </div>

                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.logout-mini {
    margin-top: 12px;
    width: 100%;
    padding: 9px 12px;
    background: rgba(184, 92, 60, 0.1);
    color: var(--rust);
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}
.logout-mini:hover {
    background: var(--rust);
    color: var(--cream);
}
.admin-flash {
    padding: 14px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 500;
}
.admin-flash.success {
    background: rgba(31, 155, 106, 0.12);
    color: var(--emerald-900);
    border-left: 3px solid var(--emerald-500);
}
.admin-flash.error {
    background: rgba(184, 92, 60, 0.12);
    color: var(--rust);
    border-left: 3px solid var(--rust);
}
</style>
