<script setup lang="ts">
import { ref, computed } from 'vue';
import { Plus, X, FolderPlus, Upload, Loader2, Check, Trash2 } from 'lucide-vue-next';
import axios from 'axios';
import { type Category } from '@/types';
import { usePage } from '@inertiajs/vue3';
import ImageLightbox from '@/components/ImageLightbox.vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    formValues: {
        name: string;
        stock: number;
        price: string | number;
        category_ids?: number[];
        image?: string;
        gallery?: Array<{ image: string; alt?: string }>;
    };
    categories?: Category[];
    errors?: Record<string, any>;
}>();

const emit = defineEmits<{
    (e: 'update:name', value: string): void;
    (e: 'update:stock', value: number): void;
    (e: 'update:price', value: string): void;
    (e: 'update:category_ids', value: number[]): void;
    (e: 'update:image', value: string): void;
    (e: 'update:gallery', value: Array<{ image: string; alt?: string }>): void;
}>();

// Lightbox state
const isLightboxOpen = ref(false);
const lightboxImages = ref<Array<{ image: string; alt?: string }>>([]);
const lightboxIndex = ref(0);

const openLightbox = (image: string, alt?: string) => {
    lightboxImages.value = [{ image, alt }];
    lightboxIndex.value = 0;
    isLightboxOpen.value = true;
};

// Image upload state
const mainImageInput = ref<HTMLInputElement | null>(null);
const galleryImageInput = ref<HTMLInputElement | null>(null);
const mainImageUploading = ref(false);
const galleryImageUploading = ref(false);
const uploadError = ref('');
const mainImageWasUploaded = ref(false);

const uploadImage = async (file: File): Promise<string | null> => {
    // Client-side size validation (5MB)
    if (file.size > 5 * 1024 * 1024) {
        uploadError.value = 'The image size must be less than 5MB.';
        return null;
    }

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await axios.post('/images/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data.url;
    } catch (err: any) {
        // Handle Laravel validation errors (422) specifically
        if (err.response?.status === 422 && err.response?.data?.errors?.image) {
            uploadError.value = err.response.data.errors.image[0];
        } else {
            uploadError.value = err.response?.data?.message || 'Failed to upload image';
        }
        return null;
    }
};

const onMainImageFileChange = async (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    mainImageUploading.value = true;
    uploadError.value = '';

    const url = await uploadImage(file);
    if (url) {
        emit('update:image', url);
        mainImageWasUploaded.value = true;
    }

    mainImageUploading.value = false;
    target.value = ''; // Reset input
};

    const MAX_GALLERY_IMAGES = 6;

    const onGalleryImageFileChange = async (e: Event) => {
    const target = e.target as HTMLInputElement;
    const files = target.files;
    if (!files || files.length === 0) return;

    const currentCount = props.formValues.gallery?.length ?? 0;
    if (currentCount + files.length > MAX_GALLERY_IMAGES) {
        uploadError.value = `You can only have a maximum of ${MAX_GALLERY_IMAGES} gallery images.`;
        target.value = '';
        return;
    }

    galleryImageUploading.value = true;
    uploadError.value = '';

    // Upload in parallel ("chunk")
    const uploadPromises = Array.from(files).map(file => uploadImage(file));
    const results = await Promise.all(uploadPromises);
    
    // Filter out failed uploads (nulls)
    const newUrls = results.filter((url): url is string => url !== null);

    if (newUrls.length > 0) {
        const gallery = [...(props.formValues.gallery ?? [])];
        newUrls.forEach(url => gallery.push({ image: url, alt: '' }));
        emit('update:gallery', gallery);
    }

    galleryImageUploading.value = false;
    target.value = ''; // Reset input
};

// Gallery URL state
const newGalleryUrl = ref('');

const addGalleryImage = () => {
    if (!newGalleryUrl.value.trim()) return;
    
    const currentCount = props.formValues.gallery?.length ?? 0;
    if (currentCount >= MAX_GALLERY_IMAGES) {
        uploadError.value = `Maximum of ${MAX_GALLERY_IMAGES} gallery images reached.`;
        return;
    }

    const gallery = [...(props.formValues.gallery ?? [])];
    gallery.push({ image: newGalleryUrl.value.trim(), alt: '' });
    emit('update:gallery', gallery);
    newGalleryUrl.value = '';
    uploadError.value = ''; // Clear any previous error
};

const removeGalleryImage = (index: number) => {
    const gallery = [...(props.formValues.gallery ?? [])];
    gallery.splice(index, 1);
    emit('update:gallery', gallery);
};

