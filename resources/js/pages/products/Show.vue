<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import DeleteProduct from '@/components/DeleteProduct.vue';
import ImageLightbox from '@/components/ImageLightbox.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Product } from '@/types';

const props = defineProps<{
    product: Product;
}>();

const breadcrumbs = [
    { title: 'Products', href: '/products' },
    { title: props.product.name, href: `/products/${props.product.id}` },
];

const selectedId = ref<number | null>(null);
const isDeleteOpen = ref(false);

const openDelete = (id: number) => {
    selectedId.value = id;
    isDeleteOpen.value = true;
};

const closeDelete = () => {
    isDeleteOpen.value = false;
    selectedId.value = null;
};

const confirmDelete = () => {
    if (selectedId.value === null) return;
    router.delete(`/products/${selectedId.value}`);
    closeDelete();
};

// Lightbox state
const isLightboxOpen = ref(false);
const lightboxIndex = ref(0);

const allImages = () => {
    const images: Array<{ image: string; alt?: string }> = [];
    if (props.product.image) {
        images.push({ image: props.product.image, alt: props.product.name });
    }
    if (props.product.gallery) {
        images.push(...props.product.gallery);
    }
    return images;
};

const openLightbox = (index: number) => {
    lightboxIndex.value = index;
    isLightboxOpen.value = true;
};

// Compute grid layout based on total image count
const mosaicGridClass = computed(() => {
    const galleryCount = props.product.gallery?.length ?? 0;
    const hasMain = props.product.image ? 1 : 0;
    const total = hasMain + galleryCount;

    if (total <= 2) return 'grid-cols-2';
    if (total <= 4) return 'grid-cols-3';
    return 'grid-cols-4';
});
</script>

<template>
    <Head :title="`Details: ${props.product?.name ?? ''}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center gap-6 p-4">
            <div
                class="w-full max-w-2xl overflow-hidden rounded-xl border bg-card shadow-xl"
            >
                <div
                    class="flex items-center justify-between bg-accent px-8 py-6"
                >
                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ props.product.name }}
                        </h1>
                        <span class="text-sm text-muted-foreground"
                            >Product ID: #{{ props.product.id }}</span
                        >
                    </div>
                    <div class="rounded-lg bg-white/20 p-2 backdrop-blur-sm">
                        <span class="font-mono text-xl"
                            >${{ Number(props.product.price).toFixed(2) }}</span
                        >
                    </div>
                </div>

                <!-- Product Image Section -->
                <div class="p-8 border-b">
                    <!-- Single image: full width -->
                    <div v-if="!props.product.gallery || props.product.gallery.length === 0">
                        <img
                            v-if="props.product.image"
                            :src="props.product.image"
                            :alt="props.product.name"
                            class="w-full max-h-[500px] rounded-lg object-cover cursor-pointer ring-1 ring-border transition-all duration-200 hover:scale-[1.02] hover:shadow-2xl"
                            @click="openLightbox(0)"
                        />
                        <div v-else class="w-full h-48 rounded-lg bg-muted flex items-center justify-center">
                            <span class="text-muted-foreground">No image available</span>
                        </div>
                    </div>

                    <!-- Multiple images: mosaic grid -->
                    <div v-else class="grid gap-2" :class="mosaicGridClass">
                        <!-- Main Image (larger) -->
                        <div v-if="props.product.image" class="row-span-2">
                            <img
                                :src="props.product.image"
                                :alt="props.product.name"
                                class="w-full h-full min-h-[200px] max-h-[400px] rounded-lg object-cover cursor-pointer ring-1 ring-border transition-all duration-200 hover:scale-[1.03] hover:shadow-xl hover:z-10 relative"
                                @click="openLightbox(0)"
                            />
                        </div>
                        <!-- Gallery Thumbnails -->
                        <img
                            v-for="(item, i) in props.product.gallery"
                            :key="i"
                            :src="item.image"
                            :alt="item.alt || `Gallery image ${i + 1}`"
                            class="w-full h-full min-h-[100px] max-h-[195px] rounded-lg object-cover cursor-pointer ring-1 ring-border transition-all duration-200 hover:scale-[1.05] hover:shadow-xl hover:z-10 relative"
                            @click="openLightbox(props.product.image ? i + 1 : i)"
                        />
                    </div>
                </div>

                <div class="p-8">
                    <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div
                            class="rounded-lg border p-4"
                        >
                            <h3
                                class="mb-1 text-sm font-bold tracking-wider uppercase"
                            >
                                Availability
                            </h3>
                            <div class="flex items-center">
                                <span
                                    class="text-3xl font-bold"
                                    :class="
                                        props.product.stock > 0
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ props.product.stock }}
                                </span>
                                <span class="ml-2 text-sm text-muted-foreground"
                                    >units in stock</span
                                >
                            </div>

                            <div
                                class="mt-3 h-2.5 w-full rounded-full"
                            >
                                <div
                                    class="h-2.5 rounded-full"
                                    :class="
                                        props.product.stock > 10
                                            ? 'bg-green-500'
                                            : props.product.stock > 0
                                              ? 'bg-yellow-500'
                                              : 'bg-red-500'
                                    "
                                    :style="{
                                        width:
                                            Math.min(props.product.stock, 100) +
                                            '%',
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col justify-center space-y-2 rounded-lg border p-4"
                        >
                            <div>
                                <span
                                    class="text-xs font-bold uppercase"
                                    >Created at</span
                                >
                                <p class="text-sm text-muted-foreground">
                                    {{ props.product.created_at ?? '' }}
                                </p>
                            </div>
                            <div>
                                <span
                                    class="text-xs font-bold uppercase"
                                    >Last updated</span
                                >
                                <p class="text-sm text-muted-foreground">
                                    {{ props.product.updated_at ?? '' }}
                                </p>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div
                            v-if="props.product.categories && props.product.categories.length > 0"
                            class="rounded-lg border p-4"
                        >
                            <h3 class="mb-1 text-sm font-bold tracking-wider uppercase">Categories</h3>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="cat in props.product.categories" 
                                    :key="cat.id"
                                    class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary"
                                >
                                    {{ cat.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between border-t pt-6"
                    >
                        <Link
                            href="/products"
                            class="flex items-center font-medium text-muted-foreground hover:text-foreground"
                        >
                            ← Back to list
                        </Link>

                        <div class="flex gap-3">
                            <Link :href="`/products/${props.product.id}/edit`">
                                <Button
                                    class="bg-yellow-500 font-bold hover:bg-yellow-600"
                                    >Edit</Button
                                >
                            </Link>

                            <Button
                                @click="openDelete(product.id)"
                                variant="destructive"
                                >Delete
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Product Modal -->
        <DeleteProduct
            v-model:open="isDeleteOpen"
            @close="closeDelete"
            @confirm="confirmDelete"
        />

        <!-- Image Lightbox -->
        <ImageLightbox
            v-model:open="isLightboxOpen"
            :images="allImages()"
            :initial-index="lightboxIndex"
        />
    </AppLayout>
</template>
