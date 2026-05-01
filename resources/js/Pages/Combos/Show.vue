<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';

defineProps({
    combo: { type: Object, required: true },
    hotels: { type: Array, default: () => [] },
    places: { type: Array, default: () => [] },
});

const placeTypeTag = {
    restaurant: 'Nhà hàng',
    cafe: 'Quán cafe',
    attraction: 'Tham quan',
    shopping: 'Mua sắm',
    bar: 'Quán bar',
    spa: 'Spa',
};

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

function askSmarty(combo) {
    window.dispatchEvent(new CustomEvent('staysmart:open-chat'));
    setTimeout(() => {
        const msg = `Tôi muốn đặt combo ${combo.title}, ${combo.duration}, ngân sách quanh ${fmt(combo.from_price)}đ.`;
        window.dispatchEvent(new CustomEvent('staysmart:prefill-chat', { detail: { message: msg } }));
    }, 600);
}
</script>

<template>
    <Head :title="combo.title" />
    <AppLayout>
        <section class="combo-detail">
            <div style="margin-bottom: 20px">
                <Link :href="route('home')" class="btn btn-ghost" style="padding-left: 0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6" /></svg>
                    Quay lại trang chủ
                </Link>
            </div>

            <div class="combo-hero">
                <img :src="combo.image" :alt="combo.title" @error="$event.target.src = `https://placehold.co/1400x800/14724f/fbf8f1?text=${encodeURIComponent(combo.title)}`" />
                <div class="combo-hero-overlay">
                    <span class="hero-eyebrow" style="margin-bottom: 16px;">Combo · {{ combo.district }}</span>
                    <h1>{{ combo.title }}</h1>
                    <p class="combo-tagline">{{ combo.tagline }}</p>
                    <p class="combo-desc">{{ combo.description }}</p>
                    <div class="combo-actions">
                        <button class="btn btn-emerald" @click="askSmarty(combo)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>
                            Hỏi Smarty về combo này
                        </button>
                        <Link :href="route('hotels.index', { location: combo.district })" class="btn btn-ghost-light">
                            Xem tất cả KS quận này →
                        </Link>
                    </div>
                </div>
            </div>

            <div class="combo-meta-row">
                <div class="meta-item">
                    <label>Thời gian</label>
                    <strong>{{ combo.duration }}</strong>
                </div>
                <div class="meta-item">
                    <label>Khu vực</label>
                    <strong>{{ combo.district }}</strong>
                </div>
                <div class="meta-item">
                    <label>Giá từ</label>
                    <strong>{{ fmt(combo.from_price) }}đ</strong>
                </div>
                <div class="meta-item">
                    <label>Phù hợp</label>
                    <strong>Cặp đôi · Gia đình</strong>
                </div>
            </div>

            <div v-if="combo.highlights?.length" class="highlights-panel">
                <h3>Điểm nổi bật trong combo</h3>
                <div class="highlight-grid">
                    <div v-for="(h, i) in combo.highlights" :key="i" class="highlight-item">
                        <span class="highlight-num">{{ i + 1 }}</span>
                        <span>{{ h }}</span>
                    </div>
                </div>
            </div>

            <div class="section-head" style="margin-top: 48px;">
                <div>
                    <h2>Khách sạn <em>phù hợp</em></h2>
                </div>
                <p>Đã lọc theo {{ combo.district }} và ngân sách combo.</p>
            </div>

            <div v-if="hotels.length === 0" class="empty">
                Chưa có khách sạn phù hợp ngân sách combo. Bạn có thể nới giá ở danh sách KS.
            </div>
            <div v-else class="hotel-grid">
                <Link
                    v-for="hotel in hotels"
                    :key="hotel.id"
                    :href="route('hotels.show', hotel.slug)"
                    class="hotel-card"
                >
                    <div class="img-wrap">
                        <img :src="hotel.image" :alt="hotel.name" />
                        <div v-if="hotel.has_vr_tour" class="vr-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0" /><path d="M3 12h18" /></svg>
                            VR
                        </div>
                        <div class="price-badge">{{ fmt(hotel.base_price) }}đ</div>
                    </div>
                    <div class="info">
                        <h3>{{ hotel.name }}</h3>
                        <div class="loc">📍 {{ hotel.district }}</div>
                        <div class="meta">
                            <div class="rating"><span style="color: var(--gold)">★</span> {{ hotel.rating }} <small>({{ hotel.reviews_count }})</small></div>
                            <div class="price">{{ fmt(hotel.base_price) }}<small>đ/đêm</small></div>
                        </div>
                    </div>
                </Link>
            </div>

            <div class="section-head" style="margin-top: 48px;">
                <div>
                    <h2>Địa điểm <em>quanh khu</em></h2>
                </div>
                <p>Ăn uống · Tham quan · Cafe</p>
            </div>

            <div v-if="places.length === 0" class="empty">
                Chưa có dữ liệu địa điểm cho khu vực này.
            </div>
            <div v-else class="place-detail-grid">
                <div v-for="p in places" :key="p.id" class="place-detail-card">
                    <img :src="p.image_url || `https://placehold.co/400x300/14724f/fbf8f1?text=${encodeURIComponent(p.name)}`" :alt="p.name" />
                    <div class="pd-info">
                        <span class="pd-tag">{{ placeTypeTag[p.type] || p.type }}</span>
                        <h4>{{ p.name }}</h4>
                        <p>📍 {{ p.address }}</p>
                        <div class="pd-meta">
                            <span><span style="color: var(--gold)">★</span> {{ p.rating }}</span>
                            <span v-if="p.avg_price > 0">{{ fmt(p.avg_price) }}đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.combo-detail {
    max-width: 1280px;
    margin: 0 auto;
    padding: 30px 32px 60px;
}
.combo-hero {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    aspect-ratio: 21/9;
    max-height: 460px;
    box-shadow: var(--shadow-lg);
    margin-bottom: 28px;
}
.combo-hero img {
    width: 100%; height: 100%; object-fit: cover;
}
.combo-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(11, 20, 16, 0.92) 0%, rgba(11, 20, 16, 0.55) 50%, transparent 90%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 40px 48px;
    color: var(--cream);
}
.combo-hero-overlay h1 {
    font-family: var(--serif);
    font-size: clamp(32px, 4.5vw, 56px);
    font-weight: 500;
    line-height: 1.05;
    letter-spacing: -0.02em;
    margin-bottom: 8px;
}
.combo-tagline {
    font-size: 15px;
    color: var(--emerald-300);
    font-weight: 500;
    margin-bottom: 12px;
}
.combo-desc {
    max-width: 680px;
    color: rgba(251, 248, 241, 0.85);
    line-height: 1.6;
    font-size: 14px;
    margin-bottom: 20px;
}
.combo-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.btn-ghost-light {
    background: rgba(255, 255, 255, 0.12);
    color: var(--cream);
    backdrop-filter: blur(8px);
    padding: 10px 18px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 500;
}
.btn-ghost-light:hover {
    background: rgba(255, 255, 255, 0.2);
}
.combo-meta-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 16px;
    padding: 22px 28px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 28px;
}
.meta-item label {
    display: block;
    font-size: 11px;
    color: var(--ink-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}
.meta-item strong {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 500;
    color: var(--ink-900);
}
.highlights-panel {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 16px;
    padding: 28px 32px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 8px;
}
.highlights-panel h3 {
    font-family: var(--serif);
    font-size: 22px;
    font-weight: 500;
    margin-bottom: 18px;
}
.highlight-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
}
.highlight-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: var(--paper);
    border-radius: 12px;
    font-size: 14px;
}
.highlight-num {
    width: 28px;
    height: 28px;
    background: var(--emerald-700);
    color: var(--cream);
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-family: var(--serif);
    font-weight: 600;
    font-size: 13px;
    flex-shrink: 0;
}
.empty {
    background: var(--cream);
    border: 1px dashed rgba(11, 20, 16, 0.12);
    border-radius: 14px;
    padding: 40px;
    text-align: center;
    color: var(--ink-500);
    font-size: 14px;
}
.place-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
}
.place-detail-card {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: transform 0.25s, box-shadow 0.25s;
}
.place-detail-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}
.place-detail-card img {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
}
.pd-info {
    padding: 14px 16px 16px;
}
.pd-tag {
    display: inline-block;
    font-size: 10px;
    color: var(--emerald-700);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
    margin-bottom: 4px;
}
.pd-info h4 {
    font-family: var(--serif);
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 6px;
}
.pd-info p {
    font-size: 12px;
    color: var(--ink-500);
    margin-bottom: 8px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.pd-meta {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 500;
}

@media (max-width: 800px) {
    .combo-hero { aspect-ratio: 4/3; }
    .combo-hero-overlay { padding: 24px 24px; }
    .combo-meta-row { grid-template-columns: 1fr 1fr; }
}
</style>
