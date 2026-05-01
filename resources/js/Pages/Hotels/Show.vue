<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    hotel: { type: Object, required: true },
});

const activeImage = ref(props.hotel.images[0] || null);
const showVR = ref(false);

const amenityLabels = {
    wifi: { icon: '📶', label: 'Wi-Fi miễn phí' },
    breakfast: { icon: '🍳', label: 'Bữa sáng' },
    parking: { icon: '🅿️', label: 'Bãi đỗ xe' },
    elevator: { icon: '🛗', label: 'Thang máy' },
    gym: { icon: '💪', label: 'Phòng gym' },
    restaurant: { icon: '🍽️', label: 'Nhà hàng' },
    bar: { icon: '🍸', label: 'Quầy bar' },
    laundry: { icon: '🧺', label: 'Giặt ủi' },
    pool: { icon: '🏊', label: 'Bể bơi' },
    spa: { icon: '🧖', label: 'Spa' },
    concierge: { icon: '🎩', label: 'Lễ tân 24/7' },
    room_service: { icon: '🛎️', label: 'Phục vụ phòng' },
    view: { icon: '🌆', label: 'View đẹp' },
};

const amenities = computed(() =>
    (props.hotel.amenities || []).map((k) => amenityLabels[k] || { icon: '✨', label: k })
);

function formatPrice(p) {
    return new Intl.NumberFormat('vi-VN').format(p);
}

function bookRoom(roomId) {
    router.get(route('booking.create'), {
        hotel: props.hotel.slug,
        room: roomId,
    });
}
</script>

