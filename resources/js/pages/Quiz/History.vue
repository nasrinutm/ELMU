<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Calendar, Trophy, BarChart3, RotateCcw, Gamepad2 } from 'lucide-vue-next';
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
    <Head :title="`History - ${quizTitle}`" />

    <AppLayout>
        <div class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm py-4 px-6 flex justify-between items-center rounded-b-3xl mx-2 mt-2">
            <div class="flex items-center gap-4">
                <Link :href="route('quiz.index')" class="text-slate-400 hover:text-teal-600 transition-colors">
                    <ArrowLeft class="h-6 w-6" />
                </Link>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 truncate">Quiz History</h2>
                    <p class="text-xs text-slate-500 font-medium">{{ quizTitle }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 px-5 py-2 rounded-full font-mono text-xl font-bold border border-teal-200 bg-teal-50 text-teal-700">
                <BarChart3 class="h-5 w-5" /> {{ stats.attempts }}
            </div>
        </div>

        <div class="flex flex-col gap-6 p-6 bg-slate-50 min-h-screen items-center">
            
            <div class="w-full max-w-4xl space-y-8">
                
                <Card class="bg-white text-slate-900 border border-slate-200 shadow-xl overflow-hidden rounded-[2rem]">
                    <div class="p-8">
                        <h3 class="font-bold text-slate-400 uppercase tracking-widest text-xs mb-6 text-center">Performance Overview</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-slate-50 border border-slate-100 rounded-3xl p-6 flex flex-col items-center justify-center gap-1">
                                <span class="text-4xl font-black text-slate-900">{{ stats.attempts }}</span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Attempts</span>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-3xl p-6 flex flex-col items-center justify-center gap-1">
                                <span class="text-4xl font-black text-slate-900">{{ stats.average }}%</span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Average</span>
                            </div>
                            <div class="bg-teal-50 border border-teal-100 rounded-3xl p-6 flex flex-col items-center justify-center gap-1">
                                <span class="text-4xl font-black text-teal-600">{{ stats.best }}%</span>
                                <span class="text-[10px] font-bold text-teal-600 uppercase tracking-widest">Best Score</span>
                            </div>
                        </div>
                    </div>
                </Card>

                <div class="space-y-4">
                    <h3 class="text-2xl font-black text-slate-900 ml-4 flex items-center gap-2">
                        <Gamepad2 class="w-6 h-6 text-teal-600" /> Past Attempts
                    </h3>
                    
                    <div v-if="attempts.length === 0" class="text-center py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200">
                        <Trophy class="h-16 w-16 text-slate-200 mx-auto mb-4" />
                        <p class="text-slate-500 font-medium text-lg">No attempts found yet.</p>
                    </div>

                    <div v-for="(attempt, index) in attempts" :key="attempt.id" 
                        class="bg-white rounded-[2rem] p-6 flex flex-col md:flex-row items-center justify-between gap-6 shadow-md border border-slate-100 transition-transform hover:scale-[1.01] hover:border-teal-200"
                    >
                        <div class="flex items-center gap-6 w-full md:w-auto">
                            <div class="h-20 w-20 rounded-[1.5rem] flex flex-col items-center justify-center shrink-0"
                                :class="attempt.percentage >= 50 ? 'bg-emerald-50 border border-emerald-100' : 'bg-rose-50 border border-rose-100'">
                                <span class="text-2xl font-black" :class="attempt.percentage >= 50 ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ Math.round(attempt.percentage) }}%
                                </span>
                            </div>
                            
                            <div>
                                <div class="font-black text-slate-900 text-xl uppercase tracking-tighter">Attempt #{{ attempts.length - index }}</div>
                                <div class="flex items-center gap-2 text-sm text-slate-500 font-medium mt-1">
                                    <Calendar class="h-4 w-4 text-teal-500" /> {{ attempt.date }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-8 w-full md:w-auto justify-between md:justify-end bg-slate-50 md:bg-transparent p-5 md:p-0 rounded-2xl border border-slate-100 md:border-none">
                            <div class="text-center">
                                <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1">Accuracy</div>
                                <div class="font-bold text-slate-900 text-xl">{{ attempt.score }} <span class="text-slate-400 text-sm">/ {{ attempt.total }}</span></div>
                            </div>
                            
                            <div class="text-right">
                                <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1">Status</div>
                                <div class="font-black text-xl uppercase tracking-tighter" :class="attempt.percentage >= 50 ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ attempt.percentage >= 50 ? 'Passed' : 'Failed' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center pt-10">
                    <Link :href="route('quiz.show', attempts[0]?.id || 1)">
                        <Button class="bg-teal-600 hover:bg-teal-700 text-white font-black px-16 py-8 rounded-3xl text-xl shadow-xl transition-all active:scale-95 uppercase tracking-widest">
                            Try Again <RotateCcw class="ml-2 h-6 w-6" />
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>