<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { TrendingUp } from 'lucide-vue-next';
import type { ProductWithCapital } from '@/types';

defineProps<{
    products: ProductWithCapital[];
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-sidebar-border/50 px-3 py-2">
            <div class="flex items-center gap-1.5">
                <div class="flex h-6 w-6 items-center justify-center rounded bg-amber-500/20">
                    <TrendingUp class="h-3 w-3 text-amber-400" />
                </div>
                <h3 class="text-sm font-semibold text-foreground">Top Products</h3>
            </div>
        </div>

        <!-- List -->
        <div class="divide-y divide-sidebar-border/50">
            <Link
                v-for="(product, index) in products"
                :key="product.id"
                :href="`/products/${product.id}`"
                class="flex items-center gap-2 px-3 py-2 transition-colors hover:bg-muted/50"
            >
                <!-- Rank -->
                <div
                    :class="[
                        'flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                        index === 0 && 'bg-amber-500/20 text-amber-400',
                        index === 1 && 'bg-slate-400/20 text-slate-400',
                        index === 2 && 'bg-orange-600/20 text-orange-500',
                        index > 2 && 'bg-muted text-muted-foreground',
                    ]"
                >
                    {{ index + 1 }}
                </div>

                <!-- Product image -->
                <div class="h-8 w-8 shrink-0 overflow-hidden rounded bg-muted">
                    <img
                        v-if="product.image"
                        :src="product.image"
                        :alt="product.name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-xs text-muted-foreground">
                        N/A
                    </div>
                </div>

                <!-- Product info -->
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-foreground">{{ product.name }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatCurrency(product.price) }} × {{ product.stock }}
                    </p>
                </div>

                <!-- Capital value -->
                <div class="text-right">
                    <p class="text-sm font-semibold text-foreground">{{ formatCurrency(product.capital) }}</p>
                </div>
            </Link>

            <!-- Empty state -->
            <div v-if="products.length === 0" class="px-3 py-4 text-center text-xs text-muted-foreground">
                No products
            </div>
        </div>
    </div>
</template>
