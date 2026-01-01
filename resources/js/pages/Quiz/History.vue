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

// Breadcrumbs for standardized LMS navigation
const breadcrumbs = [
    { title: 'Quizzes', href: route('quizzes.index') },
    { title: 'History', href: '#' }
];
</script>

<template>
    <Head :title="`Evaluation History - ${quizTitle}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div :class="`theme-${userRole}`" class="min-h-screen bg-slate-50 flex flex-col items-center p-6 font-sans">

            <div class="sticky top-0 z-50 w-full max-w-5xl bg-white border border-slate-200 shadow-sm py-5 px-8 flex justify-between items-center rounded-none mb-8">
                <div class="flex items-center gap-5">
                    <Link :href="route('quizzes.index')" class="text-slate-400 hover:text-action transition-colors">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter leading-none">Evaluation History</h2>
                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.25em] mt-1.5">{{ quizTitle }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-5 py-2.5 rounded-none font-black text-xl border border-action/20 bg-action/5 text-action tabular-nums tracking-tighter">
                    <BarChart3 class="h-5 w-5" /> {{ stats.attempts }}
                </div>
            </div>

            <div class="w-full max-w-5xl space-y-12">

                <Card class="bg-white text-slate-900 border border-slate-200 shadow-sm overflow-hidden rounded-none">
                    <div class="p-12">
                        <h3 class="font-black text-slate-400 uppercase tracking-[0.3em] text-[10px] mb-12 text-center border-b border-slate-50 pb-6">
                            Performance Metrics
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                            <div class="flex flex-col items-center group transition-transform duration-300">
                                <span class="text-5xl font-black text-slate-900 tabular-nums tracking-tighter">{{ stats.attempts }}</span>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] mt-4 group-hover:text-action transition-colors">Total Sessions</span>
                            </div>

                            <div class="flex flex-col items-center group transition-transform duration-300">
                                <span class="text-5xl font-black text-slate-900 tabular-nums tracking-tighter">{{ stats.average }}%</span>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] mt-4 group-hover:text-action transition-colors">Average Accuracy</span>
                            </div>

                            <div class="flex flex-col items-center group transition-transform duration-300 border-action/10 bg-action/5 p-6 md:p-0 md:bg-transparent">
                                <span class="text-5xl font-black text-action tabular-nums tracking-tighter">{{ stats.best }}%</span>
                                <span class="text-[10px] font-black text-action uppercase tracking-[0.25em] mt-4">Personal Best</span>
                            </div>
                        </div>
                    </div>
                </Card>

                <div class="space-y-6">
                    <h3 class="text-lg font-black text-slate-900 ml-1 flex items-center gap-3 uppercase tracking-tight">
                        <Gamepad2 class="w-6 h-6 text-action" />
                        Past Submissions
                    </h3>

                    <div v-if="attempts.length === 0" class="text-center py-24 bg-white rounded-none border-2 border-dashed border-slate-200">
                        <Trophy class="h-16 w-16 text-slate-200 mx-auto mb-5" />
                        <p class="text-slate-400 font-black uppercase tracking-[0.2em] text-[10px]">No evaluative records identified.</p>
                    </div>

                    <div v-for="(attempt, index) in attempts" :key="attempt.id"
                        class="bg-white rounded-none p-8 flex flex-col md:flex-row items-center justify-between gap-10 shadow-sm border border-slate-200 transition-all hover:border-action/40 group"
                    >
                        <div class="flex items-center gap-10 w-full md:w-auto">
                            <div class="h-24 w-24 rounded-none flex flex-col items-center justify-center shrink-0 border-2 transition-all duration-500"
                                :class="attempt.percentage >= 50 ? 'bg-emerald-50 border-emerald-500/30' : 'bg-rose-50 border-rose-500/30'">
                                <span class="text-2xl font-black tabular-nums tracking-tighter" :class="attempt.percentage >= 50 ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ attempt.percentage }}%
                                </span>
                            </div>

                            <div class="flex-1">
                                <div class="font-black text-slate-900 text-2xl uppercase tracking-tighter italic">Attempt #{{ attempts.length - index }}</div>
                                <div class="flex items-center gap-2.5 text-[10px] text-slate-400 font-black mt-3 uppercase tracking-widest leading-none">
                                    <Calendar class="h-3.5 w-3.5 text-action" />
                                    {{ attempt.date }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-14 w-full md:w-auto justify-between md:justify-end bg-slate-50 md:bg-transparent p-7 md:p-0 rounded-none border-y border-slate-100 md:border-none">
                            <div class="text-center">
                                <div class="text-[9px] text-slate-400 uppercase font-black tracking-[0.3em] mb-2.5">Raw Score</div>
                                <div class="font-black text-slate-900 text-2xl tabular-nums tracking-tighter">
                                    {{ attempt.score }} <span class="text-slate-300 text-base font-bold">/ {{ attempt.total }}</span>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-[9px] text-slate-400 uppercase font-black tracking-[0.3em] mb-2.5">Status</div>
                                <div class="font-black text-lg uppercase tracking-tight leading-none" :class="attempt.percentage >= 50 ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ attempt.percentage >= 50 ? 'QUALIFIED' : 'REQUIRES REVIEW' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center pt-14 pb-12">
                    <Link :href="route('quizzes.show', props.quizId)">
                        <Button class="btn-theme font-black px-20 py-10 rounded-none text-xl shadow-xl transition-all active:scale-95 uppercase tracking-[0.25em]">
                            Restart Session <RotateCcw class="ml-5 h-6 w-6" />
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
