<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Fix for TypeScript not knowing about the global route function
declare const route: Function;

// Define navigation items using standard Laravel route names
const sidebarNavItems = computed(() => [
    {
        title: 'Profile',
        href: route('profile.edit'),
        active: route().current('profile.edit'),
    },
    {
        title: 'Password',
        href: route('password.edit'),
        active: route().current('password.edit') || route().current('password.update'),
    },
    // Uncomment below if you add Two-Factor routes to web.php later
    // {
    //     title: 'Two-Factor Auth',
    //     href: route('two-factor.show'),
    //     active: route().current('two-factor.*'),
    // },
]);
</script>

<template>
    <div class="px-4 py-6">
        <HeadingSmall
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.title"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': item.active },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
