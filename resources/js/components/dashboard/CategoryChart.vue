<script setup lang="ts">
import { PieChart } from 'lucide-vue-next';
import type { CategoryStat } from '@/types';

defineProps<{
    categories: CategoryStat[];
}>();

// Generate colors for categories
const colors = [
    'bg-violet-500',
    'bg-sky-500',
    'bg-emerald-500',
    'bg-amber-500',
    'bg-rose-500',
    'bg-fuchsia-500',
    'bg-cyan-500',
    'bg-orange-500',
];

const getColor = (index: number) => colors[index % colors.length];
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card dark:border-sidebar-border"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-sidebar-border/50 px-3 py-2">
            <div class="flex items-center gap-1.5">
                <div class="flex h-6 w-6 items-center justify-center rounded bg-violet-500/20">
                    <PieChart class="h-3 w-3 text-violet-400" />
                </div>
                <h3 class="text-sm font-semibold text-foreground">By Category</h3>
            </div>
            <span class="text-xs text-muted-foreground">{{ categories.length }} cat.</span>
        </div>

        <!-- Chart visualization -->
        <div class="p-3">
            <!-- Progress bar style chart -->
            <div class="space-y-2">
                <div
                    v-for="(category, index) in categories"
                    :key="category.id"
                    class="group"
                >
                    <div class="mb-0.5 flex items-center justify-between text-sm">
                        <span class="font-medium text-foreground truncate">{{ category.name }}</span>
                        <span class="text-xs text-muted-foreground shrink-0 ml-2">
                            {{ category.count }} <span>({{ category.percentage }}%)</span>
                        </span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                        <div
                            :class="[getColor(index), 'h-full rounded-full transition-all duration-500']"
                            :style="{ width: `${category.percentage}%` }"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="categories.length === 0" class="py-4 text-center text-xs text-muted-foreground">
                No categories
            </div>
        </div>
    </div>
</template>
