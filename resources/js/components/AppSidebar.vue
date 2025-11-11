<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Users, BookOpen, Folder, LayoutGrid } from 'lucide-vue-next'
import { route } from 'ziggy-js';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[]; // <-- This is the array of role names from Spatie
}

const page = usePage()
// Use computed to cast the user and make it reactive
const user = computed(() => page.props.auth.user as AuthUser | null)

const mainNavItems = ref<NavItem[]>([])

if (user.value?.roles.includes('admin')) {
    mainNavItems.value = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'Manage User', href: route('users.index'), icon: Users } // 👈 Use Ziggy
    ]
} else if (user.value?.roles.includes('teacher')) {
    mainNavItems.value = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'My Classes', href: '/classes', icon: Folder },
        { title: 'Assignments', href: '/assignments', icon: BookOpen },
    ]
} else {
    // This will be the default for 'student' or any other role
    mainNavItems.value = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'My Courses', href: '/courses', icon: BookOpen },
        { title: 'Forum', href: '/forum', icon: BookOpen }
    ]
}

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
