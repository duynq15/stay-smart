<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    rooms: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    hotels: { type: Array, default: () => [] },
});

const filters = reactive({ ...props.filters });
let debounce;
watch(filters, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get(route('admin.rooms.index'), val, { preserveState: true, replace: true, preserveScroll: true });
    }, 200);
}, { deep: true });

const editing = ref(null);
const editForm = useForm({ name: '', price_per_night: 0, capacity: 2, available_units: 5, is_active: true });

function startEdit(r) {
    editing.value = r.id;
    editForm.name = r.name;
    editForm.price_per_night = r.price_per_night;
    editForm.capacity = r.capacity;
    editForm.available_units = r.available_units;
    editForm.is_active = r.is_active;
}

function saveEdit(r) {
    editForm.put(route('admin.rooms.update', r.id), {
        preserveScroll: true,
        onSuccess: () => { editing.value = null; },
    });
}

function destroy(r) {
    if (!confirm(`Xóa phòng "${r.name}"?`)) return;
    router.delete(route('admin.rooms.destroy', r.id), { preserveScroll: true });
}

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}
</script>

<template>
    <Head title="Admin · Phòng" />
    <AdminLayout page-title="Quản lý <em>phòng</em>" :page-subtitle="`${rooms.total} loại phòng`">
        <div class="filter-row">
            <div class="search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: var(--ink-500);"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input v-model="filters.q" type="text" placeholder="Tìm tên phòng / khách sạn..." />
            </div>
            <select v-model="filters.hotel_id">
                <option value="">Tất cả khách sạn</option>
                <option v-for="h in hotels" :key="h.id" :value="h.id">{{ h.name }}</option>
            </select>
            <select v-model="filters.sort">
                <option value="price-asc">Giá thấp → cao</option>
                <option value="price-desc">Giá cao → thấp</option>
                <option value="capacity">Sức chứa</option>
            </select>
        </div>

        <div class="panel" style="padding: 0;">
            <div class="table-wrap" style="margin: 0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Khách sạn / Phòng</th>
                            <th>Mô tả</th>
                            <th>Giá/đêm</th>
                            <th>Sức chứa</th>
                            <th>Còn lại</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in rooms.data" :key="r.id">
                            <td>
                                <small style="color: var(--ink-500)">{{ r.hotel.name }} · {{ r.hotel.district }}</small><br>
                                <strong v-if="editing !== r.id">{{ r.name }}</strong>
                                <input v-else v-model="editForm.name" class="inline-edit" />
                            </td>
                            <td><small style="color: var(--ink-500)">{{ r.description }}</small></td>
                            <td>
                                <span v-if="editing !== r.id">{{ fmt(r.price_per_night) }}đ</span>
                                <input v-else type="number" v-model.number="editForm.price_per_night" class="inline-edit" style="width: 110px" />
                            </td>
                            <td>
                                <span v-if="editing !== r.id">{{ r.capacity }}</span>
                                <input v-else type="number" v-model.number="editForm.capacity" class="inline-edit" style="width: 60px" />
                            </td>
                            <td>
                                <span v-if="editing !== r.id">{{ r.available_units }}</span>
                                <input v-else type="number" v-model.number="editForm.available_units" class="inline-edit" style="width: 60px" />
                            </td>
                            <td>
                                <span v-if="editing !== r.id" class="status-pill" :class="r.is_active ? 'st-active' : 'st-inactive'">
                                    {{ r.is_active ? 'Hoạt động' : 'Tạm ngưng' }}
                                </span>
                                <label v-else class="toggle"><input type="checkbox" v-model="editForm.is_active" />Hoạt động</label>
                            </td>
                            <td class="row-actions">
                                <template v-if="editing === r.id">
                                    <button class="row-btn" @click="saveEdit(r)">Lưu</button>
                                    <button class="row-btn" @click="editing = null">Hủy</button>
                                </template>
                                <template v-else>
                                    <button class="row-btn" @click="startEdit(r)">Sửa</button>
                                    <button class="row-btn danger" @click="destroy(r)">Xóa</button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.inline-edit {
    border: 1px solid var(--emerald-500);
    background: var(--cream);
    border-radius: 6px;
    padding: 5px 8px;
    font-size: 13px;
    outline: none;
    width: 100%;
}
.toggle {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
}
.status-pill {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 500;
}
.st-active { background: rgba(31, 155, 106, 0.15); color: var(--emerald-900); }
.st-inactive { background: rgba(11, 20, 16, 0.08); color: var(--ink-700); }
.row-actions { display: flex; gap: 6px; }
.row-btn { padding: 5px 10px; border-radius: 6px; font-size: 12px; background: var(--paper); border: none; cursor: pointer; }
.row-btn:hover { background: var(--ink-100); }
.row-btn.danger { color: var(--rust); }
.row-btn.danger:hover { background: rgba(184, 92, 60, 0.15); }
</style>
