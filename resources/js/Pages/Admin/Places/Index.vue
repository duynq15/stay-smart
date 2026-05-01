<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    places: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    districts: { type: Array, default: () => [] },
});

const filters = reactive({ ...props.filters });
let debounce;
watch(filters, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.places.index'), val, { preserveState: true, replace: true, preserveScroll: true });
    }, 200);
}, { deep: true });

const typeLabels = {
    restaurant: 'Nhà hàng', cafe: 'Cafe', attraction: 'Tham quan',
    shopping: 'Mua sắm', bar: 'Bar', spa: 'Spa',
};

function destroy(p) {
    if (!confirm(`Xóa "${p.name}"?`)) return;
    router.delete(route('admin.places.destroy', p.id), { preserveScroll: true });
}

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}
</script>

<template>
    <Head title="Admin · Địa điểm" />
    <AdminLayout page-title="Quản lý <em>địa điểm</em>" :page-subtitle="`${places.total} địa điểm ăn uống / tham quan`">
        <template #actions>
            <Link :href="route('admin.places.create')" class="btn btn-emerald">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Thêm địa điểm
            </Link>
        </template>

        <div class="filter-row">
            <div class="search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--ink-500);"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input v-model="filters.q" type="text" placeholder="Tìm theo tên..." />
            </div>
            <select v-model="filters.type">
                <option value="">Mọi loại</option>
                <option v-for="(label, key) in typeLabels" :key="key" :value="key">{{ label }}</option>
            </select>
            <select v-model="filters.district">
                <option value="">Mọi quận</option>
                <option v-for="d in districts" :key="d" :value="d">{{ d }}</option>
            </select>
        </div>

        <div class="panel" style="padding: 0;">
            <div class="table-wrap" style="margin: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên</th>
                            <th>Loại</th>
                            <th>Quận</th>
                            <th>Đánh giá</th>
                            <th>Giá TB</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in places.data" :key="p.id">
                            <td>
                                <img v-if="p.image_url" :src="p.image_url" :alt="p.name" class="thumb" />
                                <div v-else class="thumb-fallback">📍</div>
                            </td>
                            <td><strong>{{ p.name }}</strong><br><small style="color: var(--ink-500)">{{ p.address }}</small></td>
                            <td>{{ typeLabels[p.type] || p.type }}</td>
                            <td>{{ p.district }}</td>
                            <td>{{ p.rating }} ★</td>
                            <td>{{ p.avg_price > 0 ? fmt(p.avg_price) + 'đ' : '—' }}</td>
                            <td class="row-actions">
                                <Link :href="route('admin.places.edit', p.id)" class="row-btn">Sửa</Link>
                                <button class="row-btn danger" @click="destroy(p)">Xóa</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.thumb, .thumb-fallback {
    width: 56px; height: 42px;
    border-radius: 6px;
    object-fit: cover;
    background: var(--paper);
    display: grid;
    place-items: center;
    font-size: 20px;
}
.row-actions { display: flex; gap: 6px; }
.row-btn { padding: 5px 10px; border-radius: 6px; font-size: 12px; background: var(--paper); border: none; cursor: pointer; color: var(--ink-700); }
.row-btn:hover { background: var(--ink-100); }
.row-btn.danger { color: var(--rust); }
.row-btn.danger:hover { background: rgba(184, 92, 60, 0.15); }
</style>
