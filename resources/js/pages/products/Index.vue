<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Trash2, Pencil, PackagePlus } from 'lucide-vue-next';
import { ref } from 'vue';

import DeleteProduct from '@/components/DeleteProduct.vue';
import ImageLightbox from '@/components/ImageLightbox.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Product, type Category } from '@/types';

// Props from Laravel
defineProps<{
    products?: {
        data?: Array<Product>;
        links?: Array<any>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
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
const lightboxImages = ref<Array<{ image: string; alt?: string }>>([]);

const openImageLightbox = (product: Product, e: Event) => {
    e.stopPropagation();
    if (product.image) {
        lightboxImages.value = [{ image: product.image, alt: product.name }];
        isLightboxOpen.value = true;
    }
};
</script>

<template>
    <Head title="Product Inventory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">

            <!-- Header with title and button -->
            <div class="flex items-center justify-between">
                <Heading
                    title="Product Inventory"
                    description="Manage your stock, prices and product catalog."
                />

                <Button as-child variant="default">
                    <Link href="/products/create">
                        <PackagePlus class="mr-2 h-4 w-4" />
                        New Product
                    </Link>
                </Button>
            </div>

            <!-- Table -->
            <div class="rounded-xl border border-sidebar-border/70 overflow-hidden dark:border-sidebar-border">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="h-10 px-4 font-medium w-20">Image</th>
                        <th class="h-10 px-4 font-medium">Name</th>
                        <th class="h-10 px-4 font-medium">Category</th>
                        <th class="h-10 px-4 font-medium">Price</th>
                        <th class="h-10 px-4 font-medium">Stock</th>
                        <th class="h-10 px-4 font-medium text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr
                        v-for="product in products?.data ?? []"
                        :key="product.id"
                        class="transition-colors hover:bg-muted/50 cursor-pointer"
                        @click="router.visit(`/products/${product.id}`)"
                    >
                        <td class="p-2">
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    class="h-12 w-12 rounded-md object-cover ring-1 ring-border cursor-pointer transition-all duration-200 hover:scale-125 hover:shadow-xl hover:z-10 relative"
                                    @click="openImageLightbox(product, $event)"
                                />
                                <div v-else class="h-12 w-12 rounded-md bg-muted flex items-center justify-center">
                                    <span class="text-xs text-muted-foreground">N/A</span>
                                </div>
                        </td>
                        <td class="p-4 font-medium text-foreground">{{ product.name }}</td>
                        <td class="p-4">
                            <div v-if="product.categories && product.categories.length > 0" class="flex flex-wrap gap-1">
                                <span
                                    v-for="cat in product.categories"
                                    :key="cat.id"
                                    class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                                >
                                    {{ cat.name }}
                                </span>
                            </div>
                            <span v-else class="text-xs text-muted-foreground">—</span>
                        </td>
                        <td class="p-4 text-foreground">${{ Number(product.price).toFixed(2) }}</td>
                        <td class="p-4">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="product.stock > 0
                                        ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/20 dark:text-green-400'
                                        : 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/20 dark:text-red-400'"
                                >
                                    {{ product.stock }} units
                                </span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <Button as-child variant="ghost" size="icon" class="h-8 w-8" @click.stop>
                                    <Link :href="`/products/${product.id}/edit`">
                                        <Pencil class="h-4 w-4 text-muted-foreground hover:text-foreground" />
                                        <span class="sr-only">Edit</span>
                                    </Link>
                                </Button>

                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8 hover:bg-destructive/10 hover:text-destructive"
                                    @click.stop="openDelete(product.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    <span class="sr-only">Delete</span>
                                </Button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="(products?.data ?? []).length === 0">
                        <td colspan="6" class="p-8 text-center text-muted-foreground">
                            No products found. Create your first one!
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="(products?.links ?? []).length > 0" class="flex justify-center gap-1">
                <span
                    v-for="(link, i) in products?.links ?? []"
                    :key="i"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="px-3 py-1 rounded text-sm transition-colors"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted'"
                    />

                    <button
                        v-else
                        class="px-3 py-1 rounded text-sm opacity-50 pointer-events-none"
                        v-html="link.label"
                        disabled
                    />
                </span>
            </div>
        </div>

        <!-- Delete Product Dialog -->
        <DeleteProduct
            v-model:open="isDeleteOpen"
            @close="closeDelete"
            @confirm="confirmDelete"
        />

        <!-- Image Lightbox -->
        <ImageLightbox
            v-model:open="isLightboxOpen"
            :images="lightboxImages"
            :initial-index="0"
        />
    </AppLayout>
</template>
