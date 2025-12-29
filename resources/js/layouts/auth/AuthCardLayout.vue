<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-[#FFD900] p-6 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">

            <Card class="rounded-xl border-none bg-[#003366] text-white shadow-xl">
                <CardHeader class="px-10 pt-8 pb-0 text-center">

                    <Link
                        :href="route('home')"
                        class="flex items-center justify-center gap-2 self-center font-medium mb-6"
                    >
                        <div class="flex h-30 w-60 items-center justify-center rounded-full bg-[#ffd900] p-0
                        ">
                            <img
                                src="/logo.png"
                                alt="ELMU"
                                class="h-full w-full object-contain"
                            />
                        </div>
                    </Link>

                    <CardTitle class="text-xl font-bold text-white">
                        {{ title }}
                    </CardTitle>
                    <CardDescription class="text-gray-300">
                        {{ description }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="px-10 py-8">
                    <slot />
                </CardContent>
            </Card>

        </div>
    </div>
</template>