<template>
    <Head :title="hotel.name" />
    <AppLayout>
        <section class="results-page active hotel-detail">
            <div style="margin-bottom: 20px">
                <Link :href="route('hotels.index')" class="btn btn-ghost" style="padding-left: 0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6" /></svg>
                    Quay lại danh sách
                </Link>
            </div>

            <div class="detail-hero">
                <div class="detail-main-img">
                    <img v-if="activeImage" :src="activeImage.url" :alt="activeImage.caption || hotel.name" />
                    <button v-if="hotel.has_vr_tour" class="vr-toggle" @click="showVR = !showVR">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0" /><path d="M3 12h18" /><path d="M12 3a15 15 0 0 1 0 18" /><path d="M12 3a15 15 0 0 0 0 18" /></svg>
                        {{ showVR ? 'Đóng VR' : 'Xem VR Tour' }}
                    </button>
                </div>
                <div v-if="hotel.images.length > 1" class="thumb-row">
                    <button
                        v-for="img in hotel.images"
                        :key="img.id"
                        class="thumb"
                        :class="{ active: activeImage?.id === img.id }"
                        @click="activeImage = img; showVR = false"
                    >
                        <img :src="img.url" :alt="img.caption || ''" />
                    </button>
                </div>
            </div>

            <div v-if="showVR" class="vr-frame">
                <div class="vr-placeholder">
                    <div class="vr-icon">🥽</div>
                    <h3>VR Tour 360°</h3>
                    <p>Demo placeholder — tích hợp Pannellum/Marzipano hoặc Google Street View Embed API ở đây.</p>
                    <p class="vr-coords" v-if="hotel.lat">📍 {{ hotel.lat }}, {{ hotel.lng }}</p>
                </div>
            </div>

            <div class="detail-body">
                <div class="detail-content">
                    <div class="modal-title-row">
                        <div>
                            <h2 class="detail-title">{{ hotel.name }}</h2>
                            <div class="loc-row">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="3" /></svg>
                                {{ hotel.address }}
                            </div>
                        </div>
                        <div class="detail-rating">
                            <strong>{{ hotel.rating }} ★</strong>
                            <small>{{ hotel.reviews_count }} đánh giá</small>
                        </div>
                    </div>

                    <div class="modal-amenities">
                        <div v-for="(a, i) in amenities" :key="i" class="amenity">
                            <span style="font-size: 18px">{{ a.icon }}</span>
                            {{ a.label }}
                        </div>
                    </div>

                    <p class="hotel-desc" v-if="hotel.description">{{ hotel.description }}</p>

                    <h3 class="rooms-heading">Chọn phòng</h3>
                    <div class="room-list">
                        <div v-for="room in hotel.rooms" :key="room.id" class="room-card-detail">
                            <img v-if="room.image" :src="room.image" :alt="room.name" class="room-img" />
                            <div class="room-info">
                                <h4>{{ room.name }}</h4>
                                <div class="room-meta">{{ room.description }}</div>
                                <div class="room-meta">👥 Tối đa {{ room.capacity }} khách · Còn {{ room.available_units }} phòng</div>
                            </div>
                            <div class="room-action">
                                <span class="num">{{ formatPrice(room.price_per_night) }}đ</span>
                                <small>/ đêm</small>
                                <button class="book-btn" @click="bookRoom(room.id)">Đặt phòng ngay</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.detail-hero {
    margin-bottom: 24px;
}
.detail-main-img {
    position: relative;
    aspect-ratio: 16/9;
    max-height: 480px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    background: var(--paper);
}
.detail-main-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.thumb-row {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    overflow-x: auto;
    padding: 4px;
}
.thumb {
    flex: 0 0 100px;
    height: 70px;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid transparent;
    cursor: pointer;
    transition: border-color 0.2s;
    padding: 0;
}
.thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.thumb.active {
    border-color: var(--emerald-500);
}
.vr-frame {
    margin-bottom: 24px;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(135deg, #0a3d2e, #14724f);
    aspect-ratio: 16/9;
    max-height: 380px;
}
.vr-placeholder {
    height: 100%;
    display: grid;
    place-items: center;
    text-align: center;
    color: var(--cream);
    padding: 32px;
}
.vr-icon {
    font-size: 48px;
    margin-bottom: 12px;
}
.vr-placeholder h3 {
    font-family: var(--serif);
    font-size: 26px;
    margin-bottom: 8px;
}
.vr-placeholder p {
    font-size: 14px;
    opacity: 0.85;
}
.vr-coords {
    margin-top: 8px;
    font-size: 13px;
    opacity: 0.7;
}
.detail-content {
    background: var(--cream);
    border-radius: 16px;
    padding: 32px;
    border: 1px solid rgba(11, 20, 16, 0.06);
    box-shadow: var(--shadow-sm);
}
.detail-title {
    font-family: var(--serif);
    font-size: 32px;
    font-weight: 500;
    letter-spacing: -0.02em;
    line-height: 1.1;
}
.detail-rating {
    text-align: right;
    flex-shrink: 0;
}
.detail-rating strong {
    font-family: var(--serif);
    font-size: 20px;
    color: var(--emerald-700);
    display: block;
}
.detail-rating small {
    font-size: 12px;
    color: var(--ink-500);
}
.hotel-desc {
    color: var(--ink-700);
    line-height: 1.6;
    margin-bottom: 28px;
}
.rooms-heading {
    font-family: var(--serif);
    font-size: 22px;
    font-weight: 500;
    margin-bottom: 14px;
}
.room-card-detail {
    border: 1px solid rgba(11, 20, 16, 0.08);
    border-radius: 14px;
    padding: 14px;
    display: grid;
    grid-template-columns: 130px 1fr auto;
    gap: 16px;
    align-items: center;
    transition: border-color 0.2s;
    margin-bottom: 12px;
}
.room-card-detail:hover {
    border-color: var(--emerald-500);
}
.room-img {
    width: 130px;
    height: 100px;
    border-radius: 10px;
    object-fit: cover;
}
.room-info h4 {
    font-family: var(--serif);
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 4px;
}
.room-meta {
    font-size: 13px;
    color: var(--ink-500);
}
.room-action {
    text-align: right;
}
.room-action .num {
    font-family: var(--serif);
    font-size: 20px;
    font-weight: 600;
    color: var(--ink-900);
    display: block;
}
.room-action small {
    font-size: 11px;
    color: var(--ink-500);
}
.book-btn {
    margin-top: 8px;
    padding: 9px 18px;
    background: var(--emerald-700);
    color: var(--cream);
    border-radius: 999px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}
.book-btn:hover {
    background: var(--emerald-900);
}
@media (max-width: 700px) {
    .room-card-detail {
        grid-template-columns: 1fr;
    }
    .room-img {
        width: 100%;
        height: 160px;
    }
    .room-action {
        text-align: left;
    }
}
</style>
