<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, CheckCircle, XCircle, AlertCircle } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    attempt: {
        id: number;
        score: number;
        total: number;
        percentage: number;
        date: string;
        student_name: string;
    };
    quiz_title: string;
    questions: Array<{
        id: number;
        text: string;
        explanation: string;
        student_selected_option_id: number | null;
        is_correct: boolean;
        options: Array<{
            id: number;
            text: string;
            is_correct: boolean;
        }>;
    }>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Results', href: '#' },
    { title: 'Review Attempt', href: '#' },
];

// FIX: This function handles the back navigation safely
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="`Review - ${props.attempt.student_name}`" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900">Attempt Review</h1>
                    <p class="text-slate-500 font-medium">
                        Student: <span class="text-teal-700 font-bold">{{ attempt.student_name }}</span> 
                        | Date: {{ attempt.date }}
                    </p>
                </div>
                <Button @click="goBack" variant="outline" class="border-slate-200 text-slate-600 hover:text-teal-600 rounded-xl px-6 font-bold">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Results
                </Button>
            </div>

            <Card class="bg-white border-none shadow-sm rounded-[2rem] p-8 flex items-center justify-between">
                <div>
                    <div class="text-sm font-bold text-slate-400 uppercase tracking-widest">Final Score</div>
                    <div class="text-4xl font-black text-slate-900">
                        {{ attempt.score }} / {{ attempt.total }}
                    </div>
                </div>
                <div class="h-20 w-20 rounded-2xl flex items-center justify-center font-black text-2xl border"
                    :class="attempt.percentage >= 50 ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'">
                    {{ attempt.percentage }}%
                </div>
            </Card>

            <div class="space-y-6">
                <div v-for="(question, index) in questions" :key="question.id" 
                    class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
                    
                    <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex justify-between items-start">
                        <div class="flex gap-3">
                            <span class="h-8 w-8 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold shrink-0">
                                {{ index + 1 }}
                            </span>
                            <h3 class="font-bold text-lg text-slate-900 mt-0.5">{{ question.text }}</h3>
                        </div>
                        
                        <Badge :class="question.is_correct ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'" class="px-3 py-1 font-bold">
                            <CheckCircle v-if="question.is_correct" class="w-4 h-4 mr-1" />
                            <XCircle v-else class="w-4 h-4 mr-1" />
                            {{ question.is_correct ? 'Correct' : 'Incorrect' }}
                        </Badge>
                    </div>

                    <div class="p-6 space-y-3">
                        <div v-for="option in question.options" :key="option.id" 
                            class="flex items-center p-4 rounded-xl border-2 transition-all relative"
                            :class="[
                                option.is_correct 
                                    ? 'border-emerald-500 bg-emerald-50' 
                                    : (question.student_selected_option_id === option.id && !option.is_correct)
                                        ? 'border-rose-500 bg-rose-50'
                                        : 'border-slate-100 bg-white opacity-60'
                            ]">

                            
                            <div class="mr-4">
                                <CheckCircle v-if="option.is_correct" class="w-6 h-6 text-emerald-600" />
                                <XCircle v-else-if="question.student_selected_option_id === option.id" class="w-6 h-6 text-rose-600" />
                                <div v-else class="w-6 h-6 rounded-full border-2 border-slate-200"></div>
                            </div>

                            <span class="font-bold text-slate-700">{{ option.text }}</span>

                            <span v-if="option.is_correct" class="absolute right-4 text-xs font-black text-emerald-600 uppercase tracking-wider">Correct Answer</span>
                            <span v-if="question.student_selected_option_id === option.id && !option.is_correct" class="absolute right-4 text-xs font-black text-rose-600 uppercase tracking-wider">Student Selection</span>
                        </div>

                        <div v-if="question.explanation" class="mt-4 p-4 bg-blue-50 text-blue-800 rounded-xl border border-blue-100 text-sm font-medium flex gap-2">
                            <AlertCircle class="w-5 h-5 shrink-0" />
                            <div>
                                <span class="font-bold block mb-1">Explanation:</span>
                                {{ question.explanation }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>