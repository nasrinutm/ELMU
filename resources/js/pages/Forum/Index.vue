<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    MessageSquare,
    PlusCircle,
    Search,
    Clock,
    MessageCircle,
    User as UserIcon,
    ArrowUpDown
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
// Dynamic role for theme application (Indigo for student)
const userRole = computed(() => page.props.auth.user?.roles[0] || 'student');

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Discussion Forum', href: '/forum' },
];

const search = ref(props.filters.search || '');
const sort = ref(props.filters.sort || 'latest');

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
        <div :class="`theme-${userRole}`" class="min-h-screen space-y-6 font-sans antialiased">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Community Forum</h1>
                    <p class="text-sm text-slate-500 font-normal mt-1">Join the conversation, ask questions, and share knowledge.</p>
                </div>

                <Link :href="route('forum.create')">
                    <Button class="btn-theme font-semibold px-6 py-2.5 rounded-none text-sm shadow-sm transition-all active:scale-95 uppercase tracking-widest">
                        <PlusCircle class="mr-2 h-4 w-4" /> Start New Discussion
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-none border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input
                        v-model="search"
                        placeholder="Search discussions..."
                        class="pl-9 bg-slate-50 border-slate-200 focus:bg-white transition-all h-10 rounded-none text-sm"
                    />
                </div>

                <div class="relative w-full md:w-56">
                    <ArrowUpDown class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
                    <select
                        v-model="sort"
                        class="w-full h-10 pl-9 pr-4 rounded-none border border-slate-200 bg-slate-50 text-sm text-slate-700 focus:ring-1 focus:ring-action focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="replies">Most Replies</option>
                    </select>
                </div>
            </div>

            <div class="space-y-4">
                <div
                    v-for="post in posts.data"
                    :key="post.id"
                    class="group bg-white rounded-none border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-action/30 transition-all cursor-pointer relative"
                >
                    <Link :href="route('forum.show', post.id)" class="absolute inset-0" />

                    <div class="flex items-start gap-5">
                        <div class="shrink-0">
                            <div class="h-10 w-10 rounded-none bg-action/5 text-action flex items-center justify-center font-medium text-sm border border-action/10 uppercase">
                                {{ post.user?.name?.charAt(0) || 'U' }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-slate-900 group-hover:text-action transition-colors truncate">
                                {{ post.title }}
                            </h3>

                            <div class="mt-2 flex items-center gap-4 text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                                <span class="flex items-center gap-1.5">
                                    <UserIcon class="w-3.5 h-3.5 text-action" />
                                    {{ post.user?.name || 'Unknown' }}
                                </span>
                                <span class="flex items-center gap-1.5" :title="post.created_at">
                                    <Clock class="w-3.5 h-3.5 text-action" />
                                    {{ formatDate(post.created_at) }}
                                </span>
                            </div>
                        </div>

                        <div class="hidden md:flex flex-col items-end justify-center min-w-[80px]">
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-none border border-slate-100 group-hover:bg-action/5 group-hover:border-action/10 transition-colors">
                                <MessageCircle class="w-4 h-4 text-slate-400 group-hover:text-action" />
                                <span class="font-bold text-slate-700 tabular-nums group-hover:text-action">{{ post.replies_count }}</span>
                            </div>
                            <span class="text-[9px] text-slate-400 mt-1 uppercase font-bold tracking-widest">Replies</span>
                        </div>
                    </div>
                </div>

                <div v-if="!posts.data || posts.data.length === 0" class="text-center py-20 bg-white rounded-none border-2 border-dashed border-slate-200">
                    <div class="bg-slate-50 h-16 w-16 rounded-none flex items-center justify-center mx-auto mb-4">
                        <MessageSquare class="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 uppercase tracking-tight">No discussions yet</h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto mt-2 mb-6">
                        Be the first to start a conversation in the community!
                    </p>
                    <Link :href="route('forum.create')">
                        <Button class="btn-theme font-semibold px-8 py-2.5 rounded-none text-xs uppercase tracking-widest">
                            Start a Discussion
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="posts.links && posts.links.length > 3" class="flex justify-center pt-6 pb-12">
                <div class="flex gap-1 flex-wrap justify-center">
                    <template v-for="(link, key) in posts.links" :key="key">
                        <div
                            v-if="!link.url"
                            class="px-4 py-2 text-xs text-slate-400 border border-slate-100 rounded-none bg-white font-medium uppercase tracking-widest"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            class="px-4 py-2 text-xs border rounded-none transition-all shadow-sm font-medium uppercase tracking-widest"
                            :class="link.active ? 'btn-theme border-transparent' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                            :href="link.url"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        </div>
    </AppSidebarLayout>
</template>
