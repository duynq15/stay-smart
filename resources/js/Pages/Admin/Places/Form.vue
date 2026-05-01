<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    place: { type: Object, default: null },
});

const isEdit = !!props.place;

const form = useForm({
    name: props.place?.name || '',
    type: props.place?.type || 'restaurant',
    district: props.place?.district || 'Hoàn Kiếm',
    address: props.place?.address || '',
    description: props.place?.description || '',
    rating: props.place?.rating || 4.5,
    avg_price: props.place?.avg_price || 0,
    image_url: props.place?.image_url || '',
    lat: props.place?.lat || null,
    lng: props.place?.lng || null,
    is_active: props.place?.is_active !== false,
});

const clientErrors = ref({});
const touched = ref({});

function markTouched(field) {
    touched.value[field] = true;
}

function errorOf(field) {
    return clientErrors.value[field] || form.errors[field] || '';
}

function validate() {
    const errs = {};
    if (!(form.name || '').trim()) errs.name = 'Vui lòng nhập tên địa điểm';
    if (!form.type) errs.type = 'Vui lòng chọn loại';
    if (!form.district) errs.district = 'Vui lòng chọn quận';
    if (!(form.address || '').trim()) errs.address = 'Vui lòng nhập địa chỉ';

    if (form.rating !== null && form.rating !== '' && (form.rating < 0 || form.rating > 5)) {
        errs.rating = 'Đánh giá từ 0 đến 5';
    }
    if (form.avg_price !== null && form.avg_price !== '' && form.avg_price < 0) {
        errs.avg_price = 'Giá không được âm';
    }
    if (form.image_url && !/^https?:\/\/.+/.test(form.image_url)) {
        errs.image_url = 'URL ảnh phải bắt đầu http:// hoặc https://';
    }

    clientErrors.value = errs;
    Object.keys(errs).forEach((k) => (touched.value[k] = true));
    return Object.keys(errs).length === 0;
}

function submit() {
    if (!validate()) {
        setTimeout(() => {
            const firstErr = document.querySelector('.form-field.has-error');
            firstErr?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstErr?.querySelector('input, select, textarea')?.focus();
        }, 50);
        return;
    }
    const onError = () => Object.keys(form.errors).forEach((k) => (touched.value[k] = true));
    if (isEdit) form.put(route('admin.places.update', props.place.id), { onError });
    else form.post(route('admin.places.store'), { onError });
}

const districts = ['Hoàn Kiếm', 'Tây Hồ', 'Ba Đình', 'Cầu Giấy', 'Hai Bà Trưng', 'Đống Đa', 'Long Biên', 'Hà Đông', 'Hoàng Mai'];
const types = [
    { key: 'restaurant', label: 'Nhà hàng' },
    { key: 'cafe', label: 'Cafe' },
    { key: 'attraction', label: 'Tham quan' },
    { key: 'shopping', label: 'Mua sắm' },
    { key: 'bar', label: 'Bar' },
    { key: 'spa', label: 'Spa' },
];
</script>

