<script setup lang="ts">
import { ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog';

const props = defineProps<{
    open?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();

const localOpen = ref(Boolean(props.open));
watch(() => props.open, (v) => (localOpen.value = Boolean(v)));
watch(localOpen, (v) => emit('update:open', v));
</script>

<template>
    <Dialog v-model:open="localOpen">
        <DialogTrigger />
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete product</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete this product? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="secondary">Cancel</Button>
                </DialogClose>
                <Button variant="destructive" @click="$emit('confirm')">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
