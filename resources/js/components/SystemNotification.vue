<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed top-5 right-5 z-[100] w-full max-w-sm overflow-hidden rounded-lg bg-[#0f172a] shadow-lg border-l-4 border-teal-500">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-teal-500/20 p-1.5 rounded-full">
                            <CheckCircle class="h-5 w-5 text-teal-500" />
                        </div>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-xs font-bold text-teal-500 uppercase tracking-widest">System Notification</p>
                        <p class="mt-1 text-sm font-semibold text-white">{{ message }}</p>
                    </div>
                    <div class="ml-4 flex flex-shrink-0">
                        <button @click="$emit('close')" class="inline-flex rounded-md text-slate-400 hover:text-white">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { CheckCircle, X } from 'lucide-vue-next';
defineProps({
    show: Boolean,
    message: String
});
defineEmits(['close']);
</script>