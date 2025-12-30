<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Trophy, Calendar, Target, BarChart3 } from 'lucide-vue-next';
import { route } from 'ziggy-js';

defineProps<{
    quizTitle: string;
    stats: {
        attempts: number;
        average: number;
        best: number;
    };
    attempts: Array<{
        id: number;
        score: number;
        total: number;
        date: string;
        percentage: number;
    }>;
}>();
</script>

<template>
    <Head title="Quiz History" />

    <AppLayout>
        <div class="flex flex-col gap-6 p-6 bg-[#002B5C] min-h-screen text-white">
            
            <div class="flex items-center justify-between">
                <Link :href="route('quiz.index')" class="flex items-center gap-2 text-[#FFD700] hover:underline font-medium">
                    <ArrowLeft class="h-4 w-4" /> Back to Topics
                </Link>
            </div>

            <div class="flex flex-col gap-2">
                <h1 class="text-3xl font-bold text-[#FFD700]">Quiz History</h1>
                <p class="text-gray-300">Review your past attempts for <span class="font-semibold text-white">{{ quizTitle }}</span></p>
            </div>

            <Card class="bg-white text-[#002B5C] border-none shadow-xl p-6">
                <h3 class="font-bold text-lg mb-6 border-b pb-2 opacity-80">{{ quizTitle }} Overview</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center justify-center gap-2">
                        <span class="text-4xl font-black text-[#002B5C]">{{ stats.attempts }}</span>
                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Attempts</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center justify-center gap-2">
                        <span class="text-4xl font-black text-[#002B5C]">{{ stats.average }}%</span>
                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Average Score</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center justify-center gap-2">
                        <span class="text-4xl font-black text-[#FFD700] drop-shadow-sm">{{ stats.best }}%</span>
                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Best Score</span>
                    </div>
                </div>
            </Card>

            <div class="space-y-4">
                <h3 class="text-xl font-bold text-white mt-4">Past Attempts</h3>
                
                <div v-if="attempts.length === 0" class="text-center py-10 text-gray-400 border-2 border-dashed border-[#004080] rounded-lg">
                    No attempts yet. Start a quiz to see your history!
                </div>

                <div v-for="(attempt, index) in attempts" :key="attempt.id" 
                    class="bg-white rounded-lg p-4 flex flex-col md:flex-row items-center justify-between gap-4 shadow-md transition-transform hover:scale-[1.01]"
                >
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <div class="h-16 w-16 rounded-lg flex items-center justify-center font-bold text-xl"
                            :class="attempt.percentage >= 50 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                            {{ attempt.percentage }}%
                        </div>
                        
                        <div>
                            <div class="font-bold text-[#002B5C] text-lg">Attempt #{{ attempts.length - index }}</div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <Calendar class="h-3 w-3" /> {{ attempt.date }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end bg-gray-50 md:bg-transparent p-3 md:p-0 rounded-lg">
                        <div class="text-right">
                            <div class="text-xs text-gray-400 uppercase font-bold">Correct</div>
                            <div class="font-bold text-[#002B5C]">{{ attempt.score }} / {{ attempt.total }}</div>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-xs text-gray-400 uppercase font-bold">Status</div>
                            <div class="font-bold" :class="attempt.percentage >= 50 ? 'text-green-600' : 'text-red-600'">
                                {{ attempt.percentage >= 50 ? 'Passed' : 'Failed' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>