// Category creation
const showCategoryModal = ref(false);
const newCategoryName = ref('');
const categoryLoading = ref(false);
const categoryError = ref('');
const localCategories = ref<Category[]>([]);
const newCategoryInput = ref<HTMLInputElement | null>(null);

const categoriesList = computed(() => {
    return localCategories.value.length > 0 ? localCategories.value : (props.categories ?? []);
});

const createCategory = async () => {
    if (!newCategoryName.value.trim()) return;
    categoryLoading.value = true;
    categoryError.value = '';

    try {
        const response = await axios.post('/categories', {
            name: newCategoryName.value.trim(),
        });
        const newCat = response.data.category;

        // Update local categories
        localCategories.value = [...categoriesList.value, newCat];

        // Add to selected categories
        const currentIds = [...(props.formValues.category_ids ?? [])];
        if (!currentIds.includes(newCat.id)) {
            emit('update:category_ids', [...currentIds, newCat.id]);
        }

        newCategoryName.value = '';
        showCategoryModal.value = false;
    } catch (err: any) {
        // Validation error from backend (includes duplicate name check)
        categoryError.value = err.response?.data?.message || 'Failed to create category';
    } finally {
        categoryLoading.value = false;
    }
};

const toggleCategory = (categoryId: number) => {
    const currentIds = [...(props.formValues.category_ids ?? [])];
    const index = currentIds.indexOf(categoryId);
    
    if (index === -1) {
        emit('update:category_ids', [...currentIds, categoryId]);
    } else {
        currentIds.splice(index, 1);
        emit('update:category_ids', currentIds);
    }
};

function onNameInput(e: Event) {
    const target = e.target as HTMLInputElement | null;
    if (!target) return;
    emit('update:name', target.value);
}

function onStockInput(e: Event) {
    const target = e.target as HTMLInputElement | null;
    if (!target) return;
    emit('update:stock', Number(target.value));
}

function onPriceInput(e: Event) {
    const target = e.target as HTMLInputElement | null;
    if (!target) return;
    emit('update:price', target.value);
}

function onImageInput(e: Event) {
    const target = e.target as HTMLInputElement | null;
    if (!target) return;
    emit('update:image', target.value);
    // If manually typing, user is providing a URL, so we 'reset' the uploaded state
    // so they can see what they are typing.
    mainImageWasUploaded.value = false;
}

// Compute display value for image input
// If uploaded, show empty string (or placeholder via UI), else show actual value
const mainImageDisplayValue = computed(() => {
    if (mainImageWasUploaded.value) {
        return '';
    }
    return trimUrl(props.formValues.image ?? '');
});

// URL Trimming Logic
const CLOUDINARY_PREFIX = 'https://res.cloudinary.com/tonys-dev-cloud/';

function trimUrl(url: string) {
    if (!url) return '';
    if (url.startsWith(CLOUDINARY_PREFIX)) {
        return url.replace(CLOUDINARY_PREFIX, '');
    }
    return url;
}

function expandUrl(partial: string) {
    if (!partial) return '';
    if (partial.startsWith('http://') || partial.startsWith('https://')) {
        return partial;
    }
    return CLOUDINARY_PREFIX + partial;
}

// Override onImageInput to use expandUrl
function onImageInputWrapper(e: Event) {
    const target = e.target as HTMLInputElement | null;
    if (!target) return;
    emit('update:image', expandUrl(target.value));
    mainImageWasUploaded.value = false;
}

// Gallery Input Wrapper
function onGalleryInputWrapper(e: Event) {
    const target = e.target as HTMLInputElement | null;
    if (!target) return;
    // We treat the input as valid partial URL, immediately expanding it for the model (newGalleryUrl is local ref though)
    // Actually newGalleryUrl is used to ADD. So we should just store the raw value or expanded?
    // Let's store the trimmed value in the input, but expand when adding.
    // Wait, v-model syncs the input. 
    // Let's use :value and @input for gallery input too.
    newGalleryUrl.value = target.value; 
}

// When adding gallery image, expand it
const addGalleryImageWrapper = () => {
    if (!newGalleryUrl.value.trim()) return;
    
    const currentCount = props.formValues.gallery?.length ?? 0;
    if (currentCount >= MAX_GALLERY_IMAGES) {
        uploadError.value = `Maximum of ${MAX_GALLERY_IMAGES} gallery images reached.`;
        return;
    }

    const gallery = [...(props.formValues.gallery ?? [])];
    gallery.push({ image: expandUrl(newGalleryUrl.value.trim()), alt: '' });
    emit('update:gallery', gallery);
    newGalleryUrl.value = '';
    uploadError.value = ''; 
};

