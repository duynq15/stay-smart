<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    hotel: { type: Object, default: null },
    amenityOptions: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.hotel);

const form = useForm({
    name: props.hotel?.name || '',
    district: props.hotel?.district || 'Hoàn Kiếm',
    address: props.hotel?.address || '',
    lat: props.hotel?.lat || null,
    lng: props.hotel?.lng || null,
    stars: props.hotel?.stars || 4,
    base_price: props.hotel?.base_price || 1000000,
    description: props.hotel?.description || '',
    amenities: Array.isArray(props.hotel?.amenities) ? [...props.hotel.amenities] : [],
    phone: props.hotel?.phone || '',
    email: props.hotel?.email || '',
    has_vr_tour: props.hotel?.has_vr_tour || false,
    is_active: props.hotel?.is_active !== false,
    images: [], // for create mode: array of pending {url, caption, is_primary}
});

// Used in create mode (form.images) and edit mode (POST individually)
const draftImage = ref({ url: '', caption: '', is_primary: false });
const imageForm = useForm({
    url: '',
    caption: '',
    is_primary: false,
});

const draftImageError = ref('');

function addDraftImage() {
    draftImageError.value = '';
    const url = (draftImage.value.url || '').trim();

    if (!url) {
        draftImageError.value = 'Vui lòng nhập URL ảnh';
        return;
    }
    if (!/^https?:\/\/.+\..+/i.test(url)) {
        draftImageError.value = 'URL ảnh phải bắt đầu bằng http:// hoặc https:// (vd: https://images.unsplash.com/...)';
        return;
    }

    if (draftImage.value.is_primary) {
        form.images.forEach((img) => (img.is_primary = false));
    }
    form.images.push({ ...draftImage.value, url });
    draftImage.value = { url: '', caption: '', is_primary: false };
}

function removeDraftImage(idx) {
    form.images.splice(idx, 1);
}

function setDraftPrimary(idx) {
    form.images.forEach((img, i) => (img.is_primary = i === idx));
}

function toggleAmenity(key) {
    const i = form.amenities.indexOf(key);
    if (i === -1) form.amenities.push(key);
    else form.amenities.splice(i, 1);
}

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
    const name = (form.name || '').trim();
    const address = (form.address || '').trim();

    if (!name) errs.name = 'Vui lòng nhập tên khách sạn';
    else if (name.length > 200) errs.name = 'Tên không quá 200 ký tự';

    if (!form.district) errs.district = 'Vui lòng chọn quận';

    if (!address) errs.address = 'Vui lòng nhập địa chỉ';
    else if (address.length > 255) errs.address = 'Địa chỉ không quá 255 ký tự';

    if (!form.stars || form.stars < 1 || form.stars > 5) {
        errs.stars = 'Vui lòng chọn hạng sao (1–5)';
    }

    if (form.base_price === null || form.base_price === '' || isNaN(form.base_price)) {
        errs.base_price = 'Vui lòng nhập giá';
    } else if (Number(form.base_price) < 0) {
        errs.base_price = 'Giá không được âm';
    }

    // Optional fields validation when filled
    if (form.lat !== null && form.lat !== '' && (form.lat < -90 || form.lat > 90)) {
        errs.lat = 'Vĩ độ phải trong khoảng -90 đến 90';
    }
    if (form.lng !== null && form.lng !== '' && (form.lng < -180 || form.lng > 180)) {
        errs.lng = 'Kinh độ phải trong khoảng -180 đến 180';
    }
    if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errs.email = 'Email không hợp lệ';
    }
    if (form.phone && !/^[0-9+\-\s().]{6,20}$/.test(form.phone)) {
        errs.phone = 'Số điện thoại không hợp lệ';
    }

    clientErrors.value = errs;

    // Mark all fields with errors as touched so they show messages
    Object.keys(errs).forEach((k) => (touched.value[k] = true));

    return Object.keys(errs).length === 0;
}

const serverErrorList = ref([]);

