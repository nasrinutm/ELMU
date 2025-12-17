<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import { computed, ref } from 'vue'
import { Users, BookOpen, LayoutGrid, FileText } from 'lucide-vue-next'
import { route } from 'ziggy-js';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

const page = usePage()
const user = computed(() => page.props.auth.user as AuthUser | null)

const mainNavItems = ref<NavItem[]>([])

// Menu Logic
if (user.value?.roles.includes('admin')) {
    mainNavItems.value = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'Manage User', href: route('users.index'), icon: Users },
        { title: 'Forum', href: '/forum', icon: BookOpen },
        { title: 'Chatbot', href: '/admin/chatbot', icon: BookOpen },
    ]
} else if (user.value?.roles.includes('teacher')) {
    mainNavItems.value = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'Materials', href: route('materials.index'), icon: FileText },
        { title: 'Forum', href: '/forum', icon: BookOpen }
    ]
} else {
    mainNavItems.value = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'Learning Materials', href: route('materials.index'), icon: BookOpen },
        { title: 'Forum', href: '/forum', icon: BookOpen }
    ]
}

// Empty footer to remove Github Repo
const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo class="w-48 h-48 max-w-none"/>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter v-if="footerNavItems.length" :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
