<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import ChatbotWidget from '@/components/ChatbotWidget.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
        <ChatbotWidget />
    </AppLayout>
</template>
