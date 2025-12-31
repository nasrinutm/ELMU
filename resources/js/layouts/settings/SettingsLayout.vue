<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const navItems = [
    { name: 'Profile Information', href: route('profile.edit') },
    { name: 'Password Security', href: route('user-password.edit') },
];
</script>

<template>
    <div class="flex flex-col space-y-8 lg:flex-row lg:space-x-12 lg:space-y-0 px-6 py-8">
        <aside class="lg:w-1/5">
            <nav class="flex space-x-2 lg:flex-col lg:space-x-0 lg:space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="block rounded-md px-3 py-2 text-sm font-medium transition-colors"
                    :class="route().current(item.href) ? 'bg-neutral-100 text-black' : 'text-neutral-500 hover:bg-neutral-50 hover:text-black'"
                >
                    {{ item.name }}
                </Link>
            </nav>
        </aside>
        <div class="flex-1 lg:max-w-4xl">
            <slot />
        </div>
    </div>
</template>
