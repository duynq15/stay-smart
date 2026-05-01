<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    hotels: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    districts: { type: Array, default: () => [] },
    totalCount: { type: Number, default: 0 },
});

const filters = reactive({
    sort: props.filters.sort || 'recommended',
    price: props.filters.price || 'all',
    location: props.filters.location || 'all',
    amenities: Array.isArray(props.filters.amenities) ? [...props.filters.amenities] : [],
    q: props.filters.q || '',
});

let debounce;
watch(filters, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('hotels.index'), { ...val }, { preserveState: true, preserveScroll: true, replace: true });
    }, 200);
}, { deep: true });

function toggleAmenity(key) {
    const idx = filters.amenities.indexOf(key);
    if (idx === -1) filters.amenities.push(key);
    else filters.amenities.splice(idx, 1);
}

function formatPrice(p) {
    return new Intl.NumberFormat('vi-VN').format(p);
}

const amenityChips = [
    { key: 'pool', label: '🏊 Bể bơi' },
    { key: 'gym', label: '💪 Gym' },
    { key: 'spa', label: '🧖 Spa' },
    { key: 'view', label: '🌆 View đẹp' },
];
</script>

<template>
    <Head title="Tìm khách sạn Hà Nội" />
    <AppLayout>
        <section class="results-page active">
            <div style="margin-bottom: 20px">
                <Link :href="route('home')" class="btn btn-ghost" style="padding-left: 0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6" /></svg>
                    Quay lại
                </Link>
            </div>

            <div class="section-head">
                <div>
                    <h2><em>{{ totalCount }}</em> khách sạn phù hợp</h2>
                </div>
                <p>Đã lọc theo mong muốn của bạn từ trợ lý AI.</p>
            </div>

            <div class="filter-bar">
                <div class="filter-group">
                    <label>Sắp xếp</label>
                    <select v-model="filters.sort">
                        <option value="recommended">Đề xuất</option>
                        <option value="name">Tên A-Z</option>
                        <option value="popular">Phổ biến nhất</option>
                        <option value="newest">Mới nhất</option>
                        <option value="price-asc">Giá thấp → cao</option>
                        <option value="price-desc">Giá cao → thấp</option>
                        <option value="rating">Đánh giá tốt nhất</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Khoảng giá</label>
                    <select v-model="filters.price">
                        <option value="all">Tất cả</option>
                        <option value="0-1000000">Dưới 1tr</option>
                        <option value="1000000-2000000">1tr – 2tr</option>
                        <option value="2000000-5000000">2tr – 5tr</option>
                        <option value="5000000-99999999">Trên 5tr</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Vị trí</label>
                    <select v-model="filters.location">
                        <option value="all">Toàn Hà Nội</option>
                        <option v-for="d in districts" :key="d" :value="d">{{ d }}</option>
                    </select>
                </div>
                <div class="filter-chips" style="margin-left: auto">
                    <button
                        v-for="chip in amenityChips"
                        :key="chip.key"
                        type="button"
                        class="chip"
                        :class="{ active: filters.amenities.includes(chip.key) }"
                        @click="toggleAmenity(chip.key)"
                    >
                        {{ chip.label }}
                    </button>
                </div>
            </div>

            <div v-if="hotels.data.length === 0" class="empty-state">
                <h3>Không tìm thấy khách sạn phù hợp</h3>
                <p>Hãy thử nới lỏng bộ lọc hoặc trò chuyện với Smarty để được gợi ý khác.</p>
            </div>

            <div v-else class="hotel-grid">
                <Link
                    v-for="hotel in hotels.data"
                    :key="hotel.id"
                    :href="route('hotels.show', hotel.slug)"
                    class="hotel-card"
                >
                    <div class="img-wrap">
                        <img :src="hotel.image" :alt="hotel.name" />
                        <div v-if="hotel.has_vr_tour" class="vr-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0" /><path d="M3 12h18" /></svg>
                            VR Tour
                        </div>
                        <div class="price-badge">{{ formatPrice(hotel.base_price) }}đ</div>
                    </div>
                    <div class="info">
                        <h3>{{ hotel.name }}</h3>
                        <div class="loc">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="3" /></svg>
                            {{ hotel.district }}
                        </div>
                        <div class="meta">
                            <div class="rating">
                                <span style="color: var(--gold)">★</span>
                                {{ hotel.rating }}
                                <small style="color: var(--ink-500)">({{ hotel.reviews_count }})</small>
                            </div>
                            <div class="price">
                                {{ formatPrice(hotel.base_price) }}<small>đ/đêm</small>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-if="hotels.links && hotels.last_page > 1" class="pagination">
                <Link
                    v-for="link in hotels.links"
                    :key="link.label"
                    :href="link.url || ''"
                    v-html="link.label"
                    class="page-link"
                    :class="{ active: link.active, disabled: !link.url }"
                    preserve-scroll
                />
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--cream);
    border-radius: 16px;
    border: 1px dashed rgba(11, 20, 16, 0.12);
}
.empty-state h3 {
    font-family: var(--serif);
    font-size: 22px;
    font-weight: 500;
    margin-bottom: 8px;
}
.empty-state p {
    color: var(--ink-500);
    font-size: 14px;
}
.pagination {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-top: 32px;
    flex-wrap: wrap;
}
.page-link {
    padding: 8px 14px;
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.08);
    border-radius: 8px;
    font-size: 13px;
    color: var(--ink-700);
}
.page-link.active {
    background: var(--emerald-700);
    color: var(--cream);
    border-color: var(--emerald-700);
}
.page-link.disabled {
    pointer-events: none;
    opacity: 0.4;
}
</style>
