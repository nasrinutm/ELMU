<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'; 
import { Button } from '@/components/ui/button'; 
import { Plus, Pencil, Trash2, Eye, Search, Gamepad2, Users } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';

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

// Filter Logic
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

// Helper for Difficulty Color
const getDifficultyColor = (difficulty: string) => {
    switch(difficulty) {
        case 'Easy': return 'bg-green-900/50 text-green-200 border-green-700';
        case 'Medium': return 'bg-yellow-900/50 text-yellow-200 border-yellow-700';
        case 'Hard': return 'bg-red-900/50 text-red-200 border-red-700';
        default: return 'bg-gray-700 text-gray-300 border-gray-600';
    }
};
</script>

<template>
    <Head title="Teacher Dashboard" />

    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Teacher Dashboard', href: route('teacher.quiz.index') }
    ]">
        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                
                <div class="bg-[#003366] overflow-hidden shadow-xl sm:rounded-lg border border-[#004080]">
                    
                    <div class="p-6 border-b border-[#004080] flex flex-col sm:flex-row justify-between items-center gap-4 bg-[#002244]">
                        <h1 class="text-2xl font-bold text-[#FFD700]">Teacher Dashboard</h1>
                        
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <div class="relative w-full sm:w-64">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-blue-300" />
                                <input 
                                    v-model="searchQuery"
                                    type="text" 
                                    placeholder="Search quizzes..." 
                                    class="w-full pl-9 pr-4 py-2 bg-[#003366] border border-blue-400 text-white rounded-md text-sm placeholder-blue-300/50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                />
                            </div>

                            <Link :href="route('teacher.quiz.create')">
                                <Button class="bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md whitespace-nowrap">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Create New Quiz
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <div class="p-0">
                        <div v-if="filteredQuizzes.length > 0" class="divide-y divide-[#004080]">
                            
                            <div v-for="quiz in filteredQuizzes" :key="quiz.id" 
                                class="p-6 hover:bg-[#003366]/50 transition duration-150 ease-in-out flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 group">
                                
                                <div class="flex items-start gap-4">
                                    <div class="p-3 rounded-lg bg-[#002244] border border-[#004080] group-hover:border-blue-500/50 transition">
                                        <Gamepad2 class="w-6 h-6 text-blue-300" />
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-3">
                                            <h3 class="font-bold text-lg text-white group-hover:text-[#FFD700] transition cursor-default">
                                                {{ quiz.title }}
                                            </h3>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded border uppercase tracking-wider" :class="getDifficultyColor(quiz.difficulty)">
                                                {{ quiz.difficulty }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-sm text-gray-300 mb-2 line-clamp-2 mt-1">{{ quiz.description }}</p>
                                        
                                        <div class="flex items-center gap-4 text-xs text-gray-400">
                                            <span class="flex items-center text-blue-300">
                                                <Users class="w-3 h-3 mr-1" />
                                                {{ quiz.attempts_count }} Student Attempts
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3 self-end sm:self-center">
                                    
                                    <Link :href="route('teacher.quiz.results', quiz.id)" 
                                        class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-md text-sm font-semibold shadow hover:shadow-lg transition flex items-center border border-blue-500/50" 
                                        title="View Results">
                                        <Eye class="w-4 h-4 mr-2" />
                                        Results
                                    </Link>

                                    <div class="flex items-center gap-1 pl-3 border-l border-[#004080] ml-2">
                                        <Link :href="route('teacher.quiz.edit', quiz.id)" class="p-2 text-gray-400 hover:text-[#FFD700] hover:bg-[#002244] rounded-md transition" title="Edit Quiz">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                        <button @click="deleteQuiz(quiz.id)" class="p-2 text-gray-400 hover:text-red-400 hover:bg-[#002244] rounded-md transition" title="Delete Quiz">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="bg-[#002244] p-4 rounded-full mb-4">
                                <Search class="w-8 h-8 text-gray-500" />
                            </div>
                            <h3 class="text-lg font-medium text-white">No quizzes found</h3>
                            <p class="text-gray-400 mt-1">Get started by creating your first quiz.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>