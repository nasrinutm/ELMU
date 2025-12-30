<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <!--
        1. Page Background -> Yellow (#FFD900)
    -->
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-[#FFD900] p-6 md:p-10"
    >
        <div class="flex w-full max-w-md flex-col gap-6">

            <!--
                2. Login Box -> Dark Blue (#003366)
                We also add 'text-white' so the title reads clearly on blue.
            -->
            <Card class="rounded-xl border-none bg-[#003366] text-black shadow-xl">
                <CardHeader class="px-10 pt-8 pb-0 text-center">

                    <!-- 3. Logo moved INSIDE the box -->
                    <Link
                        :href="home()"
                        class="flex items-center justify-center gap-2 self-center font-medium mb-6"
                    >
                        <!-- White circle background for logo to pop -->
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
