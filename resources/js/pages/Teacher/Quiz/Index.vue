<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
// FIX: Use SidebarLayout to match other pages
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Search,
    Plus,
    BrainCircuit,
    Pencil,
    Trash2,
    Eye,
    Users,
    FileQuestion
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    quizzes: Array<{
        id: number;
        title: string;
        description: string;
        attempts_count: number;
        difficulty: string;
        created_at: string;
    }>;
}>();

const searchQuery = ref('');

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Quiz Management', href: route('teacher.quiz.index') },
];

// Client-side Filter Logic (Kept from your original code)
const filteredQuizzes = computed(() => {
    let data = props.quizzes;
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter((q) =>
            q.title.toLowerCase().includes(query) ||
            q.description.toLowerCase().includes(query)
        );
    }
    return data;
});

const deleteQuiz = (id: number) => {
    if (confirm('Are you sure? This will delete all student results for this quiz too.')) {
        router.delete(route('teacher.quiz.destroy', id));
    }
};

// Helper: Modern Difficulty Colors
const getDifficultyColor = (difficulty: string) => {
    const d = (difficulty || '').toLowerCase();
    if (d === 'easy') return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    if (d === 'medium') return 'bg-amber-100 text-amber-700 border-amber-200';
    if (d === 'hard') return 'bg-rose-100 text-rose-700 border-rose-200';
    return 'bg-slate-100 text-slate-700 border-slate-200';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Quiz Management" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        <BrainCircuit class="w-7 h-7 text-teal-600" />
                        Quiz Management
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">
                        Create, edit, and manage assessments for your students.
                    </p>
                </div>

                <Link :href="route('teacher.quiz.create')">
                    <Button class="w-full md:w-auto bg-teal-600 hover:bg-teal-700 text-white shadow-sm gap-2 font-medium">
                        <Plus class="w-4 h-4" /> Create New Quiz
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="relative w-full">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search quizzes by title..."
                        class="pl-9 bg-slate-50 border-slate-200 focus:bg-white focus:ring-teal-500 focus:border-teal-500 transition-all w-full"
                    />
                </div>
            </div>

            <div class="grid gap-4">

                <div v-if="filteredQuizzes.length === 0" class="bg-white p-12 rounded-xl border border-slate-200 text-center shadow-sm">
                    <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <FileQuestion class="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">No quizzes found</h3>
                    <p class="text-slate-500 mt-1 text-sm">Get started by creating your first quiz.</p>
                </div>

                <div
                    v-for="quiz in filteredQuizzes"
                    :key="quiz.id"
                    class="group bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-teal-300 transition-all flex flex-col md:flex-row gap-5 items-start md:items-center"
                >
                    <div class="shrink-0 hidden md:block">
                        <div class="h-12 w-12 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-100">
                            <BrainCircuit class="w-6 h-6" />
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1 flex-wrap">
                            <h3 class="text-lg font-bold text-slate-900 truncate group-hover:text-teal-700 transition-colors">
                                {{ quiz.title }}
                            </h3>
                            <Badge variant="outline" :class="getDifficultyColor(quiz.difficulty)" class="uppercase text-[10px] tracking-wider font-bold">
                                {{ quiz.difficulty }}
                            </Badge>
                        </div>

                        <p class="text-slate-500 text-sm line-clamp-1 mb-3">
                            {{ quiz.description || 'No description provided.' }}
                        </p>

                        <div class="flex items-center gap-4 text-xs font-medium text-slate-400">
                            <span class="flex items-center gap-1.5 text-slate-600">
                                <Users class="w-3.5 h-3.5 text-teal-500" />
                                {{ quiz.attempts_count }} Student Attempts
                            </span>
                            <span class="text-slate-300">|</span>
                            <span>Created {{ formatDate(quiz.created_at) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end md:self-center w-full md:w-auto justify-end border-t md:border-t-0 border-slate-100 pt-3 md:pt-0 mt-2 md:mt-0">
                        <Link :href="route('teacher.quiz.results', quiz.id)">
                            <Button size="sm" class="bg-blue-600 hover:bg-blue-700 text-white border-none gap-2 shadow-sm">
                                <Eye class="w-3.5 h-3.5" /> Results
                            </Button>
                        </Link>

                        <div class="flex items-center gap-1 border-l border-slate-200 pl-2 ml-2">
                            <Link :href="route('teacher.quiz.edit', quiz.id)">
                                <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-md">
                                    <Pencil class="w-4 h-4" />
                                </Button>
                            </Link>

                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md"
                                @click="deleteQuiz(quiz.id)"
                            >
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppSidebarLayout>
</template>
