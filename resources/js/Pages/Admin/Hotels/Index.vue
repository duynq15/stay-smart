<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    hotels: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    districts: { type: Array, default: () => [] },
});

const filters = reactive({ ...props.filters });
let debounce;
watch(filters, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.hotels.index'), val, { preserveState: true, replace: true, preserveScroll: true });
    }, 200);
}, { deep: true });

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

function destroy(h) {
    const msg = h.rooms_count > 0
        ? `Xóa "${h.name}"?\n\nKhách sạn có ${h.rooms_count} phòng. Tất cả phòng sẽ bị xóa theo. Hành động không thể hoàn tác.`
        : `Xóa "${h.name}"? Hành động không thể hoàn tác.`;
    if (!confirm(msg)) return;
    router.delete(route('admin.hotels.destroy', h.id), { preserveScroll: true });
}

function toggleActive(h) {
    router.patch(route('admin.hotels.toggle', h.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Admin · Khách sạn" />
    <AdminLayout
        page-title="Quản lý <em>khách sạn</em>"
        :page-subtitle="`${hotels.total} khách sạn trên hệ thống`"
    >
        <template #actions>
            <Link :href="route('admin.hotels.create')" class="btn btn-emerald">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Thêm khách sạn
            </Link>
        </template>

        <div class="filter-row">
            <div class="search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--ink-500);"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input v-model="filters.q" type="text" placeholder="Tìm theo tên khách sạn..." />
            </div>
            <select v-model="filters.district">
                <option value="">Tất cả quận</option>
                <option v-for="d in districts" :key="d" :value="d">{{ d }}</option>
            </select>
            <select v-model="filters.stars">
                <option value="">Mọi hạng sao</option>
                <option value="5">5 sao</option>
                <option value="4">4 sao</option>
                <option value="3">3 sao</option>
                <option value="2">2 sao</option>
                <option value="1">1 sao</option>
            </select>
            <select v-model="filters.status">
                <option value="">Mọi trạng thái</option>
                <option value="active">Đang hoạt động</option>
                <option value="inactive">Tạm ngưng</option>
            </select>
        </div>

        <div class="panel" style="padding: 0;">
            <div class="table-wrap" style="margin: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Khách sạn</th>
                            <th>Quận</th>
                            <th>Hạng</th>
                            <th>Giá từ</th>
                            <th>Đánh giá</th>
                            <th>Phòng</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="h in hotels.data" :key="h.id">
                            <td>#{{ h.id }}</td>
                            <td>
                                <img v-if="h.primary_image" :src="h.primary_image" :alt="h.name" class="thumb" />
                                <div v-else class="thumb-fallback">🏨</div>
                            </td>
                            <td>
                                <strong>{{ h.name }}</strong>
                                <span v-if="h.has_vr_tour" class="vr-tag">VR</span>
                                <br>
                                <small style="color: var(--ink-500)">{{ h.slug }}</small>
                            </td>
                            <td>{{ h.district }}</td>
                            <td><span style="color: var(--gold); white-space: nowrap;">{{ '★'.repeat(h.stars) }}</span></td>
                            <td style="white-space: nowrap;">{{ fmt(h.base_price) }}đ</td>
                            <td style="white-space: nowrap;">{{ h.rating }} <small style="color: var(--ink-500)">({{ h.reviews_count }})</small></td>
                            <td>{{ h.rooms_count }}</td>
                            <td>
                                <button
                                    class="status-pill toggle-btn"
                                    :class="h.is_active ? 'st-active' : 'st-inactive'"
                                    @click="toggleActive(h)"
                                    :title="h.is_active ? 'Click để tạm ngưng' : 'Click để kích hoạt'"
                                >
                                    {{ h.is_active ? 'Hoạt động' : 'Tạm ngưng' }}
                                </button>
                            </td>
                            <td class="row-actions">
                                <Link :href="route('hotels.show', h.slug)" class="row-btn" target="_blank" title="Xem trên trang user">👁</Link>
                                <Link :href="route('admin.hotels.edit', h.id)" class="row-btn">Sửa</Link>
                                <button class="row-btn danger" @click="destroy(h)">Xóa</button>
                            </td>
                        </tr>
                        <tr v-if="hotels.data.length === 0">
                            <td colspan="10" class="empty-row">Không có khách sạn nào khớp bộ lọc.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination" style="margin: 0; border-top: 1px solid rgba(11,20,16,.06);">
                <span>Hiển thị <strong>{{ hotels.from || 0 }}–{{ hotels.to || 0 }}</strong> / <strong>{{ hotels.total }}</strong></span>
                <div class="page-buttons">
                    <Link
                        v-for="link in hotels.links"
                        :key="link.label"
                        :href="link.url || ''"
                        class="page-btn"
                        :class="{ active: link.active, disabled: !link.url }"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.thumb {
    width: 56px; height: 42px;
    border-radius: 6px;
    object-fit: cover;
}
.thumb-fallback {
    width: 56px; height: 42px;
    border-radius: 6px;
    background: var(--paper);
    display: grid;
    place-items: center;
    font-size: 20px;
}
.vr-tag {
    display: inline-block;
    margin-left: 8px;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: 600;
    background: var(--ink-900);
    color: var(--cream);
    letter-spacing: 0.05em;
    vertical-align: middle;
}
.status-pill {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border: none;
    cursor: pointer;
}
.toggle-btn:hover { transform: scale(1.05); }
.st-active { background: rgba(31, 155, 106, 0.15); color: var(--emerald-900); }
.st-inactive { background: rgba(11, 20, 16, 0.08); color: var(--ink-700); }
.row-actions {
    display: flex;
    gap: 6px;
    white-space: nowrap;
}
.row-btn {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 12px;
    background: var(--paper);
    color: var(--ink-700);
    border: none;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
    display: inline-block;
}
.row-btn:hover { background: var(--ink-100); }
.row-btn.danger { color: var(--rust); }
.row-btn.danger:hover { background: rgba(184, 92, 60, 0.15); }
.empty-row {
    text-align: center;
    color: var(--ink-500);
    padding: 32px !important;
}
.page-btn.disabled {
    opacity: 0.4;
    pointer-events: none;
}
.page-btn.active {
    background: var(--emerald-700);
    color: var(--cream);
}
</style>