<template>
    <Head :title="isEdit ? `Sửa: ${place.name}` : 'Thêm địa điểm'" />
    <AdminLayout
        :page-title="isEdit ? `Sửa <em>địa điểm</em>` : `Thêm <em>địa điểm</em>`"
        :page-subtitle="isEdit ? place.name : ''"
    >
        <template #actions>
            <Link :href="route('admin.places.index')" class="btn btn-ghost btn-sm">← Quay lại</Link>
        </template>

        <form @submit.prevent="submit" class="panel" style="max-width: 760px;">
            <div v-if="Object.keys(clientErrors).length > 0" class="error-banner">
                <strong>⚠ Còn {{ Object.keys(clientErrors).length }} trường chưa hợp lệ:</strong>
                <ul>
                    <li v-for="(msg, field) in clientErrors" :key="field">{{ msg }}</li>
                </ul>
            </div>

            <div class="form-grid">
                <div class="form-field full" :class="{ 'has-error': touched.name && errorOf('name') }">
                    <label>Tên địa điểm <span class="req">*</span></label>
                    <input type="text" v-model="form.name" @blur="markTouched('name')" placeholder="Bún Chả Hương Liên..." />
                    <small v-if="touched.name && errorOf('name')" class="err">⚠ {{ errorOf('name') }}</small>
                </div>
                <div class="form-field" :class="{ 'has-error': touched.type && errorOf('type') }">
                    <label>Loại <span class="req">*</span></label>
                    <select v-model="form.type" @blur="markTouched('type')">
                        <option value="">— Chọn loại —</option>
                        <option v-for="t in types" :key="t.key" :value="t.key">{{ t.label }}</option>
                    </select>
                    <small v-if="touched.type && errorOf('type')" class="err">⚠ {{ errorOf('type') }}</small>
                </div>
                <div class="form-field" :class="{ 'has-error': touched.district && errorOf('district') }">
                    <label>Quận <span class="req">*</span></label>
                    <select v-model="form.district" @blur="markTouched('district')">
                        <option value="">— Chọn quận —</option>
                        <option v-for="d in districts" :key="d" :value="d">{{ d }}</option>
                    </select>
                    <small v-if="touched.district && errorOf('district')" class="err">⚠ {{ errorOf('district') }}</small>
                </div>
                <div class="form-field full" :class="{ 'has-error': touched.address && errorOf('address') }">
                    <label>Địa chỉ <span class="req">*</span></label>
                    <input type="text" v-model="form.address" @blur="markTouched('address')" placeholder="24 Lê Văn Hưu..." />
                    <small v-if="touched.address && errorOf('address')" class="err">⚠ {{ errorOf('address') }}</small>
                </div>
                <div class="form-field" :class="{ 'has-error': touched.rating && errorOf('rating') }">
                    <label>Đánh giá</label>
                    <input type="number" v-model.number="form.rating" @blur="markTouched('rating')" min="0" max="5" step="0.1" />
                    <small v-if="touched.rating && errorOf('rating')" class="err">⚠ {{ errorOf('rating') }}</small>
                </div>
                <div class="form-field" :class="{ 'has-error': touched.avg_price && errorOf('avg_price') }">
                    <label>Giá trung bình (VND)</label>
                    <input type="number" v-model.number="form.avg_price" @blur="markTouched('avg_price')" min="0" />
                    <small v-if="touched.avg_price && errorOf('avg_price')" class="err">⚠ {{ errorOf('avg_price') }}</small>
                </div>
                <div class="form-field full" :class="{ 'has-error': touched.image_url && errorOf('image_url') }">
                    <label>URL ảnh</label>
                    <input type="url" v-model="form.image_url" @blur="markTouched('image_url')" placeholder="https://..." />
                    <small v-if="touched.image_url && errorOf('image_url')" class="err">⚠ {{ errorOf('image_url') }}</small>
                </div>
                <div class="form-field full">
                    <label>Mô tả</label>
                    <textarea v-model="form.description" rows="3"></textarea>
                </div>
                <div class="form-field">
                    <label>Lat</label>
                    <input type="number" v-model.number="form.lat" step="0.000001" />
                </div>
                <div class="form-field">
                    <label>Lng</label>
                    <input type="number" v-model.number="form.lng" step="0.000001" />
                </div>
                <div class="form-field full">
                    <label class="toggle-row">
                        <input type="checkbox" v-model="form.is_active" />
                        <span>Hoạt động</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-emerald" :disabled="form.processing">
                    {{ form.processing ? 'Đang lưu...' : (isEdit ? 'Cập nhật' : 'Tạo địa điểm') }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.form-field { display: flex; flex-direction: column; gap: 6px; }
.form-field.full { grid-column: 1 / -1; }
.form-field label { font-size: 12px; font-weight: 500; color: var(--ink-700); }
.form-field input, .form-field select, .form-field textarea {
    border: 1px solid rgba(11, 20, 16, 0.12);
    background: var(--paper);
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 14px;
    outline: none;
    font-family: inherit;
}
.form-field input:focus, .form-field select:focus, .form-field textarea:focus {
    border-color: var(--emerald-500);
}
.toggle-row { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; }
.form-actions { margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(11, 20, 16, 0.06); display: flex; justify-content: flex-end; }
.form-field .err {
    color: var(--rust);
    font-size: 12px;
    font-weight: 500;
}
.form-field .req { color: var(--rust); font-weight: 600; }
.form-field.has-error label { color: var(--rust); }
.form-field.has-error input,
.form-field.has-error select,
.form-field.has-error textarea {
    border-color: var(--rust);
    background: rgba(184, 92, 60, 0.04);
}
.form-field.has-error input:focus,
.form-field.has-error select:focus,
.form-field.has-error textarea:focus {
    box-shadow: 0 0 0 3px rgba(184, 92, 60, 0.15);
}
.error-banner {
    background: rgba(184, 92, 60, 0.08);
    border-left: 3px solid var(--rust);
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 18px;
    font-size: 13px;
    color: var(--rust);
}
.error-banner strong { display: block; margin-bottom: 6px; font-size: 14px; }
.error-banner ul { margin: 0; padding-left: 18px; line-height: 1.6; }
</style>
