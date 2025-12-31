<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
// Use the sidebar layout as seen in the navigation examples
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
    FileQuestion,
    ChevronRight
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

const getDifficultyColor = (difficulty: string) => {
    const d = (difficulty || '').toLowerCase();
    if (d === 'easy') return 'bg-emerald-50 text-emerald-600 border-emerald-100';
    if (d === 'medium') return 'bg-amber-50 text-amber-600 border-amber-100';
    if (d === 'hard') return 'bg-rose-50 text-rose-600 border-rose-100';
    return 'bg-slate-50 text-slate-600 border-slate-100';
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
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                        <BrainCircuit class="w-8 h-8 text-teal-600" />
                        Quiz Management
                    </h1>
                    <p class="text-slate-500 mt-1 font-medium">Create and oversee assessments for your classroom.</p>
                </div>

                <Link :href="route('teacher.quiz.create')">
                    <Button class="w-full md:w-auto bg-teal-600 hover:bg-teal-700 text-white shadow-lg shadow-teal-600/20 gap-2 font-bold py-6 px-6 rounded-2xl transition-all active:scale-95">
                        <Plus class="w-5 h-5" /> Create New Quiz
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-[1.5rem] border border-slate-200 shadow-sm">
                <div class="relative w-full">
                    <Search class="absolute left-4 top-3 h-4 w-4 text-slate-400" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search quizzes by title..."
                        class="pl-11 h-12 bg-slate-50 border-slate-200 rounded-xl focus:bg-white focus:ring-teal-500 focus:border-teal-500 transition-all w-full"
                    />
                </div>
            </div>

            <div class="grid gap-4">
                <div v-if="filteredQuizzes.length === 0" class="bg-white p-20 rounded-[2rem] border-2 border-dashed border-slate-200 text-center shadow-sm">
                    <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <FileQuestion class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">No quizzes available</h3>
                    <p class="text-slate-500 mt-1">Start by clicking the "Create New Quiz" button.</p>
                </div>

                <div
                    v-for="quiz in filteredQuizzes"
                    :key="quiz.id"
                    class="group bg-white rounded-[2rem] border border-slate-100 p-6 shadow-sm hover:shadow-md hover:border-teal-300 transition-all flex flex-col md:flex-row gap-6 items-start md:items-center"
                >
                    <div class="shrink-0 hidden md:block">
                        <div class="h-16 w-16 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-100 shadow-sm">
                            <BrainCircuit class="w-8 h-8" />
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2 flex-wrap">
                            <h3 class="text-xl font-black text-slate-900 truncate group-hover:text-teal-700 transition-colors">
                                {{ quiz.title }}
                            </h3>
                            <Badge variant="outline" :class="getDifficultyColor(quiz.difficulty)" class="uppercase text-[10px] tracking-widest font-black px-3 py-1 rounded-lg">
                                {{ quiz.difficulty }}
                            </Badge>
                        </div>

                        <p class="text-slate-500 text-sm line-clamp-1 mb-4 font-medium">
                            {{ quiz.description || 'No description provided.' }}
                        </p>

                        <div class="flex items-center gap-5 text-xs font-bold text-slate-400">
                            <span class="flex items-center gap-1.5 text-teal-700 bg-teal-50 px-3 py-1.5 rounded-full border border-teal-100">
                                <Users class="w-4 h-4" />
                                {{ quiz.attempts_count }} Student Attempts
                            </span>
                            <span class="flex items-center gap-1.5">
                                <Clock class="w-4 h-4 text-slate-300" />
                                Created {{ formatDate(quiz.created_at) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 self-stretch md:self-center justify-end md:border-l border-slate-100 md:pl-6">
                        <Link :href="route('teacher.quiz.results', quiz.id)">
                            <Button variant="outline" class="border-slate-200 text-slate-600 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 rounded-xl px-5 font-bold gap-2">
                                <Eye class="w-4 h-4" /> Results
                            </Button>
                        </Link>

                        <div class="flex items-center gap-1">
                            <Link :href="route('teacher.quiz.edit', quiz.id)">
                                <Button variant="ghost" size="icon" class="h-10 w-10 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-xl">
                                    <Pencil class="w-5 h-5" />
                                </Button>
                            </Link>

                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-10 w-10 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl"
                                @click="deleteQuiz(quiz.id)"
                            >
                                <Trash2 class="w-5 h-5" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>