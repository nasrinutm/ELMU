<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Clock, HelpCircle, Lock, Search, Gamepad2, 
    ChevronRight, BarChart3 
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

interface Quiz {
    id: number;
    title: string;
    description: string;
    questions_count: number;
    time_limit: string;
    difficulty: string;
    attempts_taken: number;
    attempts_max: number;
    attempts_left: number;
    is_locked: boolean;
}

const props = defineProps<{
    quizzes: Quiz[];
    filters: {
        search?: string;
        difficulty?: string;
    };
}>();

const search = ref(props.filters.search || '');
const difficulty = ref(props.filters.difficulty || 'All');

const updateList = debounce(() => {
    router.get(
        route('quiz.index'),
        { search: search.value, difficulty: difficulty.value },
        { preserveState: true, replace: true }
    );
}, 300);

watch([search, difficulty], () => updateList());

const getDifficultyClass = (level: string) => {
    const l = level.toLowerCase();
    if (l === 'easy') return 'bg-emerald-50 text-emerald-600 border-emerald-100';
    if (l === 'medium') return 'bg-amber-50 text-amber-600 border-amber-100';
    return 'bg-rose-50 text-rose-600 border-rose-100';
};
</script>

<template>
    <Head title="Available Quizzes" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        <Gamepad2 class="w-7 h-7 text-teal-600" />
                        Available Quizzes
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">Test your knowledge and track your progress.</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input 
                        v-model="search" 
                        placeholder="Search quizzes..." 
                        class="pl-9 w-full h-10 rounded-md bg-slate-50 border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all"
                    />
                </div>
                <select 
                    v-model="difficulty" 
                    class="h-10 px-3 rounded-md bg-slate-50 border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                >
                    <option value="All">All Levels</option>
                    <option value="Easy">Easy</option>
                    <option value="Medium">Medium</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="quiz in quizzes" :key="quiz.id" 
                    class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-teal-300 transition-all overflow-hidden flex flex-col">
                    
                    <div class="p-6 space-y-4 flex-1">
                        <div class="flex justify-between items-start">
                            <div class="h-12 w-12 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600">
                                <Gamepad2 class="w-6 h-6" />
                            </div>
                            <Badge variant="outline" :class="['font-bold uppercase text-[10px]', getDifficultyClass(quiz.difficulty)]">
                                {{ quiz.difficulty }}
                            </Badge>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-teal-700 transition-colors">
                                {{ quiz.title }}
                            </h3>
                            <p class="text-slate-500 text-sm line-clamp-2 mt-1">{{ quiz.description }}</p>
                        </div>

                        <div class="flex items-center gap-4 text-xs font-semibold text-slate-400">
                            <span class="flex items-center gap-1">
                                <HelpCircle class="w-3.5 h-3.5 text-teal-500" /> {{ quiz.questions_count }} Qs
                            </span>
                            <span class="flex items-center gap-1">
                                <Clock class="w-3.5 h-3.5 text-teal-500" /> {{ quiz.time_limit }}
                            </span>
                        </div>

                        <div class="pt-2">
                            <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mb-1.5">
                                <span class="text-slate-400">Attempts Used</span>
                                <span class="text-slate-900">{{ quiz.attempts_taken }} / {{ quiz.attempts_max }}</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-500 transition-all duration-500" 
                                    :class="{ 'bg-rose-500': quiz.is_locked }"
                                    :style="{ width: `${(quiz.attempts_taken / quiz.attempts_max) * 100}%` }">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-2">
                        <div v-if="quiz.is_locked" class="w-full bg-slate-100 text-slate-400 font-bold py-2.5 rounded-xl flex items-center justify-center gap-2 text-sm cursor-not-allowed">
                            <Lock class="w-4 h-4" /> Max Attempts
                        </div>

                        <Link v-else :href="route('quiz.show', quiz.id)">
                            <Button class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-5 rounded-xl shadow-sm gap-2">
                                Start Quiz <ChevronRight class="w-4 h-4" />
                            </Button>
                        </Link>

                        <div v-if="quiz.attempts_taken > 0" class="mt-4 text-center">
                            <Link :href="route('quiz.history', quiz.id)" 
                                class="text-xs font-bold text-slate-400 hover:text-teal-600 flex items-center justify-center gap-1.5 transition-colors">
                                <BarChart3 class="w-3.5 h-3.5" /> View Past Results
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>