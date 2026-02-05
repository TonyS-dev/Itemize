<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Package,
    DollarSign,
    Tags,
    TrendingUp,
    Crown,
    Sparkles,
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type DashboardStats } from '@/types';

import StatCard from '@/components/dashboard/StatCard.vue';
import TopProductsList from '@/components/dashboard/TopProductsList.vue';
import LowStockAlert from '@/components/dashboard/LowStockAlert.vue';
import CategoryChart from '@/components/dashboard/CategoryChart.vue';

const props = defineProps<{
    stats: DashboardStats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('en-US').format(value);
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-3 overflow-x-auto p-3 lg:p-4">
            <!-- Header (compact) -->
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600">
                    <Sparkles class="h-4 w-4 text-white" />
                </div>
                <div>
                    <h1 class="text-lg font-bold text-foreground">Inventory Overview</h1>
                    <p class="text-xs text-muted-foreground">Track your products and capital</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Products"
                    :value="formatNumber(stats.totalProducts)"
                    :icon="Package"
                    variant="info"
                    :subtitle="`${stats.totalCategories} categories`"
                />
                <StatCard
                    title="Total Capital"
                    :value="formatCurrency(stats.totalCapital)"
                    :icon="DollarSign"
                    variant="success"
                    subtitle="Price × Stock"
                />
                <StatCard
                    title="Average Price"
                    :value="formatCurrency(stats.averagePrice)"
                    :icon="TrendingUp"
                    variant="default"
                    subtitle="Per product"
                />
                <StatCard
                    title="Highest Value"
                    :value="formatCurrency(stats.highestCapitalProduct?.capital ?? 0)"
                    :icon="Crown"
                    variant="warning"
                    :subtitle="stats.highestCapitalProduct?.name ?? 'No products'"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-3 lg:grid-cols-2">
                <!-- Category Chart -->
                <CategoryChart :categories="stats.categoryDistribution" />

                <!-- Top 5 Products -->
                <TopProductsList :products="stats.topProducts" />
            </div>

            <!-- Low Stock Alerts (inline) -->
            <LowStockAlert
                :low-stock-products="stats.lowStockProducts"
                :out-of-stock-products="stats.outOfStockProducts"
            />
        </div>
    </AppLayout>
</template>
