<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import AppLogo from './AppLogo.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';

import {
    Users,
    BookOpen,
    LayoutGrid,
    FileText,
    Gamepad2,
    CheckCircle,
    Bot,
    GraduationCap,
    BarChart3
} from 'lucide-vue-next';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: Array<string | { name: string }>;
}

const page = usePage();
const user = computed(() => page.props.auth.user as AuthUser | null);

// Helper to check roles safely
const hasRole = (roleName: string) => {
    if (!user.value?.roles) return false;
    return user.value.roles.some(r => {
        if (typeof r === 'string') return r === roleName;
        return r.name === roleName;
    });
};

// --- DYNAMIC SIDEBAR THEME ---
const sidebarTheme = computed(() => {
    const baseSettings = {
        '--sidebar-width': '16rem',
        '--sidebar-width-icon': '3rem',
    };

    // 1. TEACHER: Professional Teal Theme
    if (hasRole('teacher')) {
        return {
            ...baseSettings,
            '--sidebar-background': '174 75% 26%',   // Deep Teal
            '--sidebar-foreground': '160 30% 98%',   // White Text
            '--sidebar-primary': '170 75% 50%',      // Bright Teal Active
            '--sidebar-accent': '174 75% 22%',       // Darker Teal Hover
            '--sidebar-border': '174 75% 26%',
        };
    }

    // 2. STUDENT: Vibrant Royal Indigo Theme
    if (hasRole('student')) {
        return {
            ...baseSettings,
            '--sidebar-background': '243 45% 35%',   // Deep Indigo
            '--sidebar-foreground': '226 100% 94%',  // Light Indigo Text
            '--sidebar-primary': '239 84% 67%',      // Bright Indigo Active
            '--sidebar-accent': '244 55% 41%',       // Hover Indigo
            '--sidebar-border': '243 45% 35%',
        };
    }

    // 3. ADMIN (Default Slate Colors)
    return baseSettings;
});

const mainNavItems = computed<NavItem[]>(() => {
    if (hasRole('admin')) {
        return [
            { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
            { title: 'User Management', href: route('users.index'), icon: Users },
            { title: 'AI Knowledge Base', href: route('chatbot.details'), icon: Bot },
            { title: 'Discussion Forum', href: route('forum.index'), icon: BookOpen },
        ];
    }
    else if (hasRole('teacher')) {
        return [
            { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
            { title: 'Learning Materials', href: route('materials.index'), icon: FileText },
            { title: 'Student Progress', href: route('students.index'), icon: GraduationCap },
            { title: 'Performance Reports', href: route('reports.index'), icon: BarChart3 },
            { title: 'Discussion Forum', href: route('forum.index'), icon: BookOpen },
            { title: 'Activities', href: route('activities.index'), icon: Gamepad2 },
            { title: 'Quiz Management', href: route('teacher.quiz.index'), icon: CheckCircle },
        ];
    }
    else {
        // STUDENT NAVIGATION
        return [
            { title: 'Dashboard', href: route('dashboard'), icon: LayoutGrid },
            { title: 'Learning Materials', href: route('materials.index'), icon: FileText },
            { title: 'My Progress', href: user.value ? route('students.show', user.value.id) : '#', icon: BarChart3 },
            { title: 'Discussion Forum', href: route('forum.index'), icon: BookOpen },
            { title: 'Activities', href: route('activities.index'), icon: Gamepad2 },
            { title: 'Quizzes', href: route('quizzes.index'), icon: CheckCircle },
        ];
    }
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        :style="sidebarTheme"
    >
        <SidebarHeader class="bg-sidebar">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo class="w-auto h-10" />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="bg-sidebar">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="bg-sidebar">
            <NavFooter v-if="footerNavItems.length" :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
