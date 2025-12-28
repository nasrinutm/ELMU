<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';
// UPDATED: Removed 'Activity', Added 'Gamepad2'
import { Users, BookOpen, LayoutGrid, FileText, Gamepad2, CheckCircle, Bot } from 'lucide-vue-next';
import { route } from 'ziggy-js';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

const page = usePage();
const user = computed(() => page.props.auth.user as AuthUser | null);

const mainNavItems = computed<NavItem[]>(() => {
    // Menu Logic
    if (user.value?.roles.includes('admin')) {
        return [
            { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
            { title: 'Manage User', href: route('users.index'), icon: Users },
            { title: 'Forum', href: '/forum', icon: BookOpen },
            { title: 'Activity', href: route('activities.index'), icon: Gamepad2 }, // <--- Updated Icon
            { title: 'Quiz', href: '#', icon: CheckCircle },
            { title: 'Chatbot', href: '/admin/chatbot', icon: Bot },
        ];
    } else if (user.value?.roles.includes('teacher')) {
        return [
            { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
            { title: 'Materials', href: route('materials.index'), icon: FileText },
            { title: 'Forum', href: route('forum.index'), icon: BookOpen },
            { title: 'Activity', href: route('activities.index'), icon: Gamepad2 },
            // --- FIX FOR TEACHER ---
            // Changed Title to "Manage Quizzes" and Route to "teacher.quiz.index"
            { title: 'Manage Quizzes', href: route('teacher.quiz.index'), icon: CheckCircle },
        ];
    } else {
        return [
            { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
            { title: 'Learning Materials', href: route('materials.index'), icon: BookOpen },
            { title: 'Activity', href: route('activities.index'), icon: Gamepad2 },
            // --- FIX FOR STUDENT ---
            // Changed Title to "Take Quiz" for clarity (Route stays 'quiz.index')
            { title: 'Take Quiz', href: route('quiz.index'), icon: CheckCircle },
        ];
    }
});

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