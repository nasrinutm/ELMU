<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

// Fix for TypeScript not knowing about global route function
declare const route: Function;

const passwordInput = ref<HTMLInputElement | null>(null);
const isDialogOpen = ref(false);

// Setup standard Inertia form used for password confirmation
const form = useForm({
    password: '',
});

// Function to submit delete request to backend endpoint
const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    isDialogOpen.value = false;
    form.clearErrors();
    form.reset();
};

const onOpenAutoFocus = (e: Event) => {
    e.preventDefault();
    nextTick(() => passwordInput.value?.focus());
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall
            title="Delete account"
            description="Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain."
        />

        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warning</p>
                <p class="text-sm">
                    Please proceed with caution, this cannot be undone.
                </p>
            </div>
            <Dialog v-model:open="isDialogOpen">
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button">
                        Delete account
                    </Button>
                </DialogTrigger>
                <DialogContent @open-auto-focus="onOpenAutoFocus">
                    <DialogHeader>
                        <DialogTitle>Are you sure you want to delete your account?</DialogTitle>
                        <DialogDescription>
                            Once your account is deleted, all of its resources and data will be
                            permanently deleted. Please enter your password to confirm you
                            would like to permanently delete your account.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="deleteUser" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                ref="passwordInput"
                                placeholder="Password"
                                @keyup.enter="deleteUser"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary" @click="closeModal">
                                    Cancel
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="form.processing"
                                data-test="confirm-delete-user-button"
                            >
                                Delete account
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
