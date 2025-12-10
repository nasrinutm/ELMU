<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

const isItemActive = (href: any) => {
    if (!href) return false;

    // 1. Clean Current URL (Remove query params & trailing slashes)
    // e.g. "/dashboard?id=1" -> "/dashboard"
    let currentPath = page.url.split('?')[0].replace(/\/$/, '');
    if (currentPath === '') currentPath = '/'; 

    // 2. Clean Target URL (Remove domain & query params & trailing slashes)
    // e.g. "http://localhost:8000/dashboard" -> "/dashboard"
    let targetPath = String(href);
    if (targetPath.startsWith('http')) {
        try {
            targetPath = new URL(targetPath).pathname;
        } catch (e) {}
    }
    targetPath = targetPath.split('?')[0].replace(/\/$/, '');
    if (targetPath === '') targetPath = '/';

    // ---------------------------------------------------------
    // LOGIC 1: EXACT MATCH
    // ---------------------------------------------------------
    if (currentPath === targetPath) {
        return true;
    }

    // ---------------------------------------------------------
    // LOGIC 2: DASHBOARD ALIAS CHECK
    // Treat "/" and "/dashboard" as the same active state
    // ---------------------------------------------------------
    if ((currentPath === '/' && targetPath === '/dashboard') || 
        (currentPath === '/dashboard' && targetPath === '/')) {
        return true;
    }

    // ---------------------------------------------------------
    // LOGIC 3: SUB-PAGE MATCH (Activities, Materials, etc.)
    // Active if current URL starts with target URL (e.g. /activities/create starts with /activities)
    // We EXCLUDE '/' and '/dashboard' to prevent everything from highlighting on home.
    // ---------------------------------------------------------
    if (targetPath !== '/' && targetPath !== '/dashboard' && currentPath.startsWith(targetPath + '/')) {
        return true;
    }

    return false;
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isItemActive(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>