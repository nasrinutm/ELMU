<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { PlusCircle, Trash2, Users, Eye, Pencil } from 'lucide-vue-next';
import { route } from 'ziggy-js';

defineProps<{
    quizzes: Array<{
        id: number;
        title: string;
        description: string;
        attempts_count: number;
        difficulty: string;
    }>;
}>();

const deleteQuiz = (id: number) => {
    if (confirm('Are you sure? This will delete all student results for this quiz too.')) {
        router.delete(route('teacher.quiz.destroy', id));
    }
};
</script>

<template>
    <Head title="Teacher Dashboard" />

    <AppLayout>
        <div class="flex flex-col gap-6 p-6 bg-[#002B5C] min-h-screen text-white">
            
            <div class="flex justify-between items-center border-b border-[#FFD700]/30 pb-6">
                <div>
                    <h1 class="text-3xl font-bold text-[#FFD700]">Teacher Dashboard</h1>
                    <p class="text-gray-200">Manage quizzes and view student performance.</p>
                </div>
                <Link :href="route('teacher.quiz.create')">
                    <Button class="bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200] font-bold">
                        <PlusCircle class="mr-2 h-5 w-5" /> Create New Quiz
                    </Button>
                </Link>
            </div>

            <div class="grid gap-6">
                <Card v-for="quiz in quizzes" :key="quiz.id" class="border-none bg-white text-[#002B5C]">
                    <div class="flex flex-col md:flex-row items-center justify-between p-6">
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-xl font-bold">{{ quiz.title }}</h3>
                                <Badge :class="quiz.difficulty === 'Hard' ? 'bg-red-600 text-white hover:bg-red-700' : (quiz.difficulty === 'Medium' ? 'bg-yellow-600 text-white hover:bg-yellow-700' : 'bg-green-600 text-white hover:bg-green-700')">
                                    {{ quiz.difficulty }}
                                </Badge>
                            </div>
                            <p class="text-gray-900 font-medium">{{ quiz.description }}</p>
                            <div class="flex items-center gap-2 mt-3 text-sm font-bold text-[#002B5C]/80">
                                <Users class="h-4 w-4" />
                                {{ quiz.attempts_count }} Student Attempts
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mt-4 md:mt-0">
                            
                            <Link :href="route('teacher.quiz.edit', quiz.id)">
                                <Button class="bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200] font-bold border border-[#cfaa00]">
                                    <Pencil class="mr-2 h-4 w-4" /> Edit
                                </Button>
                            </Link>

                            <Link :href="route('teacher.quiz.results', quiz.id)">
                                <Button class="bg-[#002B5C] text-white hover:bg-[#004080] font-bold">
                                    <Eye class="mr-2 h-4 w-4" /> Results
                                </Button>
                            </Link>

                            <Button @click="deleteQuiz(quiz.id)" variant="destructive" class="bg-red-600 hover:bg-red-700 font-bold">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </Card>

                <div v-if="quizzes.length === 0" class="text-center py-12 bg-[#004080] rounded-lg border-2 border-dashed border-[#FFD700]/30">
                    <p class="text-[#FFD700] text-lg">No quizzes found. Create one to get started!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>