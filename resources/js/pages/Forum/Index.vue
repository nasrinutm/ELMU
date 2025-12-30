<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
// FIX: Import 'route' to resolve the TypeScript error
import { route } from 'ziggy-js';

// Define Props
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

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Discussion Forum', href: '/forum' },
];

// State
const search = ref(props.filters.search || '');
const sort = ref(props.filters.sort || 'latest');

// Debounce Search & Sort
const updateFilters = debounce(() => {
    router.get(route('forum.index'), {
        search: search.value,
        sort: sort.value
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

watch([search, sort], () => {
    updateFilters();
});

// Helpers
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
        <div class="min-h-screen bg-slate-50 space-y-6">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Community Forum</h1>
                    <p class="text-slate-500 mt-1">Join the conversation, ask questions, and share knowledge.</p>
                </div>

                <Link :href="route('forum.create')">
                    <Button class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white shadow-sm gap-2">
                        <PlusCircle class="w-4 h-4" /> Start New Discussion
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input
                        v-model="search"
                        placeholder="Search discussions..."
                        class="pl-9 bg-slate-50 border-slate-200 focus:bg-white transition-all h-10"
                    />
                </div>

                <div class="relative w-full md:w-56">
                    <ArrowUpDown class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
                    <select
                        v-model="sort"
                        class="w-full h-10 pl-9 pr-4 rounded-md border border-slate-200 bg-slate-50 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="replies">Most Replies</option>
                    </select>
                </div>
            </div>

            <div class="space-y-3">
                <div
                    v-for="post in posts.data"
                    :key="post.id"
                    class="group bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition-all cursor-pointer relative"
                >
                    <Link :href="route('forum.show', post.id)" class="absolute inset-0" />

                    <div class="flex items-start gap-4">
                        <div class="shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100 uppercase">
                                {{ post.user?.name?.charAt(0) || 'U' }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors wrap-break-word">
                                {{ post.title }}
                            </h3>

                            <div class="mt-2 flex items-center gap-3 text-xs text-slate-500 md:hidden">
                                <span class="flex items-center gap-1">
                                    <UserIcon class="w-3 h-3" /> {{ post.user?.name }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <MessageCircle class="w-3 h-3" /> {{ post.replies_count }}
                                </span>
                            </div>

                            <div class="mt-2 hidden md:flex items-center gap-4 text-sm text-slate-500">
                                <span class="flex items-center gap-1.5 hover:text-slate-700">
                                    <UserIcon class="w-3.5 h-3.5" />
                                    <span class="font-medium">{{ post.user?.name || 'Unknown' }}</span>
                                </span>
                                <span class="flex items-center gap-1.5" :title="post.created_at">
                                    <Clock class="w-3.5 h-3.5" />
                                    {{ formatDate(post.created_at) }}
                                </span>
                            </div>
                        </div>

                        <div class="hidden md:flex flex-col items-end justify-center min-w-20">
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 group-hover:bg-blue-50 group-hover:border-blue-100 transition-colors">
                                <MessageCircle class="w-4 h-4 text-slate-400 group-hover:text-blue-500" />
                                <span class="font-bold text-slate-700 group-hover:text-blue-700">{{ post.replies_count }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 uppercase tracking-wide">Replies</span>
                        </div>
                    </div>
                </div>

                <div v-if="!posts.data || posts.data.length === 0" class="text-center py-16 bg-white rounded-xl border border-slate-200 border-dashed">
                    <div class="bg-slate-50 h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <MessageSquare class="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">No discussions yet</h3>
                    <p class="text-slate-500 max-w-sm mx-auto mt-1 mb-6">
                        Be the first to start a conversation in the community!
                    </p>
                    <Link :href="route('forum.create')">
                        <Button variant="outline" class="border-blue-200 text-blue-600 hover:bg-blue-50">
                            Start a Discussion
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="posts.links && posts.links.length > 3" class="flex justify-center pt-4 pb-8">
                <div class="flex gap-1 flex-wrap justify-center">
                    <template v-for="(link, key) in posts.links" :key="key">
                        <div
                            v-if="!link.url"
                            class="px-3 py-1 text-sm text-slate-400 border border-slate-100 rounded bg-white"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            class="px-3 py-1 text-sm border rounded transition-colors shadow-sm"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                            :href="link.url"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        </div>
    </AppSidebarLayout>
</template>
