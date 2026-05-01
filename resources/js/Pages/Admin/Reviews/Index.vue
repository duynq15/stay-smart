<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    reviews: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const filters = reactive({ ...props.filters });
let debounce;
watch(filters, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.reviews.index'), val, { preserveState: true, replace: true, preserveScroll: true });
    }, 200);
}, { deep: true });
</script>

<template>
    <Head title="Admin · Đánh giá" />
    <AdminLayout page-title="Quản lý <em>đánh giá</em>" :page-subtitle="`${reviews.total} đánh giá từ khách`">
        <div class="filter-row">
            <select v-model="filters.rating">
                <option value="">Mọi điểm</option>
                <option value="4">≥ 4 sao</option>
                <option value="3">≥ 3 sao</option>
            </select>
        </div>

        <div class="reviews-grid">
            <div v-for="r in reviews.data" :key="r.id" class="review-card">
                <div class="review-head">
                    <img v-if="r.user.avatar" :src="r.user.avatar" :alt="r.user.name" />
                    <div v-else class="avatar-fallback">{{ r.user.name?.charAt(0) }}</div>
                    <div>
                        <strong>{{ r.user.name }}</strong>
                        <small>{{ r.created_at }}</small>
                    </div>
                    <span class="rating">★ {{ r.rating }}</span>
                </div>
                <div class="review-hotel">{{ r.hotel.name }}</div>
                <p>{{ r.comment }}</p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 14px;
}
.review-card {
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 12px;
    padding: 18px;
    box-shadow: var(--shadow-sm);
}
.review-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.review-head img, .avatar-fallback {
    width: 36px; height: 36px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--emerald-700);
    color: var(--cream);
    display: grid; place-items: center;
    font-weight: 600;
}
.review-head strong { display: block; font-size: 14px; }
.review-head small { color: var(--ink-500); font-size: 12px; }
.rating {
    margin-left: auto;
    color: var(--gold);
    font-weight: 600;
    font-size: 13px;
}
.review-hotel {
    font-family: var(--serif);
    font-size: 14px;
    color: var(--emerald-900);
    margin-bottom: 8px;
    font-weight: 500;
}
.review-card p {
    font-size: 13px;
    color: var(--ink-700);
    line-height: 1.55;
}
</style>
