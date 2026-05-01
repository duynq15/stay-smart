<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    kpis: { type: Object, required: true },
    bookingsByDay: { type: Array, required: true },
    districts: { type: Array, required: true },
    totalHotels: { type: Number, required: true },
    recentBookings: { type: Array, required: true },
});

const maxBarValue = computed(() => Math.max(1, ...props.bookingsByDay.map(d => d.total)));

function fmtMoney(n) {
    if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(1) + 'B';
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
    if (n >= 1_000) return (n / 1_000).toFixed(0) + 'k';
    return n.toString();
}

function fmtFull(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

const donutSegments = computed(() => {
    const total = props.districts.reduce((s, d) => s + d.count, 0);
    const colors = ['var(--emerald-500)', 'var(--emerald-300)', 'var(--gold)', 'var(--rust)', 'var(--ink-300)'];
    let offset = 0;
    return props.districts.map((d, i) => {
        const len = (d.count / total) * 314;
        const seg = {
            color: colors[i % colors.length],
            dasharray: `${len} 314`,
            dashoffset: -offset,
            name: d.name,
            count: d.count,
        };
        offset += len;
        return seg;
    });
});

const statusLabels = {
    pending: { label: 'Chờ thanh toán', class: 'st-pending' },
    confirmed: { label: 'Đã xác nhận', class: 'st-confirmed' },
    completed: { label: 'Hoàn tất', class: 'st-completed' },
    cancelled: { label: 'Đã hủy', class: 'st-cancelled' },
};
</script>

<template>
    <Head title="Admin · Dashboard" />
    <AdminLayout
        :page-title="`Chào, <em>${$page.props.auth.user.name}</em>`"
        :page-subtitle="`Tổng quan hoạt động hệ thống`"
    >
        <div class="kpi-grid">
            <div class="kpi">
                <div class="kpi-label">
                    <span class="kpi-icon emerald">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" /></svg>
                    </span>
                    Doanh thu tháng
                </div>
                <div class="kpi-value">{{ fmtMoney(kpis.monthRevenue) }}đ</div>
                <span class="kpi-trend" :class="kpis.revenueDelta >= 0 ? 'up' : 'down'">
                    {{ kpis.revenueDelta >= 0 ? '▲' : '▼' }} {{ Math.abs(kpis.revenueDelta) }}% so với tháng trước
                </span>
            </div>
            <div class="kpi">
                <div class="kpi-label">
                    <span class="kpi-icon gold">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /></svg>
                    </span>
                    Đơn đặt phòng
                </div>
                <div class="kpi-value">{{ kpis.totalBookings }}</div>
                <span class="kpi-trend up">▲ {{ kpis.todayBookings }} đơn mới hôm nay</span>
            </div>
            <div class="kpi">
                <div class="kpi-label">
                    <span class="kpi-icon dark">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /></svg>
                    </span>
                    Người dùng
                </div>
                <div class="kpi-value">{{ kpis.totalUsers }}</div>
                <span class="kpi-trend up">▲ {{ kpis.newUsersThisWeek }} đăng ký mới tuần này</span>
            </div>
            <div class="kpi">
                <div class="kpi-label">
                    <span class="kpi-icon rust">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>
                    </span>
                    Chat AI hôm nay
                </div>
                <div class="kpi-value">{{ kpis.todayChats }}</div>
                <span class="kpi-trend up">▲ Trợ lý đang hoạt động</span>
            </div>
        </div>

        <div class="grid-2">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-title">Đơn đặt phòng theo ngày</div>
                        <div class="panel-sub">7 ngày qua · phân loại theo trạng thái</div>
                    </div>
                </div>
                <div class="chart-bars">
                    <div v-for="(d, i) in bookingsByDay" :key="i" class="chart-bar-group">
                        <div class="bar bar-completed" :style="{ height: (d.completed / maxBarValue * 100) + '%' }"></div>
                        <div class="bar bar-confirmed" :style="{ height: (d.confirmed / maxBarValue * 100) + '%' }"></div>
                        <div class="bar bar-cancelled" :style="{ height: (d.cancelled / maxBarValue * 100) + '%' }"></div>
                    </div>
                </div>
                <div class="bar-labels">
                    <span v-for="(d, i) in bookingsByDay" :key="i">{{ d.label }}</span>
                </div>
                <div class="chart-legend">
                    <span><span class="legend-dot" style="background: var(--emerald-500)"></span>Hoàn tất</span>
                    <span><span class="legend-dot" style="background: var(--emerald-300)"></span>Xác nhận</span>
                    <span><span class="legend-dot" style="background: var(--rust)"></span>Đã hủy</span>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-title">Phân bố theo quận</div>
                        <div class="panel-sub">{{ totalHotels }} khách sạn</div>
                    </div>
                </div>
                <div class="donut-wrap">
                    <div class="donut">
                        <svg width="130" height="130" viewBox="0 0 130 130">
                            <circle
                                v-for="(seg, i) in donutSegments"
                                :key="i"
                                cx="65" cy="65" r="50"
                                fill="none"
                                :stroke="seg.color"
                                stroke-width="22"
                                :stroke-dasharray="seg.dasharray"
                                :stroke-dashoffset="seg.dashoffset"
                            />
                        </svg>
                        <div class="donut-center">
                            <div class="num">{{ totalHotels }}</div>
                            <div class="lbl">Khách sạn</div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <div v-for="(seg, i) in donutSegments" :key="i" class="row">
                            <span class="name"><span class="legend-dot" :style="{ background: seg.color }"></span>{{ seg.name }}</span>
                            <span class="val">{{ seg.count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <div class="panel-title">Đơn đặt phòng gần đây</div>
                    <div class="panel-sub">5 đơn mới nhất</div>
                </div>
                <Link :href="route('admin.bookings.index')" class="btn btn-ghost btn-sm">Xem tất cả →</Link>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách</th>
                            <th>Khách sạn</th>
                            <th>Nhận / Trả</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in recentBookings" :key="b.booking_code">
                            <td><strong>{{ b.booking_code }}</strong></td>
                            <td>{{ b.guest_name }}</td>
                            <td>{{ b.hotel_name }}</td>
                            <td>{{ b.checkin_date }} → {{ b.checkout_date }}</td>
                            <td style="font-weight: 500">{{ fmtFull(b.total_amount) }}đ</td>
                            <td>
                                <span class="status-pill" :class="statusLabels[b.status].class">{{ statusLabels[b.status].label }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.chart-bar-group {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: 1px;
    height: 100%;
    width: 100%;
    padding: 0 4px;
}
.bar {
    width: 100%;
    border-radius: 3px 3px 0 0;
    min-height: 2px;
}
.bar-completed { background: var(--emerald-500); }
.bar-confirmed { background: var(--emerald-300); }
.bar-cancelled { background: var(--rust); }

.status-pill {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.st-pending { background: rgba(196, 150, 90, 0.15); color: #8b6620; }
.st-confirmed { background: rgba(31, 155, 106, 0.15); color: var(--emerald-900); }
.st-completed { background: rgba(11, 20, 16, 0.08); color: var(--ink-700); }
.st-cancelled { background: rgba(184, 92, 60, 0.15); color: var(--rust); }
</style>
