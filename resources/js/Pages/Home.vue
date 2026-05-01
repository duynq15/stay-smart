<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    featuredHotels: { type: Array, default: () => [] },
    combos: { type: Array, default: () => [] },
});

const search = ref({
    destination: 'Hà Nội',
    checkin: '04/05/2026',
    guests: '2 người',
});

function submitSearch() {
    router.get(route('hotels.index'), {
        q: search.value.destination,
    });
}

function formatPrice(p) {
    return new Intl.NumberFormat('vi-VN').format(p);
}
</script>

<template>
    <Head title="Đặt phòng khách sạn thông minh" />
    <AppLayout>
        <div id="home-page">
            <section class="hero">
                <div class="hero-bg"></div>
                <div class="hero-grid">
                    <div class="hero-text">
                        <span class="hero-eyebrow">Trợ lý AI · Hà Nội</span>
                        <h1>Tìm phòng <em>khách sạn</em><br />bằng cuộc trò chuyện.</h1>
                        <p class="lede">Mô tả mong muốn của bạn — vị trí, ngân sách, view yêu thích — STAY-SMART sẽ tìm ra khách sạn phù hợp tại 36 phố phường Hà Nội trong vài giây.</p>

                        <form class="search-card" @submit.prevent="submitSearch">
                            <div class="search-field">
                                <label>Điểm đến</label>
                                <input type="text" v-model="search.destination" placeholder="Hoàn Kiếm, Ba Đình..." />
                            </div>
                            <div class="search-field">
                                <label>Nhận phòng</label>
                                <input type="text" v-model="search.checkin" placeholder="04/05/2026" />
                            </div>
                            <div class="search-field">
                                <label>Khách</label>
                                <input type="text" v-model="search.guests" placeholder="2 người" />
                            </div>
                            <button type="submit" class="btn btn-emerald">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
                                Tìm
                            </button>
                        </form>

                        <div class="hero-stats">
                            <div class="hero-stat">
                                <strong>1,240+</strong>
                                <span>Khách sạn Hà Nội</span>
                            </div>
                            <div class="hero-stat">
                                <strong>4.9★</strong>
                                <span>Đánh giá AI tư vấn</span>
                            </div>
                            <div class="hero-stat">
                                <strong>30s</strong>
                                <span>Tìm thấy phù hợp</span>
                            </div>
                        </div>
                    </div>

                    <div class="hero-visual">
                        <div class="hero-img hero-img-1">
                            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&q=80" alt="Hotel room" />
                        </div>
                        <div class="hero-img hero-img-2">
                            <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=600&q=80" alt="Hanoi hotel" />
                        </div>
                        <div class="hero-badge">
                            <div class="ai-dot">S</div>
                            <div>
                                <strong>Đang tư vấn</strong><br />
                                <span style="color: var(--ink-500)">"View Hồ Tây, 2 người..."</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="hotels">
                <div class="section-head">
                    <div>
                        <h2>Khách sạn <em>nổi bật</em> tại Hà Nội</h2>
                    </div>
                    <p>Đã được hơn 12,000 du khách lựa chọn trong tháng này — kèm tour ảo VR.</p>
                </div>
                <div class="hotel-grid">
                    <Link
                        v-for="hotel in featuredHotels"
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
            </section>

            <section id="combos">
                <div class="section-head">
                    <div>
                        <h2>Combo <em>du lịch</em> trọn gói</h2>
                    </div>
                    <p>Khách sạn + ăn uống + tham quan, gói gọn trong một ngân sách.</p>
                </div>
                <div class="combo-grid">
                    <Link
                        v-for="(combo, i) in combos"
                        :key="combo.slug"
                        :href="route('combos.show', combo.slug)"
                        class="combo-card"
                        :class="{ large: i === 0 }"
                    >
                        <img :src="combo.image" :alt="combo.title" @error="$event.target.src = `https://placehold.co/900x600/14724f/fbf8f1?text=${encodeURIComponent(combo.title)}`" />
                        <div class="label">
                            <small>{{ combo.tagline }}</small>
                            <h3>{{ combo.title }}</h3>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
