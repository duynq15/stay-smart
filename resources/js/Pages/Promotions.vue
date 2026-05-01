<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    hotels: { type: Array, required: true },
    banners: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const sort = ref(props.filters.sort || 'discount');

let debounce;
watch(sort, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('promotions'), { sort: val }, { preserveState: true, preserveScroll: true, replace: true });
    }, 200);
});

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}
</script>

<template>
    <Head title="Khuyến mãi · Ưu đãi đặt phòng" />
    <AppLayout>
        <section class="promotions-page">
            <div class="promo-hero">
                <div class="promo-hero-bg"></div>
                <div class="promo-hero-content">
                    <span class="hero-eyebrow">Ưu đãi · Hà Nội</span>
                    <h1>Khuyến mãi <em>hấp dẫn</em><br />đang chờ bạn.</h1>
                    <p>Tiết kiệm tới 30% giá phòng với các deal được Smarty tuyển chọn riêng cho khách hàng STAY-SMART.</p>
                </div>
            </div>

            <div class="banner-row">
                <div v-for="(b, i) in banners" :key="i" class="banner-card" :style="{ background: b.color }">
                    <span class="banner-icon">{{ b.icon }}</span>
                    <div>
                        <strong>{{ b.title }}</strong>
                        <small>{{ b.tagline }}</small>
                    </div>
                </div>
            </div>

            <div class="section-head" style="margin-top: 40px;">
                <div>
                    <h2>Deal <em>nóng nhất</em> tuần này</h2>
                </div>
                <p>{{ hotels.length }} ưu đãi · cập nhật mỗi 24h</p>
            </div>

            <div class="sort-bar">
                <label>Sắp xếp:</label>
                <select v-model="sort">
                    <option value="discount">Giảm giá nhiều nhất</option>
                    <option value="name">Tên A-Z</option>
                    <option value="popular">Phổ biến nhất</option>
                    <option value="newest">Mới nhất</option>
                    <option value="price-asc">Giá thấp → cao</option>
                    <option value="price-desc">Giá cao → thấp</option>
                </select>
            </div>

            <div class="hotel-grid">
                <Link v-for="hotel in hotels" :key="hotel.id" :href="route('hotels.show', hotel.slug)" class="hotel-card promo-card">
                    <div class="img-wrap">
                        <img :src="hotel.image" :alt="hotel.name" />
                        <div class="discount-badge">-{{ hotel.discount_percent }}%</div>
                        <div v-if="hotel.has_vr_tour" class="vr-badge">VR</div>
                    </div>
                    <div class="info">
                        <h3>{{ hotel.name }}</h3>
                        <div class="loc">📍 {{ hotel.district }}</div>
                        <div class="meta">
                            <div class="rating"><span style="color: var(--gold)">★</span> {{ hotel.rating }} <small>({{ hotel.reviews_count }})</small></div>
                            <div class="price-block">
                                <small class="strike">{{ fmt(hotel.original_price) }}đ</small>
                                <span class="price">{{ fmt(hotel.base_price) }}<small>đ/đêm</small></span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.promotions-page {
    max-width: 1280px;
    margin: 0 auto;
    padding: 30px 32px 60px;
}
.promo-hero {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    padding: 64px 48px;
    margin-bottom: 32px;
    background: linear-gradient(135deg, #0a3d2e 0%, #14724f 50%, #b85c3c 100%);
}
.promo-hero-bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 50% at 80% 30%, rgba(196, 150, 90, 0.4), transparent 60%),
        radial-gradient(ellipse 40% 40% at 20% 80%, rgba(110, 209, 168, 0.3), transparent 60%);
}
.promo-hero-content {
    position: relative;
    color: var(--cream);
    max-width: 620px;
}
.promo-hero-content .hero-eyebrow {
    background: rgba(255, 255, 255, 0.15);
    color: var(--cream);
}
.promo-hero h1 {
    font-family: var(--serif);
    font-size: clamp(34px, 5vw, 60px);
    font-weight: 500;
    line-height: 1.05;
    letter-spacing: -0.02em;
    margin: 16px 0 14px;
}
.promo-hero h1 em {
    font-style: italic;
    color: var(--emerald-300);
    font-weight: 400;
}
.promo-hero p {
    color: rgba(251, 248, 241, 0.85);
    font-size: 16px;
    line-height: 1.6;
    max-width: 520px;
}
.sort-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}
.sort-bar label {
    font-size: 13px;
    color: var(--ink-500);
    font-weight: 500;
}
.sort-bar select {
    border: 1px solid rgba(11, 20, 16, 0.12);
    background: var(--cream);
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    color: var(--ink-900);
    outline: none;
    cursor: pointer;
    transition: border-color 0.2s;
}
.sort-bar select:focus {
    border-color: var(--emerald-500);
}
.banner-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}
.banner-card {
    color: var(--cream);
    border-radius: 14px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: var(--shadow-sm);
}
.banner-icon {
    font-size: 28px;
    flex-shrink: 0;
}
.banner-card strong {
    display: block;
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 500;
}
.banner-card small {
    display: block;
    font-size: 12px;
    opacity: 0.85;
    margin-top: 2px;
}
.promo-card { position: relative; }
.discount-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: var(--rust);
    color: var(--cream);
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    z-index: 2;
}
.promo-card .vr-badge {
    top: 12px;
    right: 12px;
    left: auto;
}
.price-block {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
.strike {
    color: var(--ink-300);
    text-decoration: line-through;
    font-size: 11px;
    font-weight: 400;
}
.price {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 600;
    color: var(--rust);
}

@media (max-width: 800px) {
    .banner-row { grid-template-columns: 1fr; }
    .promo-hero { padding: 40px 28px; }
}
</style>
