<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    ArrowLeft, Calendar, Trophy, BarChart3,
    RotateCcw, Gamepad2
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const props = defineProps<{
    quizTitle: string;
    quizId: number;
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

const page = usePage();

// Role-based theme synchronization (Indigo for student)
const userRole = computed(() => page.props.auth.user?.roles[0] || 'student');

const breadcrumbs = [
    { title: 'Quizzes', href: route('quizzes.index') },
    { title: 'History', href: '#' }
];
</script>

<template>
    <Head :title="`Evaluation History - ${quizTitle}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div :class="`theme-${userRole}`" class="min-h-screen bg-slate-50 flex flex-col items-center p-6 font-sans font-normal antialiased">

            <div class="sticky top-0 z-50 w-full max-w-5xl bg-white border border-slate-200 shadow-sm py-4 px-8 flex justify-between items-center rounded-none mb-8">
                <div class="flex items-center gap-4">
                    <Link :href="route('quizzes.index')" class="text-slate-400 hover:text-action transition-colors">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 uppercase tracking-tight leading-none">Evaluation History</h2>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest mt-1 italic">{{ quizTitle }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-4 py-1.5 rounded-none font-mono text-lg font-medium border border-action/20 bg-action/5 text-action tabular-nums">
                    <BarChart3 class="h-4 w-4 opacity-50" /> {{ stats.attempts }}
                </div>
            </div>

            <div class="w-full max-w-5xl space-y-8">

                <Card class="bg-white text-slate-900 border border-slate-200 shadow-sm overflow-hidden rounded-none">
                    <div class="p-8 relative">
                        <h3 class="font-medium text-slate-400 uppercase tracking-widest text-[10px] mb-8 text-center border-b border-slate-50 pb-4">
                            Performance Metrics
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col items-center group">
                                <span class="text-3xl font-semibold text-slate-900 tabular-nums tracking-tighter">{{ stats.attempts }}</span>
                                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mt-2">Total Sessions</span>
                            </div>

                            <div class="flex flex-col items-center group">
                                <span class="text-3xl font-semibold text-slate-900 tabular-nums tracking-tighter">{{ stats.average }}%</span>
                                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mt-2">Average Accuracy</span>
                            </div>

                            <div class="flex flex-col items-center group border-action/10 bg-action/5 p-4 md:p-0 md:bg-transparent">
                                <span class="text-3xl font-semibold text-action tabular-nums tracking-tighter">{{ stats.best }}%</span>
                                <span class="text-[10px] font-medium text-action uppercase tracking-widest mt-2">Personal Best</span>
                            </div>
                        </div>
                    </div>
                </Card>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-slate-900 ml-1 flex items-center gap-3 uppercase tracking-tight">
                        <Gamepad2 class="w-5 h-5 text-action" />
                        Past Submissions
                    </h3>

                    <div v-if="attempts.length === 0" class="text-center py-20 bg-white rounded-none border-2 border-dashed border-slate-200">
                        <Trophy class="h-16 w-16 text-slate-200 mx-auto mb-4" />
                        <p class="text-slate-400 font-medium uppercase tracking-widest text-[10px]">No records identified.</p>
                    </div>

                    <div v-for="(attempt, index) in attempts" :key="attempt.id"
                        class="bg-white rounded-none p-6 flex flex-col md:flex-row items-center justify-between gap-8 shadow-sm border border-slate-200 transition-all hover:border-action/40 group"
                    >
                        <div class="flex items-center gap-6 w-full md:w-auto">
                            <div class="h-16 w-16 rounded-none flex flex-col items-center justify-center shrink-0 border-2 transition-all duration-300"
                                :class="attempt.percentage >= 50 ? 'bg-emerald-50 border-emerald-500/20' : 'bg-rose-50 border-rose-500/20'">
                                <span class="text-xl font-medium tabular-nums tracking-tighter" :class="attempt.percentage >= 50 ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ attempt.percentage }}%
                                </span>
                            </div>

                            <div class="flex-1">
                                <div class="font-semibold text-slate-900 text-lg uppercase tracking-tighter italic leading-none">Attempt #{{ attempts.length - index }}</div>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400 font-medium mt-2 uppercase tracking-widest leading-none">
                                    <Calendar class="h-3.5 w-3.5 text-action" />
                                    {{ attempt.date }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-10 w-full md:w-auto justify-between md:justify-end bg-slate-50 md:bg-transparent p-4 md:p-0 rounded-none border-y border-slate-100 md:border-none">
                            <div class="text-center">
                                <div class="text-[9px] text-slate-400 uppercase font-medium tracking-widest mb-1">Raw Score</div>
                                <div class="font-medium text-slate-900 text-lg tabular-nums tracking-tighter">
                                    {{ attempt.score }} <span class="text-slate-300 text-xs">/ {{ attempt.total }}</span>
                                </div>
                            </div>

                            <div class="text-right min-w-[120px]">
                                <div class="text-[9px] text-slate-400 uppercase font-medium tracking-widest mb-1">Status</div>
                                <div class="font-semibold text-base uppercase tracking-tight leading-none" :class="attempt.percentage >= 50 ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ attempt.percentage >= 50 ? 'Qualified' : 'Requires Review' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="stats.attempts < 3" class="flex justify-center pt-8 pb-12">
                    <Link :href="route('quizzes.show', props.quizId)">
                        <Button class="btn-theme font-semibold px-8 py-2.5 rounded-none text-sm shadow-md transition-all active:scale-95 uppercase tracking-widest">
                            Retake Evaluation <RotateCcw class="ml-2 h-4 w-4" />
                        </Button>
                    </Link>
                </div>
                <div v-else class="flex justify-center pt-8 pb-12">
                    <span class="text-slate-400 font-medium italic text-xs uppercase tracking-widest">
                        Attempt limit reached (3/3)
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
