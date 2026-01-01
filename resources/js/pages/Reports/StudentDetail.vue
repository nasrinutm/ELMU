<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    FileText, CheckCircle, Trophy, User as UserIcon,
    Download, PencilLine, Trash2, X, MessageSquare
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    student: { id: number; name: string; email: string; username: string };
    activities: any[];
    quizzes: any[];
    existingReport: any | null;
    stats: { quiz_avg: number; activities_completed: number };
}>();

const uniqueHighestQuizzes = computed(() => {
    const highestScores: Record<string, any> = {};

    props.quizzes.forEach((quiz) => {
          if (!highestScores[quiz.title] || quiz.score > highestScores[quiz.title].score) {
            highestScores[quiz.title] = quiz;
        }
    });
    return Object.values(highestScores).sort((a, b) => b.score - a.score);
});

const page = usePage();
const teacherName = (page.props.auth.user as any).name;
const isModalOpen = ref(false);

const breadcrumbs = [
    { title: 'Performance Reports', href: route('reports.index') },
    { title: props.student.name, href: '#' },
];

const form = useForm({
    student_id: props.student.id,
    comments: props.existingReport ? props.existingReport.comments : '',
});

watch(() => props.existingReport, (newVal) => {
    form.comments = newVal ? newVal.comments : '';
});

const submitRemark = () => {
    form.post(route('reports.remark.save'), {
        preserveScroll: true,
        onSuccess: () => isModalOpen.value = false
    });
};

const deleteRemark = () => {
    if (!props.existingReport) return;
    if (confirm('Delete this evaluation?')) {
        form.delete(route('reports.remark.delete', props.existingReport.id), {
            preserveScroll: true
        });
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};

// FIX: Define print function to avoid "window does not exist" error
const handlePrint = () => {
    window.print();
};
</script>

<template>
    <Head :title="student.name + ' - Report'" />
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row items-center gap-6">
                <div class="h-20 w-20 rounded-full bg-teal-600 text-white flex items-center justify-center text-3xl font-bold border-4 border-teal-50 shrink-0 uppercase">
                    {{ student.name.charAt(0) }}
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-2xl font-bold text-slate-900">{{ student.name }}</h1>
                    <p class="text-slate-500">{{ student.email }}</p>
                </div>
                <div class="flex gap-3 no-print">
                    <Button @click="isModalOpen = true" class="bg-teal-600 hover:bg-teal-700 text-white gap-2">
                        <PencilLine class="w-4 h-4" /> Evaluation
                    </Button>
                    <Button @click="handlePrint" variant="outline" class="gap-2 text-slate-600">
                        <Download class="w-4 h-4" /> Download
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg"><FileText class="w-6 h-6" /></div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Activities Completed</p>
                        <p class="text-2xl font-bold text-slate-900">{{ activities.length }}</p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-teal-50 text-teal-600 rounded-lg"><Trophy class="w-6 h-6" /></div>
                    <div>
                        <p class="text-xs text-slate-400 font-bold uppercase">Quizzes Taken</p>
                        <p class="text-2xl font-bold text-slate-900">{{ quizzes.length }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 uppercase text-[10px] tracking-wider">
                        <MessageSquare class="w-4 h-4 text-teal-600" /> Official Teacher Evaluation
                    </h3>
                    <button v-if="existingReport" @click="deleteRemark" class="no-print text-slate-400 hover:text-red-600">
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
                <div class="p-6">
                    <div v-if="existingReport" class="italic text-slate-700 font-medium text-lg leading-relaxed">
                        "{{ existingReport.comments }}"
                    </div>
                    <div v-else class="text-center py-4 text-slate-400 italic">No evaluation recorded.</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-12">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
                    <div class="px-6 py-4 border-b bg-slate-50 font-bold text-xs uppercase tracking-tight flex items-center gap-2">
                        <CheckCircle class="w-4 h-4 text-teal-600" /> Activities
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="(act, index) in activities" :key="index" class="p-4 flex justify-between items-center">
                            <span class="font-medium">{{ act.title }}</span>
                            <span class="text-xs text-slate-400">{{ formatDate(act.completed_at) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden text-slate-900">
                    <div class="px-6 py-4 border-b bg-slate-50 font-bold text-xs uppercase tracking-tight flex items-center gap-2">
                        <Trophy class="w-4 h-4 text-teal-600" /> Quizzes (Best Attempts)
                    </div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="(quiz, index) in uniqueHighestQuizzes" :key="index" class="p-4 flex justify-between items-center">
                            <span class="font-medium">{{ quiz.title }}</span>
                            <Badge class="bg-teal-50 text-teal-700 border-teal-200 font-bold">{{ quiz.score }}%</Badge>
                        </div>
                        
                        <div v-if="uniqueHighestQuizzes.length === 0" class="p-6 text-center text-slate-400 italic">
                            No quizzes attempted yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl border border-slate-200 overflow-hidden text-slate-900">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 font-bold">
                    Teacher Evaluation
                    <button @click="isModalOpen = false" class="text-slate-400"><X /></button>
                </div>
                <form @submit.prevent="submitRemark" class="p-6 space-y-4">
                    <textarea v-model="form.comments" rows="5" class="w-full border rounded-lg p-4 outline-none focus:ring-2 focus:ring-teal-500 transition-all text-slate-900"></textarea>
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" class="bg-teal-600 text-white" :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
