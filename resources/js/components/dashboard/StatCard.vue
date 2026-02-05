<script setup lang="ts">
import { computed } from 'vue';
import type { LucideIcon } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    value: string | number;
    icon: LucideIcon;
    subtitle?: string;
    trend?: 'up' | 'down' | 'neutral';
    trendValue?: string;
    variant?: 'default' | 'success' | 'warning' | 'danger' | 'info';
}>();

const gradientClasses = computed(() => {
    const variants = {
        default: 'from-violet-500/20 to-purple-500/10',
        success: 'from-emerald-500/20 to-green-500/10',
        warning: 'from-amber-500/20 to-yellow-500/10',
        danger: 'from-rose-500/20 to-red-500/10',
        info: 'from-sky-500/20 to-blue-500/10',
    };
    return variants[props.variant ?? 'default'];
});

const iconBgClasses = computed(() => {
    const variants = {
        default: 'bg-violet-500/20 text-violet-400',
        success: 'bg-emerald-500/20 text-emerald-400',
        warning: 'bg-amber-500/20 text-amber-400',
        danger: 'bg-rose-500/20 text-rose-400',
        info: 'bg-sky-500/20 text-sky-400',
    };
    return variants[props.variant ?? 'default'];
});
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-3 sm:p-4 transition-all duration-300 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 dark:border-sidebar-border"
    >
        <!-- Background gradient -->
        <div
            :class="[
                'absolute inset-0 bg-gradient-to-br opacity-50 transition-opacity group-hover:opacity-70',
                gradientClasses,
            ]"
        />

        <!-- Content -->
        <div class="relative z-10">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-muted-foreground truncate py-1.5">
                        {{ title }}
                    </p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-foreground whitespace-nowrap py-1.5">
                        {{ value }}
                    </p>
                    <p v-if="subtitle" class="text-sm text-muted-foreground truncate">
                        {{ subtitle }}
                    </p>
                </div>

                <!-- Icon -->
                <div
                    :class="[
                        'flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg transition-transform group-hover:scale-110',
                        iconBgClasses,
                    ]"
                >
                    <component :is="icon" class="h-4 w-4 sm:h-5 sm:w-5" />
                </div>
            </div>

            <!-- Trend indicator -->
            <div v-if="trendValue" class="mt-2 flex items-center gap-1">
                <span
                    :class="[
                        'text-xs font-medium',
                        trend === 'up' && 'text-emerald-400',
                        trend === 'down' && 'text-rose-400',
                        trend === 'neutral' && 'text-muted-foreground',
                    ]"
                >
                    {{ trendValue }}
                </span>
            </div>
        </div>
    </div>
</template>
