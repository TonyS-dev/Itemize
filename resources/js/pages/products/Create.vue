<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Category } from '@/types';

import FormFields from './FormFields.vue';

const props = defineProps<{
    categories?: Category[];
}>();

const form = useForm({
    name: '',
    stock: 0,
    price: '' as string,
    category_ids: [] as number[],
    image: '',
    gallery: [] as Array<{ image: string; alt?: string }>,
});

const formValues = computed(() => ({
    name: form.name as string,
    stock: form.stock as number,
    price: form.price as string,
    category_ids: form.category_ids,
    image: form.image,
    gallery: form.gallery,
}));

const breadcrumbs = [
    { title: 'Products', href: '/products' },
    { title: 'New Product', href: '/products/create' },
];

import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';
import UnsavedChangesModal from '@/components/UnsavedChangesModal.vue';

// Custom Navigation Guard
const showExitModal = ref(false);
const pendingNavigationUrl = ref<string | null>(null);
const isSubmitting = ref(false);

const submit = () => {
    isSubmitting.value = true;
    form.post('/products', {
        preserveState: false,
        onError: () => {
            isSubmitting.value = false;
        }
        // Note: Don't reset isSubmitting on onFinish/onSuccess 
        // because the page will unmount and redirect
    });
};

const removeStartEventListener = router.on('before', (event) => {
    // If we are submitting (which includes the redirect after success), let it pass
    if (isSubmitting.value) {
        return true;
    }

    // If submitting the form (POST), allow it (double check)
    if (event.detail.visit.method.toLowerCase() !== 'get') {
        return true;
    }

    // If form is dirty, block navigation and show modal
    if (form.isDirty) {
        pendingNavigationUrl.value = String(event.detail.visit.url);
        showExitModal.value = true;
        return false; // Returning false cancels the visit
    }

    return true;
});

const confirmExit = () => {
    showExitModal.value = false;
    // Actually navigate (force visit)
    if (pendingNavigationUrl.value) {
        // Temporarily clear dirty state or just navigate forcefully? 
        // Inertia visit won't trigger router.on('start') again if done right, 
        // OR we just need to ensure we don't block it this time.
        // Simplest: remove listener then visit.
        removeStartEventListener();
        router.visit(pendingNavigationUrl.value);
    }
};

const cancelExit = () => {
    showExitModal.value = false;
    pendingNavigationUrl.value = null;
};

onBeforeUnmount(() => {
    removeStartEventListener();
});
</script>

<template>
    <Head title="New Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center gap-4 p-3 sm:p-4">
            <div
                class="w-full max-w-2xl rounded-lg border border-sky-500 bg-card p-4 sm:p-5"
            >
                <div class="mb-4 flex items-center justify-between">
                    <Heading title="New Product" />
                    <Link
                        href="/products"
                        class="text-sm text-muted-foreground hover:text-foreground"
                        >Back</Link
                    >
                </div>

                <form @submit.prevent="submit">
                    <FormFields
                        :formValues="formValues"
                        :categories="props.categories"
                        :errors="form.errors"
                        @update:name="(v) => (form.name = v)"
                        @update:stock="(v) => (form.stock = v)"
                        @update:price="(v) => (form.price = String(v))"
                        @update:category_ids="(v) => (form.category_ids = v)"
                        @update:image="(v) => (form.image = v)"
                        @update:gallery="(v) => (form.gallery = v)"
                    />

                    <div class="mt-3 flex items-center justify-end">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-sky-500 hover:bg-sky-600"
                            >Save Product</Button
                        >
                    </div>
                </form>
            </div>
        </div>
        <UnsavedChangesModal 
            :open="showExitModal" 
            @confirm="confirmExit" 
            @cancel="cancelExit" 
        />
    </AppLayout>
</template>
