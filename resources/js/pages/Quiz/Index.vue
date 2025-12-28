<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Clock, HelpCircle, Lock, Search, Filter } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { ref, watch } from 'vue';

// 1. Receive data & filters from Controller
const props = defineProps<{
    quizzes: Array<{
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
    }>;
    filters: {
        search?: string;
        difficulty?: string;
    };
}>();

// 2. Setup Reactive Variables for Search
const search = ref(props.filters.search || '');
const difficulty = ref(props.filters.difficulty || 'All');
let timeout: any = null;

// 3. Auto-Reload when user types or selects
const updateList = () => {
    router.get(
        route('quiz.index'),
        { search: search.value, difficulty: difficulty.value },
        { preserveState: true, replace: true }
    );
};

watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(updateList, 300); // 300ms delay to prevent lag while typing
});

watch(difficulty, () => {
    updateList(); // Reload instantly when dropdown changes
});
</script>

<template>
    <Head title="Available Quizzes" />

    <AppLayout>
        <div class="p-6 bg-[#002B5C] min-h-screen text-white">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 border-b border-[#FFD700]/30 pb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-[#FFD700]">Available Quizzes</h1>
                        <p class="text-gray-300 mt-1">Select a quiz to start or view your history.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <div class="relative w-full sm:w-64">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
                            <input 
                                v-model="search" 
                                type="text"
                                placeholder="Search quizzes..." 
                                class="w-full pl-9 pr-4 py-2 bg-white text-black rounded-md focus:outline-none focus:ring-2 focus:ring-[#FFD700]"
                            />
                        </div>

                        <div class="w-full sm:w-40">
                             <select 
                                v-model="difficulty" 
                                class="w-full py-2 px-3 rounded-md bg-white text-black focus:outline-none focus:ring-2 focus:ring-[#FFD700]"
                            >
                                <option value="All">All Levels</option>
                                <option value="Easy">Easy</option>
                                <option value="Medium">Medium</option>
                                <option value="Hard">Hard</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="quiz in quizzes" :key="quiz.id" 
                        class="bg-white text-[#002B5C] rounded-lg shadow-lg overflow-hidden flex flex-col h-full relative hover:shadow-2xl transition-all duration-300">
                        
                        <div class="absolute top-0 right-0 p-4">
                            <Badge :class="{
                                'bg-green-100 text-green-700': quiz.difficulty === 'Easy',
                                'bg-yellow-100 text-yellow-700': quiz.difficulty === 'Medium',
                                'bg-red-100 text-red-700': quiz.difficulty === 'Hard'
                            }">
                                {{ quiz.difficulty }}
                            </Badge>
                        </div>

                        <div class="p-6 flex-grow space-y-4">
                            <div>
                                <h3 class="text-xl font-bold mb-2 line-clamp-1" :title="quiz.title">
                                    {{ quiz.title }}
                                </h3>
                                <p class="text-gray-500 text-sm line-clamp-2" :title="quiz.description">
                                    {{ quiz.description }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4 text-sm text-gray-600 font-medium">
                                <span class="flex items-center gap-1"><HelpCircle class="w-4 h-4 text-[#FFD700]" /> {{ quiz.questions_count }} Qs</span>
                                <span class="flex items-center gap-1"><Clock class="w-4 h-4 text-[#FFD700]" /> {{ quiz.time_limit }}</span>
                            </div>

                            <div class="pt-2">
                                <div class="flex justify-between text-xs font-bold uppercase mb-1">
                                    <span :class="quiz.is_locked ? 'text-red-600' : 'text-gray-500'">
                                        {{ quiz.is_locked ? 'Locked' : 'Attempts Used' }}
                                    </span>
                                    <span class="text-[#002B5C]">
                                        {{ quiz.attempts_taken }} / {{ quiz.attempts_max }}
                                    </span>
                                </div>
                                <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-500" 
                                        :class="quiz.is_locked ? 'bg-red-500' : 'bg-[#002B5C]'"
                                        :style="{ width: `${(quiz.attempts_taken / quiz.attempts_max) * 100}%` }">
                                    </div>
                                </div>
                                
                                <div v-if="quiz.attempts_max > 3" class="mt-2 text-center bg-green-50 text-green-700 text-xs font-bold py-1 rounded">
                                    +{{ quiz.attempts_max - 3 }} Extra Attempt Granted!
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 border-t border-gray-100 mt-auto">
                            <button v-if="quiz.is_locked" disabled class="w-full bg-gray-300 text-gray-500 font-bold py-2 rounded flex items-center justify-center gap-2 cursor-not-allowed">
                                <Lock class="w-4 h-4" /> Max Attempts Reached
                            </button>

                            <Link v-else :href="route('quiz.show', quiz.id)" class="block w-full">
                                <button class="w-full bg-[#002B5C] hover:bg-[#004080] text-white font-bold py-2 rounded shadow transition-all hover:shadow-lg">
                                    Start Quiz 
                                    <span v-if="quiz.attempts_left > 0" class="ml-1 opacity-80 font-normal">
                                        ({{ quiz.attempts_left }} left)
                                    </span>
                                </button>
                            </Link>

                            <div v-if="quiz.attempts_taken > 0" class="mt-3 text-center">
                                <Link :href="route('quiz.history', quiz.id)" 
                                    class="text-sm text-gray-500 hover:text-[#002B5C] underline decoration-dotted transition-colors">
                                    View Past Results
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="quizzes.length === 0" class="text-center py-20 bg-[#004080]/30 rounded-xl border-2 border-dashed border-[#FFD700]/20">
                    <Filter class="w-12 h-12 text-gray-400 mx-auto mb-3 opacity-50" />
                    <p class="text-xl text-gray-300 font-medium">No quizzes found.</p>
                    <p class="text-gray-400 text-sm mt-1">Try changing your search or filter.</p>
                    <button @click="search = ''; difficulty = 'All'" class="mt-4 text-[#FFD700] underline hover:text-white transition-colors cursor-pointer">
                        Clear Filters
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>