function showServerErrors() {
    Object.keys(form.errors).forEach((k) => (touched.value[k] = true));
    // Build user-friendly list of server errors (incl. nested like images.0.url)
    serverErrorList.value = Object.entries(form.errors).map(([key, msg]) => {
        const m = key.match(/^images\.(\d+)\.(\w+)$/);
        if (m) return `Ảnh #${parseInt(m[1]) + 1} (${m[2]}): ${msg}`;
        return `${key}: ${msg}`;
    });
    setTimeout(() => {
        document.querySelector('.error-banner')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 50);
}

function submit() {
    serverErrorList.value = [];
    if (!validate()) {
        setTimeout(() => {
            const firstErr = document.querySelector('.form-field.has-error');
            firstErr?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstErr?.querySelector('input, select, textarea')?.focus();
        }, 50);
        return;
    }

    if (isEdit.value) {
        form.put(route('admin.hotels.update', props.hotel.id), {
            preserveScroll: true,
            onError: showServerErrors,
        });
    } else {
        form.post(route('admin.hotels.store'), {
            onError: showServerErrors,
        });
    }
}

function addImage() {
    if (!imageForm.url || !imageForm.url.trim()) {
        imageForm.setError('url', 'Vui lòng nhập URL ảnh');
        return;
    }
    imageForm.clearErrors();
    imageForm.post(route('admin.hotels.images.store', props.hotel.id), {
        preserveScroll: true,
        onSuccess: () => imageForm.reset(),
    });
}

function deleteImage(imageId) {
    if (!confirm('Xóa ảnh này?')) return;
    router.delete(route('admin.hotels.images.destroy', [props.hotel.id, imageId]), { preserveScroll: true });
}

function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

const districts = ['Hoàn Kiếm', 'Tây Hồ', 'Ba Đình', 'Cầu Giấy', 'Hai Bà Trưng', 'Đống Đa', 'Long Biên', 'Hà Đông', 'Hoàng Mai'];
</script>

<template>
    <Head :title="isEdit ? `Sửa: ${hotel.name}` : 'Thêm khách sạn'" />
    <AdminLayout
        :page-title="isEdit ? `Sửa <em>khách sạn</em>` : `Thêm <em>khách sạn</em> mới`"
        :page-subtitle="isEdit ? hotel.name : 'Điền thông tin chi tiết'"
    >
        <template #actions>
            <Link v-if="isEdit" :href="route('hotels.show', hotel.slug)" class="btn btn-ghost btn-sm" target="_blank">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                Xem trang user
            </Link>
            <Link :href="route('admin.hotels.index')" class="btn btn-ghost btn-sm">← Quay lại</Link>
        </template>

        <div v-if="isEdit" class="hotel-stats-bar">
            <div class="stat">
                <label>Mã KS</label>
                <strong>#{{ hotel.id }}</strong>
            </div>
            <div class="stat">
                <label>Slug</label>
                <code>{{ hotel.slug }}</code>
            </div>
            <div class="stat">
                <label>Số phòng</label>
                <strong>{{ hotel.rooms_count || 0 }}</strong>
                <Link :href="route('admin.rooms.index', { hotel_id: hotel.id })" class="stat-link">Quản lý →</Link>
            </div>
            <div class="stat">
                <label>Đơn đã đặt</label>
                <strong>{{ hotel.bookings_count || 0 }}</strong>
            </div>
            <div class="stat">
                <label>Số ảnh</label>
                <strong>{{ hotel.images?.length || 0 }}</strong>
            </div>
        </div>

        <form @submit.prevent="submit" class="hotel-form-grid">
            <div class="panel">
                <div class="panel-head"><div class="panel-title">Thông tin chung</div></div>

                <div v-if="Object.keys(clientErrors).length > 0 || serverErrorList.length > 0" class="error-banner">
                    <strong v-if="Object.keys(clientErrors).length > 0">⚠ Còn {{ Object.keys(clientErrors).length }} trường chưa hợp lệ:</strong>
                    <ul v-if="Object.keys(clientErrors).length > 0">
                        <li v-for="(msg, field) in clientErrors" :key="field">{{ msg }}</li>
                    </ul>
                    <strong v-if="serverErrorList.length > 0" :style="Object.keys(clientErrors).length > 0 ? 'margin-top:10px; display:block' : ''">⚠ Server từ chối {{ serverErrorList.length }} trường:</strong>
                    <ul v-if="serverErrorList.length > 0">
                        <li v-for="(msg, i) in serverErrorList" :key="i">{{ msg }}</li>
                    </ul>
                </div>

                <div class="form-grid">
                    <div class="form-field full" :class="{ 'has-error': touched.name && errorOf('name') }">
                        <label>Tên khách sạn <span class="req">*</span></label>
                        <input type="text" v-model="form.name" @blur="markTouched('name')" placeholder="Sofitel Legend Metropole..." />
                        <small v-if="touched.name && errorOf('name')" class="err">⚠ {{ errorOf('name') }}</small>
                    </div>

                    <div class="form-field" :class="{ 'has-error': touched.district && errorOf('district') }">
                        <label>Quận <span class="req">*</span></label>
                        <select v-model="form.district" @blur="markTouched('district')">
                            <option value="">— Chọn quận —</option>
                            <option v-for="d in districts" :key="d" :value="d">{{ d }}</option>
                        </select>
                        <small v-if="touched.district && errorOf('district')" class="err">⚠ {{ errorOf('district') }}</small>
                    </div>

                    <div class="form-field" :class="{ 'has-error': touched.stars && errorOf('stars') }">
                        <label>Hạng sao <span class="req">*</span></label>
                        <select v-model.number="form.stars" @blur="markTouched('stars')">
                            <option :value="null">— Chọn —</option>
                            <option v-for="s in [1,2,3,4,5]" :key="s" :value="s">{{ s }} sao</option>
                        </select>
                        <small v-if="touched.stars && errorOf('stars')" class="err">⚠ {{ errorOf('stars') }}</small>
                    </div>

                    <div class="form-field full" :class="{ 'has-error': touched.address && errorOf('address') }">
                        <label>Địa chỉ <span class="req">*</span></label>
                        <input type="text" v-model="form.address" @blur="markTouched('address')" placeholder="15 Ngô Quyền, Hoàn Kiếm, Hà Nội" />
                        <small v-if="touched.address && errorOf('address')" class="err">⚠ {{ errorOf('address') }}</small>
                    </div>

                    <div class="form-field" :class="{ 'has-error': touched.lat && errorOf('lat') }">
                        <label>Vĩ độ (Lat)</label>
                        <input type="number" v-model.number="form.lat" @blur="markTouched('lat')" step="0.000001" placeholder="21.0285" />
                        <small v-if="touched.lat && errorOf('lat')" class="err">⚠ {{ errorOf('lat') }}</small>
                    </div>
                    <div class="form-field" :class="{ 'has-error': touched.lng && errorOf('lng') }">
                        <label>Kinh độ (Lng)</label>
                        <input type="number" v-model.number="form.lng" @blur="markTouched('lng')" step="0.000001" placeholder="105.8542" />
                        <small v-if="touched.lng && errorOf('lng')" class="err">⚠ {{ errorOf('lng') }}</small>
                    </div>

                    <div class="form-field" :class="{ 'has-error': touched.base_price && errorOf('base_price') }">
                        <label>Giá từ (VND/đêm) <span class="req">*</span></label>
                        <input type="number" v-model.number="form.base_price" @blur="markTouched('base_price')" min="0" step="50000" />
                        <small v-if="touched.base_price && errorOf('base_price')" class="err">⚠ {{ errorOf('base_price') }}</small>
                        <small v-else-if="form.base_price > 0" style="color: var(--ink-500);">≈ {{ fmt(form.base_price) }}đ</small>
                    </div>
                    <div class="form-field" :class="{ 'has-error': touched.phone && errorOf('phone') }">
                        <label>Hotline</label>
                        <input type="tel" v-model="form.phone" @blur="markTouched('phone')" placeholder="024xxxxxxx" />
                        <small v-if="touched.phone && errorOf('phone')" class="err">⚠ {{ errorOf('phone') }}</small>
                    </div>

                    <div class="form-field full" :class="{ 'has-error': touched.email && errorOf('email') }">
                        <label>Email</label>
                        <input type="email" v-model="form.email" @blur="markTouched('email')" placeholder="info@khachsan.vn" />
                        <small v-if="touched.email && errorOf('email')" class="err">⚠ {{ errorOf('email') }}</small>
                    </div>

                    <div class="form-field full">
                        <label>Mô tả</label>
                        <textarea v-model="form.description" rows="3" placeholder="Mô tả ngắn về khách sạn, không gian, vị trí..."></textarea>
                    </div>

                    <div class="form-field full">
                        <label>Tiện nghi ({{ form.amenities.length }} đã chọn)</label>
                        <div class="amenity-grid">
                            <label v-for="a in amenityOptions" :key="a.key" class="amenity-checkbox" :class="{ checked: form.amenities.includes(a.key) }">
                                <input type="checkbox" :checked="form.amenities.includes(a.key)" @change="toggleAmenity(a.key)" />
                                <span>{{ a.label }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-field">
                        <label class="toggle-row">
                            <input type="checkbox" v-model="form.has_vr_tour" />
                            <span>Có VR Tour 360°</span>
                        </label>
                    </div>
                    <div class="form-field">
                        <label class="toggle-row">
                            <input type="checkbox" v-model="form.is_active" />
                            <span>Hoạt động (hiển thị trên trang user)</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <Link v-if="isEdit" :href="route('admin.hotels.index')" class="btn btn-ghost">Hủy</Link>
                    <button type="submit" class="btn btn-emerald" :disabled="form.processing">
                        {{ form.processing ? 'Đang lưu...' : (isEdit ? 'Lưu thay đổi' : 'Tạo khách sạn') }}
                    </button>
                </div>
            </div>

            <div v-if="isEdit" class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-title">Thư viện ảnh</div>
                        <div class="panel-sub">{{ hotel.images?.length || 0 }} ảnh · 1 ảnh chính</div>
                    </div>
                </div>

                <div v-if="!hotel.images || hotel.images.length === 0" class="image-empty">
                    Chưa có ảnh nào. Thêm ảnh đầu tiên ở dưới — sẽ tự động đặt làm ảnh chính.
                </div>

                <div v-else class="image-grid">
                    <div v-for="img in hotel.images" :key="img.id" class="image-tile">
                        <img :src="img.url" :alt="img.caption || ''" />
                        <button type="button" class="img-del" @click="deleteImage(img.id)" title="Xóa">×</button>
                        <span v-if="img.is_primary" class="img-primary">Ảnh chính</span>
                        <span v-if="img.caption" class="img-caption">{{ img.caption }}</span>
                    </div>
                </div>

                <div class="add-image-form">
                    <h4>Thêm ảnh mới</h4>
                    <input
                        v-model="imageForm.url"
                        type="url"
                        placeholder="URL ảnh (https://images.unsplash.com/...)"
                        @keydown.enter.prevent="addImage"
                    />
                    <small v-if="imageForm.errors.url" class="err">⚠ {{ imageForm.errors.url }}</small>
                    <input
                        v-model="imageForm.caption"
                        type="text"
                        placeholder="Chú thích (tùy chọn)"
                        @keydown.enter.prevent="addImage"
                    />
                    <label class="toggle-row">
                        <input v-model="imageForm.is_primary" type="checkbox" />
                        <span>Đặt làm ảnh chính</span>
                    </label>
                    <button
                        type="button"
                        class="btn btn-emerald btn-sm"
                        :disabled="imageForm.processing || !imageForm.url"
                        @click="addImage"
                    >
                        {{ imageForm.processing ? 'Đang tải...' : '+ Thêm ảnh' }}
                    </button>
                </div>
            </div>

            <div v-else class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-title">Thư viện ảnh</div>
                        <div class="panel-sub">{{ form.images.length }} ảnh sẽ được lưu cùng khách sạn</div>
                    </div>
                </div>

                <div v-if="form.images.length === 0" class="image-empty">
                    Chưa có ảnh. Thêm ít nhất 1 ảnh để hiển thị trên trang user — ảnh đầu tiên tự động làm ảnh chính.
                </div>

                <div v-else class="image-grid">
                    <div v-for="(img, idx) in form.images" :key="idx" class="image-tile">
                        <img :src="img.url" :alt="img.caption || ''" @error="$event.target.src='https://placehold.co/200x150/14724f/fbf8f1?text=Invalid+URL'" />
                        <button type="button" class="img-del" @click="removeDraftImage(idx)" title="Xóa">×</button>
                        <button v-if="!img.is_primary" type="button" class="img-set-primary" @click="setDraftPrimary(idx)" title="Đặt làm ảnh chính">⭐</button>
                        <span v-if="img.is_primary" class="img-primary">Ảnh chính</span>
                        <span v-if="img.caption" class="img-caption">{{ img.caption }}</span>
                    </div>
                </div>

                <div class="add-image-form">
                    <h4>Thêm ảnh vào khách sạn</h4>
                    <input
                        v-model="draftImage.url"
                        type="url"
                        placeholder="https://images.unsplash.com/..."
                        @keydown.enter.prevent="addDraftImage"
                        @input="draftImageError = ''"
                    />
                    <small v-if="draftImageError" class="err">⚠ {{ draftImageError }}</small>
                    <input
                        v-model="draftImage.caption"
                        type="text"
                        placeholder="Chú thích (tùy chọn)"
                        @keydown.enter.prevent="addDraftImage"
                    />
                    <label class="toggle-row">
                        <input v-model="draftImage.is_primary" type="checkbox" />
                        <span>Đặt làm ảnh chính</span>
                    </label>
                    <button type="button" class="btn btn-emerald btn-sm" :disabled="!draftImage.url" @click="addDraftImage">
                        + Thêm vào danh sách
                    </button>
                    <small style="color: var(--ink-500); font-size: 11px; line-height: 1.4;">
                        Ảnh sẽ được lưu khi bạn nhấn "Tạo khách sạn". URL phải bắt đầu bằng <code>https://</code>.
                    </small>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
.hotel-stats-bar {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    background: var(--cream);
    border: 1px solid rgba(11, 20, 16, 0.06);
    border-radius: 14px;
    padding: 18px 24px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 20px;
}
.stat {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.stat label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--ink-500);
}
.stat strong {
    font-family: var(--serif);
    font-size: 22px;
    font-weight: 500;
    color: var(--ink-900);
}
.stat code {
    font-family: 'Menlo', monospace;
    font-size: 12px;
    color: var(--emerald-700);
    background: var(--paper);
    padding: 3px 6px;
    border-radius: 4px;
    margin-top: 2px;
    align-self: flex-start;
}
.stat-link {
    font-size: 11px;
    color: var(--emerald-700);
    margin-top: 2px;
}
.hotel-form-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px;
    align-items: start;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.form-field.full { grid-column: 1 / -1; }
.form-field label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ink-700);
}
.form-field input, .form-field select, .form-field textarea {
    border: 1px solid rgba(11, 20, 16, 0.12);
    background: var(--paper);
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 14px;
    outline: none;
    font-family: inherit;
    transition: border-color 0.2s;
}
.form-field input:focus, .form-field select:focus, .form-field textarea:focus {
    border-color: var(--emerald-500);
}
.form-field .err {
    color: var(--rust);
    font-size: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}
