<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { X, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    images: Array<{ image: string; alt?: string }>;
    initialIndex?: number;
}>();

const isOpen = defineModel<boolean>('open', { default: false });
const currentIndex = ref(props.initialIndex ?? 0);
const containerRef = ref<HTMLElement | null>(null);

watch(() => props.initialIndex, (val) => {
    currentIndex.value = val ?? 0;
});

// Auto-focus when opened so keyboard events work
watch(isOpen, async (open) => {
    if (open) {
        await nextTick();
        containerRef.value?.focus();
    }
});
const currentImage = () => props.images[currentIndex.value];

const next = () => {
    if (currentIndex.value < props.images.length - 1) {
        currentIndex.value++;
    }
};

const prev = () => {
    if (currentIndex.value > 0) {
        currentIndex.value--;
    }
};

const close = () => {
    isOpen.value = false;
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') next();
    if (e.key === 'ArrowLeft') prev();
};
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="isOpen"
                ref="containerRef"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm"
                @click.self="close"
                @keydown="handleKeydown"
                tabindex="0"
            >
                <!-- Close Button -->
                <button
                    @click="close"
                    class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20"
                >
                    <X class="h-6 w-6" />
                </button>

                <!-- Navigation: Previous -->
                <button
                    v-if="images.length > 1 && currentIndex > 0"
                    @click="prev"
                    class="absolute left-4 rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20"
                >
                    <ChevronLeft class="h-8 w-8" />
                </button>

                <!-- Image -->
                <div class="max-h-[90vh] max-w-[90vw]">
                    <img
                        :src="currentImage()?.image"
                        :alt="currentImage()?.alt || 'Product image'"
                        class="max-h-[85vh] max-w-full rounded-lg object-contain shadow-2xl"
                    />
                    <p v-if="currentImage()?.alt" class="mt-2 text-center text-sm text-white/70">
                        {{ currentImage()?.alt }}
                    </p>
                </div>

                <!-- Navigation: Next -->
                <button
                    v-if="images.length > 1 && currentIndex < images.length - 1"
                    @click="next"
                    class="absolute right-4 rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20"
                >
                    <ChevronRight class="h-8 w-8" />
                </button>

                <!-- Dots Indicator -->
                <div v-if="images.length > 1" class="absolute bottom-6 flex gap-2">
                    <button
                        v-for="(_, i) in images"
                        :key="i"
                        @click="currentIndex = i"
                        class="h-2 w-2 rounded-full transition-all"
                        :class="i === currentIndex ? 'bg-white w-4' : 'bg-white/40 hover:bg-white/60'"
                    />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
