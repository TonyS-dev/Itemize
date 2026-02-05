<script setup lang="ts">
import { Button } from '@/components/ui/button';

defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        >
            <!-- Overlay click handler separately if needed, or keeping it simple on wrapper -->
            <div 
                class="absolute inset-0" 
                @click="emit('cancel')"
            ></div>
            
            <div class="relative w-full max-w-sm rounded-lg border bg-card p-6 shadow-xl z-50">
                <h3 class="text-lg font-semibold mb-2">Unsaved Changes</h3>
                <p class="text-sm text-muted-foreground mb-6">
                    You have unsaved changes. Are you sure you want to leave? Your changes will be lost.
                </p>
                <div class="flex justify-end gap-2">
                    <Button type="button" variant="ghost" @click="emit('cancel')">
                        Cancel
                    </Button>
                    <Button type="button" variant="destructive" @click="emit('confirm')">
                        Leave Page
                    </Button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