.form-field .req {
    color: var(--rust);
    font-weight: 600;
}
.form-field.has-error label {
    color: var(--rust);
}
.form-field.has-error input,
.form-field.has-error select,
.form-field.has-error textarea {
    border-color: var(--rust);
    background: rgba(184, 92, 60, 0.04);
}
.form-field.has-error input:focus,
.form-field.has-error select:focus,
.form-field.has-error textarea:focus {
    border-color: var(--rust);
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
.error-banner strong {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
}
.error-banner ul {
    margin: 0;
    padding-left: 18px;
    line-height: 1.6;
}
.amenity-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.amenity-checkbox {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: var(--paper);
    border: 1px solid transparent;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
}
.amenity-checkbox:hover {
    background: var(--ink-100);
}
.amenity-checkbox.checked {
    background: rgba(31, 155, 106, 0.1);
    border-color: var(--emerald-500);
    color: var(--emerald-900);
    font-weight: 500;
}
.toggle-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    cursor: pointer;
    padding: 8px 0;
}
.form-actions {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(11, 20, 16, 0.06);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.image-empty {
    padding: 32px 20px;
    text-align: center;
    background: var(--paper);
    border: 1px dashed rgba(11, 20, 16, 0.15);
    border-radius: 10px;
    color: var(--ink-500);
    font-size: 13px;
    margin-bottom: 16px;
}
.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}
.image-tile {
    position: relative;
    aspect-ratio: 4/3;
    border-radius: 8px;
    overflow: hidden;
    background: var(--paper);
}
.image-tile img {
    width: 100%; height: 100%; object-fit: cover;
}
.img-del {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px; height: 22px;
    background: rgba(11, 20, 16, 0.7);
    color: var(--cream);
    border-radius: 50%;
    border: none;
    font-size: 16px;
    line-height: 1;
    cursor: pointer;
}
.img-del:hover { background: var(--rust); }
.img-set-primary {
    position: absolute;
    top: 4px;
    left: 4px;
    width: 22px; height: 22px;
    background: rgba(11, 20, 16, 0.6);
    border: none;
    border-radius: 50%;
    font-size: 11px;
    line-height: 1;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.15s;
}
.image-tile:hover .img-set-primary { opacity: 1; }
.img-set-primary:hover { background: var(--gold); }
.img-primary {
    position: absolute;
    bottom: 4px;
    left: 4px;
    background: var(--emerald-700);
    color: var(--cream);
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.img-caption {
    position: absolute;
    bottom: 4px;
    right: 4px;
    background: rgba(11, 20, 16, 0.7);
    color: var(--cream);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 9px;
    max-width: 70%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.add-image-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-top: 16px;
    border-top: 1px solid rgba(11, 20, 16, 0.06);
}
.add-image-form h4 {
    font-family: var(--serif);
    font-size: 14px;
    font-weight: 500;
    color: var(--ink-700);
    margin-bottom: 4px;
}
.add-image-form input {
    border: 1px solid rgba(11, 20, 16, 0.12);
    background: var(--paper);
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
    outline: none;
}
.add-image-form input:focus { border-color: var(--emerald-500); }
.info-panel .info-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.info-panel .info-list li {
    font-size: 13px;
    color: var(--ink-700);
    line-height: 1.5;
    padding-left: 20px;
    position: relative;
}
.info-panel .info-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--emerald-500);
    font-weight: 600;
}
.info-panel .info-list code {
    background: var(--paper);
    padding: 1px 6px;
    border-radius: 4px;
    font-family: 'Menlo', monospace;
    font-size: 12px;
    color: var(--emerald-700);
}
@media (max-width: 1100px) {
    .hotel-form-grid { grid-template-columns: 1fr; }
    .form-grid { grid-template-columns: 1fr; }
    .amenity-grid { grid-template-columns: 1fr 1fr; }
    .hotel-stats-bar { grid-template-columns: 1fr 1fr 1fr; }
}
</style>
