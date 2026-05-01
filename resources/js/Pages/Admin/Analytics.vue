<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    revenueMonthly: { type: Array, required: true },
    topHotels: { type: Array, required: true },
    statusBreakdown: { type: Array, required: true },
});

const maxRev = computed(() => Math.max(1, ...props.revenueMonthly.map(r => r.value)));
const totalBookings = computed(() => props.statusBreakdown.reduce((s, x) => s + x.count, 0));

const statusColors = {
    pending: 'var(--gold)',
    confirmed: 'var(--emerald-300)',
    completed: 'var(--emerald-500)',
    cancelled: 'var(--rust)',
};
const statusLabels = {
    pending: 'Chờ thanh toán',
    confirmed: 'Đã xác nhận',
    completed: 'Hoàn tất',
    cancelled: 'Đã hủy',
};

function fmtMoney(n) {
    if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(1) + 'B';
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
    if (n >= 1_000) return (n / 1_000).toFixed(0) + 'k';
    return n.toString();
}
function fmt(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}
</script>

<template>
    <Head title="Admin · Phân tích" />
    <AdminLayout page-title="<em>Phân tích</em> & Báo cáo" page-subtitle="Doanh thu 6 tháng và top khách sạn">
        <div class="grid-2">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-title">Doanh thu theo tháng</div>
                        <div class="panel-sub">6 tháng gần đây</div>
                    </div>
                </div>
                <div class="rev-bars">
                    <div v-for="(r, i) in revenueMonthly" :key="i" class="rev-bar-wrap">
                        <div class="rev-bar-value">{{ fmtMoney(r.value) }}</div>
                        <div class="rev-bar" :style="{ height: (r.value / maxRev * 100) + '%' }"></div>
                        <div class="rev-bar-label">{{ r.label }}</div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <div class="panel-title">Trạng thái đơn</div>
                        <div class="panel-sub">{{ totalBookings }} đơn tổng</div>
                    </div>
                </div>
                <div class="status-bars">
                    <div v-for="s in statusBreakdown" :key="s.status" class="status-bar-row">
                        <div class="status-row-head">
                            <span><span class="legend-dot" :style="{ background: statusColors[s.status] }"></span> {{ statusLabels[s.status] }}</span>
                            <strong>{{ s.count }}</strong>
                        </div>
                        <div class="status-bar-track">
                            <div class="status-bar-fill" :style="{ width: (s.count / totalBookings * 100) + '%', background: statusColors[s.status] }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <div class="panel-title">Top khách sạn theo lượt đặt</div>
                    <div class="panel-sub">Tính trên đơn không hủy</div>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Khách sạn</th>
                            <th>Quận</th>
                            <th>Số đơn</th>
                            <th>Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(h, i) in topHotels" :key="i">
                            <td>{{ i + 1 }}</td>
                            <td><strong>{{ h.hotel?.name || '—' }}</strong></td>
                            <td>{{ h.hotel?.district || '—' }}</td>
                            <td>{{ h.bookings_count }}</td>
                            <td style="font-weight: 500">{{ fmt(h.revenue) }}đ</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.rev-bars {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    align-items: end;
    height: 220px;
    padding: 12px 8px 0;
}
.rev-bar-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    justify-content: flex-end;
}
.rev-bar-value {
    font-size: 11px;
    color: var(--ink-700);
    margin-bottom: 4px;
    font-weight: 500;
}
.rev-bar {
    width: 70%;
    background: linear-gradient(to top, var(--emerald-700), var(--emerald-300));
    border-radius: 6px 6px 0 0;
    min-height: 4px;
    transition: height 0.5s;
}
.rev-bar-label {
    margin-top: 6px;
    font-size: 11px;
    color: var(--ink-500);
}
.status-bars {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 8px 0;
}
.status-row-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
    font-size: 13px;
}
.status-bar-track {
    width: 100%;
    height: 8px;
    background: var(--paper);
    border-radius: 999px;
    overflow: hidden;
}
.status-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.4s;
}
</style>
