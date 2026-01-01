<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ChatbotWidget from '@/components/ChatbotWidget.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

// Refined themeClass logic to match Spatie role structures
const themeClass = computed(() => {
    const auth = (page.props as any).auth;
    const user = auth?.user;
    // Check both user.roles and auth.roles for maximum compatibility
    const roles = user?.roles || auth?.roles || [];

    const hasRole = (role: string) => roles.some((r: any) =>
        typeof r === 'string' ? r === role : r.name === role
    );

    if (hasRole('admin')) return 'theme-admin';
    if (hasRole('teacher')) return 'theme-teacher';
    if (hasRole('student')) return 'theme-student';

    return '';
});
</script>

<template>
    <AppShell variant="sidebar" :class="[themeClass, 'flex min-h-screen w-full overflow-hidden bg-slate-50']">

        <AppSidebar class="w-[280px] min-w-[280px] max-w-[280px] shrink-0 border-r border-slate-200 h-screen sticky top-0 z-40 bg-sidebar" />

        <AppContent class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden relative bg-slate-50">

            <div class="bg-white border-b border-slate-200 shrink-0 z-30">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            </div>

            <div class="flex-1 overflow-y-auto overflow-x-hidden px-6 md:px-8">
                <main class="w-full mx-auto">
                    <slot />
                </main>
            </div>

            <ChatbotWidget />
        </AppContent>
    </AppShell>
</template>

<style scoped>
/* Force strict sidebar dimensions across all resolutions */
:deep(aside) {
    width: 280px !important;
    min-width: 280px !important;
    max-width: 280px !important;
    flex-shrink: 0 !important;
}

/* Connects the sidebar background to the dynamic CSS variables in app.css */
.bg-sidebar {
    background-color: var(--sidebar-background);
}
</style>
