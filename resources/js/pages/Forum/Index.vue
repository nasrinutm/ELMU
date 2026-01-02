<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    PlusCircle,
    Search,
    MessageCircle,
    ArrowUpDown,
    CheckCircle2,
    X
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';
import { route } from 'ziggy-js';

const props = defineProps<{
    posts: {
        data: Array<{
            id: number;
            title: string;
            content?: string;
            user: { name: string; avatar?: string };
            created_at: string;
            replies_count: number;
        }>;
        links: Array<any>;
    };
    can_create: boolean;
    filters: {
        search?: string;
        sort?: string;
    };
}>();

const page = usePage();

// --- DYNAMIC THEME LOGIC ---
const themeClasses = computed(() => {
    // 1. Extract the raw role from props
    const rawRole = page.props.auth.user?.roles?.[0];

    // 2. FIXED: Use explicit type checking and casting to resolve "type never" error
    let roleName = 'student'; // Fallback default
    if (rawRole) {
        roleName = (typeof rawRole === 'object' && 'name' in rawRole)
            ? (rawRole as { name: string }).name
            : (rawRole as string);
    }

    const normalizedRole = roleName.toLowerCase();

    switch (normalizedRole) {
        case 'admin':
            // Slate-Grey matches --action-color: #1e293b
            return {
                button: 'bg-[#1e293b] hover:bg-[#0f172a]',
                icon: 'text-[#1e293b]',
                border: 'group-hover:border-[#1e293b]',
                text: 'group-hover:text-[#1e293b]'
            };
        case 'teacher':
            // Teal matches --action-color: #0d9488
            return {
                button: 'bg-[#0d9488] hover:bg-[#0f766e]',
                icon: 'text-[#0d9488]',
                border: 'group-hover:border-[#0d9488]',
                text: 'group-hover:text-[#0d9488]'
            };
        case 'student':
            // Indigo matches --action-color: #4338ca
            return {
                button: 'bg-[#4338ca] hover:bg-[#3730a3]',
                icon: 'text-[#4338ca]',
                border: 'group-hover:border-[#4338ca]',
                text: 'group-hover:text-[#4338ca]'
            };
        default:
            return {
                button: 'bg-slate-900 hover:bg-slate-800',
                icon: 'text-slate-400',
                border: 'group-hover:border-slate-900',
                text: 'group-hover:text-slate-900'
            };
    }
});

const showSuccessNotification = ref(false);

const breadcrumbs = [
    { title: 'Forum', href: '/forum' },
];

const search = ref(props.filters.search || '');
const sort = ref(props.filters.sort || 'latest');

// --- NOTIFICATION WATCHER ---
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (flash?.success) {
            showSuccessNotification.value = false;
            nextTick(() => {
                showSuccessNotification.value = true;
                setTimeout(() => { showSuccessNotification.value = false; }, 5000);
            });
        }
    },
    { deep: true, immediate: true }
);

const updateFilters = debounce(() => {
    router.get(route('forum.index'), {
        search: search.value,
        sort: sort.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch([search, sort], () => {
    updateFilters();
});

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - date.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays <= 1) return 'Today';
    if (diffDays <= 7) return `${diffDays} days ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head title="Discussion Forum" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen space-y-6 font-sans antialiased relative">

            <transition name="toast">
                <div v-if="showSuccessNotification" class="fixed top-10 right-10 z-[100] flex items-center gap-4 bg-slate-900 text-white p-5 shadow-2xl border-l-4 border-emerald-500 min-w-[350px]">
                    <div class="bg-emerald-500/20 p-2"><CheckCircle2 class="w-6 h-6 text-emerald-500" /></div>
                    <div class="flex-grow">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-500">System Notification</p>
                        <p class="text-sm font-medium">{{ (page.props as any).flash.success }}</p>
                    </div>
                    <button @click="showSuccessNotification = false" class="text-slate-500 hover:text-white transition-colors">
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </transition>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight uppercase">Community Forum</h1>
                    <p class="text-sm text-slate-500 font-normal mt-1 italic">Join the conversation and share knowledge.</p>
                </div>

                <Link :href="route('forum.create')">
                    <Button
                        class="text-white font-bold px-6 py-2.5 rounded-none text-[10px] shadow-sm transition-all active:scale-95 uppercase tracking-[0.15em] border-none"
                        :class="themeClasses.button"
                    >
                        <PlusCircle class="mr-2 h-4 w-4" /> Start New Discussion
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-none border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input v-model="search" placeholder="Search discussions..." class="pl-9 bg-slate-50 border-slate-200 focus:bg-white transition-all h-10 rounded-none text-sm" />
                </div>

                <div class="relative w-full md:w-56">
                    <ArrowUpDown class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
                    <select v-model="sort" class="w-full h-10 pl-9 pr-4 rounded-none border border-slate-200 bg-slate-50 text-sm text-slate-700 focus:ring-1 focus:ring-slate-900 appearance-none cursor-pointer">
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="replies">Most Replies</option>
                    </select>
                </div>
            </div>

            <div class="space-y-4">
                <div v-for="post in posts.data" :key="post.id"
                    class="group bg-white rounded-none border border-slate-200 p-5 shadow-sm transition-all cursor-pointer relative border-l-2 border-l-transparent"
                    :class="themeClasses.border"
                >
                    <Link :href="route('forum.show', post.id)" class="absolute inset-0" />
                    <div class="flex items-start gap-5">
                        <div class="shrink-0">
                            <div class="h-10 w-10 rounded-none text-white flex items-center justify-center font-bold text-sm uppercase"
                                :class="themeClasses.button"
                            >
                                {{ post.user?.name?.charAt(0) || 'U' }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-slate-900 transition-colors truncate uppercase tracking-tight"
                                :class="themeClasses.text"
                            >
                                {{ post.title }}
                            </h3>
                            <div class="mt-2 flex items-center gap-4 text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                <span>@{{ post.user?.name || 'Unknown' }}</span>
                                <span>{{ formatDate(post.created_at) }}</span>
                            </div>
                        </div>
                        <div class="hidden md:flex flex-col items-end justify-center min-w-[80px]">
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 border border-slate-100 transition-colors"
                                :class="themeClasses.border"
                            >
                                <MessageCircle class="w-4 h-4" :class="themeClasses.icon" />
                                <span class="font-bold text-slate-900">{{ post.replies_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from { transform: translateX(100%); opacity: 0; }
.toast-leave-to { transform: translateY(-20px); opacity: 0; }
</style>
