<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    combos: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const sort = ref(props.filters.sort || 'recommended');

let debounce;
watch(sort, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('combos.index'), { sort: val }, { preserveState: true, preserveScroll: true, replace: true });
    }, 200);
});

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}
</script>

<template>
    <Head title="Combo du lịch trọn gói" />
    <AppLayout>
        <section class="combos-list">
            <div class="section-head" style="padding: 60px 32px 0;">
                <div>
                    <span class="hero-eyebrow">5 combo · Hà Nội</span>
                    <h2 style="margin-top: 12px;">Combo <em>du lịch</em> trọn gói</h2>
                </div>
                <p>Khách sạn + ăn uống + tham quan, gói gọn theo từng quận đặc trưng.</p>
            </div>

            <div class="sort-bar">
                <label>Sắp xếp:</label>
                <select v-model="sort">
                    <option value="recommended">Đề xuất</option>
                    <option value="name">Tên A-Z</option>
                    <option value="popular">Phổ biến (nhiều KS phù hợp)</option>
                    <option value="newest">Mới nhất</option>
                    <option value="price-asc">Giá thấp → cao</option>
                    <option value="price-desc">Giá cao → thấp</option>
                </select>
            </div>

            <div class="combo-list-grid">
                <article
                    v-for="combo in combos"
                    :key="combo.slug"
                    class="combo-list-card"
                >
                    <Link :href="route('combos.show', combo.slug)" class="card-explore">
                        <div class="img-wrap">
                            <img :src="combo.image" :alt="combo.title" @error="$event.target.src = `https://placehold.co/900x600/14724f/fbf8f1?text=${encodeURIComponent(combo.title)}`" />
                            <div class="duration-badge">{{ combo.duration }}</div>
                        </div>
                        <div class="info">
                            <span class="district-tag">📍 {{ combo.district }}</span>
                            <h3>{{ combo.title }}</h3>
                            <p class="desc">{{ combo.description }}</p>
                            <ul v-if="combo.highlights?.length" class="highlights">
                                <li v-for="(h, i) in combo.highlights.slice(0, 3)" :key="i">{{ h }}</li>
                            </ul>
                        </div>
                    </Link>
                    <div class="card-foot">
                        <div class="card-foot-meta">
                            <div>
                                <small>Từ</small>
                                <strong>{{ fmt(combo.from_price) }}đ</strong>
                            </div>
                            <div class="hotel-count">
                                <strong>{{ combo.hotel_count }}</strong>
                                <small>khách sạn phù hợp</small>
                            </div>
                        </div>
                        <div class="card-foot-actions">
                            <Link :href="route('combos.show', combo.slug)" class="btn-detail">Chi tiết</Link>
                            <Link :href="route('booking.combo.create', combo.slug)" class="cta-book">Đặt tour ngay →</Link>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.combos-list {
    max-width: 1280px;
    margin: 0 auto;
    padding-bottom: 60px;
}
.sort-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 32px 0;
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
.combo-list-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    padding: 32px;
}
.combo-list-card {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
}
.combo-list-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    border-color: var(--emerald-300);
}
.card-explore {
    display: grid;
    grid-template-columns: 380px 1fr;
    color: inherit;
}
.combo-list-card .img-wrap {
    position: relative;
    overflow: hidden;
    aspect-ratio: 4/3;
}
.combo-list-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}
.combo-list-card:hover img {
    transform: scale(1.04);
}
.duration-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    background: rgba(11, 20, 16, 0.85);
    backdrop-filter: blur(8px);
    color: var(--cream);
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
}
.info {
    padding: 28px 32px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.district-tag {
    display: inline-block;
    font-size: 12px;
    color: var(--emerald-700);
    font-weight: 500;
    margin-bottom: 8px;
}
.info h3 {
    font-family: var(--serif);
    font-size: 26px;
    font-weight: 500;
    line-height: 1.15;
    letter-spacing: -0.01em;
    margin-bottom: 10px;
    color: var(--ink-900);
}
.desc {
    font-size: 14px;
    color: var(--ink-500);
    line-height: 1.6;
    margin-bottom: 14px;
}
.highlights {
    list-style: none;
    padding: 0;
    margin-bottom: 18px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.highlights li {
    font-size: 12px;
    background: var(--paper);
    padding: 5px 11px;
    border-radius: 999px;
    color: var(--ink-700);
}
.card-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 16px 32px 22px;
    border-top: 1px dashed rgba(11, 20, 16, 0.1);
    flex-wrap: wrap;
}
.card-foot-meta {
    display: flex;
    align-items: center;
    gap: 28px;
    flex-wrap: wrap;
}
.card-foot small {
    display: block;
    font-size: 11px;
    color: var(--ink-500);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.card-foot strong {
    font-family: var(--serif);
    font-size: 20px;
    font-weight: 600;
    color: var(--ink-900);
}
.hotel-count strong {
    color: var(--emerald-700);
}
.card-foot-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}
.btn-detail {
    padding: 9px 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-700);
    border: 1px solid rgba(11, 20, 16, 0.12);
    transition: border-color 0.2s, color 0.2s;
}
.btn-detail:hover {
    border-color: var(--emerald-700);
    color: var(--emerald-700);
}
.cta-book {
    background: var(--emerald-700);
    color: var(--cream);
    padding: 9px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;
    transition: background 0.2s, transform 0.2s;
}
.cta-book:hover {
    background: var(--emerald-900);
    transform: translateX(2px);
}

@media (max-width: 800px) {
    .card-explore {
        grid-template-columns: 1fr;
    }
    .combo-list-card .img-wrap {
        aspect-ratio: 16/9;
    }
    .card-foot {
        padding: 14px 20px 18px;
    }
}
</style>