// Category Deletion
const deleteCategory = async (categoryId: number, event: Event) => {
    // Stop propagation to avoid toggling selection
    event.stopPropagation();
    
    // No confirmation as requested
    try {
        await axios.delete(`/categories/${categoryId}`);
        
        // Remove from local list (ensure we have a list to manipulate)
        let currentList = categoriesList.value;
        localCategories.value = currentList.filter(c => c.id !== categoryId);
        
        // Remove from selection if present
        const currentIds = [...(props.formValues.category_ids ?? [])];
        if (currentIds.includes(categoryId)) {
            emit('update:category_ids', currentIds.filter(id => id !== categoryId));
        }

    } catch (err: any) {
        alert('Failed to delete category: ' + (err.response?.data?.message || err.message));
    }
};
</script>

<template>
    <div class="grid gap-2">
        <!-- Name -->
        <div>
            <label class="block text-base font-semibold mb-0.5">Name</label>
            <input :value="props.formValues.name" @input="onNameInput" class="w-full rounded border px-2 py-1.5 text-base" />
            <p v-if="props.errors?.name" class="text-sm text-red-600">{{ props.errors.name }}</p>
        </div>

        <!-- Stock + Price in parallel -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-base font-semibold mb-0.5">Stock</label>
                <input type="number" :value="props.formValues.stock" @input="onStockInput" class="w-full rounded border px-2 py-1.5 text-base" />
                <p v-if="props.errors?.stock" class="text-sm text-red-600">{{ props.errors.stock }}</p>
            </div>
            <div>
                <label class="block text-base font-semibold mb-0.5">Price</label>
                <input type="number" step="0.01" :value="props.formValues.price" @input="onPriceInput" class="w-full rounded border px-2 py-1.5 text-base" />
                <p v-if="props.errors?.price" class="text-sm text-red-600">{{ props.errors.price }}</p>
            </div>
        </div>

        <!-- Categories (compact) -->
        <div>
            <div class="flex items-center justify-between mb-0.5">
                <label class="block text-base font-semibold">Categories</label>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="h-6 px-2 text-sm"
                    @click="showCategoryModal = true"
                >
                    <FolderPlus class="h-4 w-4 mr-1" />
                    New
                </Button>
            </div>
            
            <div class="rounded border px-2 py-1.5 bg-background max-h-[65px] overflow-y-auto">
                <div v-if="categoriesList.length === 0" class="text-sm text-muted-foreground text-center">
                    No categories
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-2 gap-y-0.5">
                    <div 
                        v-for="cat in categoriesList" 
                        :key="cat.id"
                        class="group flex items-center gap-1 py-0.5 hover:bg-muted rounded cursor-pointer pr-1"
                        @click="toggleCategory(cat.id)"
                    >
                        <div 
                            class="h-4 w-4 rounded border flex items-center justify-center shrink-0"
                            :class="props.formValues.category_ids?.includes(cat.id) ? 'bg-primary border-primary text-primary-foreground' : 'border-input'"
                        >
                            <Check v-if="props.formValues.category_ids?.includes(cat.id)" class="h-3 w-3" />
                        </div>
                        <span class="text-sm truncate flex-1">{{ cat.name }}</span>
                        
                        <!-- Delete Button shown on hover -->
                        <button
                            type="button"
                            class="opacity-0 group-hover:opacity-100 transition-opacity text-muted-foreground hover:text-destructive p-0.5 ml-1"
                            @click="(e) => deleteCategory(cat.id, e)"
                            title="Delete Category"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
            <p v-if="props.errors?.category_ids" class="text-sm text-red-600">{{ props.errors.category_ids }}</p>
        </div>

        <!-- Category Creation Modal -->
        <Teleport to="body">
            <div
                v-if="showCategoryModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
                @click.self="showCategoryModal = false"
                @keydown.escape="showCategoryModal = false"
            >
                <div class="w-full max-w-xs rounded-lg border bg-card p-4 shadow-xl">
                    <h3 class="text-sm font-semibold mb-2">Create Category</h3>
                    <input
                        ref="newCategoryInput"
                        v-model="newCategoryName"
                        placeholder="Category name"
                        class="w-full rounded border px-2 py-1.5 text-sm mb-2"
                        v-autofocus
                        @keydown.enter.prevent="createCategory"
                    />
                    <p v-if="categoryError" class="text-xs text-red-600 mb-2">{{ categoryError }}</p>
                    <div class="flex justify-end gap-2">
                        <Button type="button" variant="ghost" size="sm" @click="showCategoryModal = false">Cancel</Button>
                        <Button type="button" size="sm" :disabled="categoryLoading" @click="createCategory">
                            {{ categoryLoading ? '...' : 'Create' }}
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Images: Main + Gallery in 2-column layout -->
        <div class="grid gap-2 sm:grid-cols-2">
            <!-- Main Image -->
            <div>
                <label class="block text-base font-semibold mb-0.5">Main Image</label>
                <div class="flex gap-1">
                    <input
                        :value="mainImageDisplayValue"
                        @input="onImageInputWrapper"
                        :placeholder="mainImageWasUploaded ? '✓ Uploaded' : 'URL or upload'"
                        class="flex-1 rounded border px-2 py-1 text-sm min-w-0"
                    />
                    <input
                        ref="mainImageInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="onMainImageFileChange"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        class="h-7 w-7 shrink-0"
                        :disabled="mainImageUploading"
                        @click="mainImageInput?.click()"
                    >
                        <Loader2 v-if="mainImageUploading" class="h-3 w-3 animate-spin" />
                        <Upload v-else class="h-3 w-3" />
                    </Button>
                </div>
                <p v-if="props.errors?.image" class="text-sm text-red-600">{{ props.errors.image }}</p>
                <div v-if="props.formValues.image" class="mt-1">
                    <img
                        :src="props.formValues.image"
                        alt="Preview"
                        class="h-20 w-20 rounded object-cover ring-1 ring-border cursor-pointer hover:scale-110 transition-transform"
                        @click="openLightbox(props.formValues.image!, 'Main Image')"
                    />
                </div>
            </div>

            <!-- Gallery Images -->
            <div>
                <label class="block text-base font-semibold mb-0.5">Gallery</label>
                <div class="flex gap-1">
                    <input
                        :value="newGalleryUrl"
                        @input="(e) => newGalleryUrl = (e.target as HTMLInputElement).value"
                        placeholder="URL or upload"
                        class="flex-1 rounded border px-2 py-1 text-sm min-w-0"
                        @keydown.enter.prevent="addGalleryImageWrapper"
                    />
                    <Button type="button" variant="outline" size="icon" class="h-7 w-7 shrink-0" @click="addGalleryImageWrapper">
                        <Plus class="h-3 w-3" />
                    </Button>
                    <input
                        ref="galleryImageInput"
                        type="file"
                        accept="image/*"
                        multiple
                        class="hidden"
                        @change="onGalleryImageFileChange"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        class="h-7 w-7 shrink-0"
                        :disabled="galleryImageUploading"
                        @click="galleryImageInput?.click()"
                    >
                        <Loader2 v-if="galleryImageUploading" class="h-3 w-3 animate-spin" />
                        <Upload v-else class="h-3 w-3" />
                    </Button>
                </div>
                <!-- Gallery Thumbnails -->
                <div v-if="props.formValues.gallery && props.formValues.gallery.length > 0" class="mt-1 flex flex-wrap gap-1">
                    <div
                        v-for="(item, i) in props.formValues.gallery"
                        :key="i"
                        class="relative group"
                    >
                        <img
                            :src="item.image"
                            :alt="item.alt || `Gallery ${i + 1}`"
                            class="h-20 w-20 rounded object-cover ring-1 ring-border cursor-pointer hover:scale-110 transition-transform"
                            @click="openLightbox(item.image, item.alt)"
                        />
                        <button
                            type="button"
                            class="absolute -top-1 -right-1 rounded-full bg-destructive p-0.5 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                            @click="removeGalleryImage(i)"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                <p v-if="props.errors?.gallery" class="text-sm text-red-600">{{ props.errors.gallery }}</p>
            </div>
        </div>

        <p v-if="uploadError" class="text-sm text-red-600">{{ uploadError }}</p>
    </div>

    <!-- Lightbox -->
    <ImageLightbox
        v-model:open="isLightboxOpen"
        :images="lightboxImages"
        :initial-index="lightboxIndex"
    />
</template>

<script lang="ts">
// Custom directive for autofocus
const vAutofocus = {
  mounted: (el: HTMLInputElement) => el.focus()
};
</script>
