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
    // 1. TEACHER: Dark Teal/Green Theme
    if (hasRole('teacher')) {
        return {
            '--sidebar-background': '166 50% 10%',    // Deep Teal Background
            '--sidebar-foreground': '160 30% 98%',    // White Text
            '--sidebar-primary': '160 84% 39%',       // Bright Emerald Active Item
            '--sidebar-accent': '166 40% 16%',        // Lighter Teal Hover
            '--sidebar-border': '166 40% 16%',        // Matching Border
        };
    }

    // 2. STUDENT: Dark Indigo Theme
    if (hasRole('student')) { // Check student explicitly
        return {
            '--sidebar-background': '224 50% 10%',    // Deep Indigo Background
            '--sidebar-foreground': '210 40% 98%',    // White Text
            '--sidebar-primary': '221.2 83.2% 53.3%', // Bright Blue Active Item
            '--sidebar-accent': '224 40% 16%',        // Lighter Indigo Hover
            '--sidebar-border': '224 40% 16%',        // Matching Border
        };
    }

    // 3. ADMIN (Default): Returns empty object
    // This allows it to use the "Slate" colors from your app.css automatically.
    return {};
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
            { title: 'Student Roster', href: route('students.index'), icon: GraduationCap },
            { title: 'Performance Reports', href: route('reports.index'), icon: BarChart3 },
            { title: 'Discussion Forum', href: route('forum.index'), icon: BookOpen },
            { title: 'Activities & Games', href: route('activities.index'), icon: Gamepad2 },
            { title: 'Quiz Management', href: route('teacher.quiz.index'), icon: CheckCircle },
        ];
    }
    else {
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
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo class="w-auto h-10" />
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
