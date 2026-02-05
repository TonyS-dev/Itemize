<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, PackageX } from 'lucide-vue-next';
import type { ProductWithCapital } from '@/types';

defineProps<{
    lowStockProducts: ProductWithCapital[];
    outOfStockProducts: ProductWithCapital[];
}>();
</script>

<template>
    <div
        v-if="lowStockProducts.length > 0 || outOfStockProducts.length > 0"
        class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <!-- Out of Stock Section -->
        <div v-if="outOfStockProducts.length > 0" class="border-b border-sidebar-border/50">
            <div class="flex items-center gap-1.5 bg-rose-500/10 px-3 py-1.5">
                <PackageX class="h-3 w-3 text-rose-400" />
                <span class="text-xs font-medium text-rose-400">
                    Out of Stock ({{ outOfStockProducts.length }})
                </span>
            </div>
            <div class="flex flex-wrap gap-1.5 p-2">
                <Link
                    v-for="product in outOfStockProducts"
                    :key="product.id"
                    :href="`/products/${product.id}/edit`"
                    class="inline-flex items-center gap-1.5 rounded bg-rose-500/10 px-2 py-1 text-xs font-medium text-rose-400 ring-1 ring-inset ring-rose-500/20 transition-all hover:bg-rose-500/20"
                >
                    {{ product.name }}
                    <span class="rounded bg-rose-500/20 px-1 py-0.5">0</span>
                </Link>
            </div>
        </div>

        <!-- Low Stock Section -->
        <div v-if="lowStockProducts.length > 0">
            <div class="flex items-center gap-1.5 bg-amber-500/10 px-3 py-1.5">
                <AlertTriangle class="h-3 w-3 text-amber-400" />
                <span class="text-xs font-medium text-amber-400">
                    Low Stock ({{ lowStockProducts.length }})
                </span>
            </div>
            <div class="flex flex-wrap gap-1.5 p-2">
                <Link
                    v-for="product in lowStockProducts"
                    :key="product.id"
                    :href="`/products/${product.id}/edit`"
                    class="inline-flex items-center gap-1.5 rounded bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-400 ring-1 ring-inset ring-amber-500/20 transition-all hover:bg-amber-500/20"
                >
                    {{ product.name }}
                    <span class="rounded bg-amber-500/20 px-1 py-0.5 text-xs">{{ product.stock }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>
