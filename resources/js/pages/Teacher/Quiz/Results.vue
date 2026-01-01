<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
// Added Eye and Trash2 icons here
import { ArrowLeft, Search, PlusCircle, CheckCircle, User, Mail, History, Award, Eye, Trash2 } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    quiz: {
        id: number;
        title: string;
    };
    studentData: Array<{
        user_id: number;
        user_name: string;
        email: string;
        attempts_count: number;
        max_allowed: number;
        extra_granted: number;
        is_locked: boolean;
        history: Array<{
            id: number;
            score: number;
            total_questions: number;
            created_at: string;
        }>;
    }>;
}>();

const search = ref('');

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Quiz Management', href: route('teacher.quiz.index') },
    { title: 'Student Results', href: '#' },
];

const filteredStudents = computed(() => {
    if (!props.studentData) return [];
    return props.studentData.filter(s => 
        s.user_name.toLowerCase().includes(search.value.toLowerCase()) ||
        s.email.toLowerCase().includes(search.value.toLowerCase())
    );
});

const grantAttempt = (userId: number, name: string, current: number, max: number) => {
    if (confirm(`Student has used ${current}/${max} attempts.\n\nGrant 1 more attempt to ${name}?`)) {
        router.post(route('teacher.attempt.grant', { quiz: props.quiz.id, user: userId }), {}, {
            preserveScroll: true,
            onSuccess: () => console.log('Attempt granted')
        });
    }
};

// --- NEW FUNCTION: DELETE ATTEMPT ---
const deleteAttempt = (attemptId: number) => {
    if (confirm('Are you sure you want to delete this attempt record? This cannot be undone.')) {
        router.delete(route('teacher.attempt.destroy', attemptId), {
            preserveScroll: true,
            onSuccess: () => console.log('Attempt deleted')
        });
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Results - ${quiz.title}`" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                        <Award class="w-8 h-8 text-teal-600" />
                        Student Results
                    </h1>
                    <p class="text-slate-500 mt-1 font-medium">
                        Managing attempts for: <span class="text-teal-700 font-bold">{{ quiz.title }}</span>
                    </p>
                </div>
                <Link :href="route('teacher.quiz.index')">
                    <Button variant="outline" class="border-slate-200 text-slate-600 hover:bg-teal-50 hover:text-teal-600 rounded-xl px-6 font-bold shadow-sm">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Back to Dashboard
                    </Button>
                </Link>
            </div>

            <div class="bg-white p-4 rounded-[1.5rem] border border-slate-200 shadow-sm">
                <div class="relative w-full">
                    <Search class="absolute left-4 top-3.5 h-4 w-4 text-slate-400" />
                    <Input 
                        v-model="search" 
                        placeholder="Search student by name or email..." 
                        class="pl-11 h-12 bg-slate-50 border-slate-200 rounded-xl focus:bg-white focus:ring-teal-500 transition-all w-full text-lg" 
                    />
                </div>
            </div>

            <div class="grid gap-6">
                <div v-if="filteredStudents.length === 0" class="text-center py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200 text-slate-400">
                    No students found matching your search.
                </div>

                <Card v-for="student in filteredStudents" :key="student.user_id" 
                    class="border-none bg-white shadow-sm hover:shadow-md transition-shadow overflow-hidden rounded-[2rem] border border-slate-100">
                    
                    <div class="p-6 bg-white border-b border-slate-50 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center font-black text-xl border border-teal-100">
                                {{ student.user_name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="font-black text-xl text-slate-900">{{ student.user_name }}</h3>
                                <div class="flex items-center gap-1.5 text-sm text-slate-400 font-medium">
                                    <Mail class="w-3.5 h-3.5" /> {{ student.email }}
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                            <Badge v-if="student.extra_granted > 0" class="bg-amber-50 text-amber-700 border-amber-200 px-3 py-1.5 rounded-lg font-bold">
                                +{{ student.extra_granted }} Bonus Attempt
                            </Badge>

                            <div class="flex items-center gap-2 px-4 py-2 rounded-xl font-black border"
                                :class="student.is_locked ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100'">
                                <span class="text-[10px] uppercase tracking-widest opacity-70">Attempts:</span>
                                <span>{{ student.attempts_count }} / {{ student.max_allowed }}</span>
                            </div>

                            <Button v-if="student.is_locked" 
                                @click="grantAttempt(student.user_id, student.user_name, student.attempts_count, student.max_allowed)" 
                                class="bg-teal-600 text-white hover:bg-teal-700 rounded-xl font-bold shadow-lg shadow-teal-600/10 transition-all active:scale-95">
                                <PlusCircle class="mr-2 h-4 w-4" /> Grant Attempt
                            </Button>
                            
                            <div v-else class="flex items-center text-emerald-600 font-bold bg-emerald-50/50 px-4 py-2 rounded-xl border border-emerald-100 text-sm">
                                <CheckCircle class="mr-2 h-4 w-4" /> Active
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/30">
                        <div class="flex items-center gap-2 mb-4 text-slate-400 font-bold text-xs uppercase tracking-widest px-2">
                            <History class="w-4 h-4" /> Attempt History
                        </div>
                        
                        <div class="grid gap-3">
                            <div v-for="(attempt, index) in student.history" :key="attempt.id" 
                                class="flex items-center justify-between p-4 rounded-2xl bg-white border border-slate-100 hover:border-teal-200 transition-colors shadow-sm">
                                <div class="flex items-center gap-4">
                                    <span class="text-xs font-black text-slate-300">#{{ student.history.length - index }}</span>
                                    <div class="text-sm text-slate-600 font-bold">
                                        {{ formatDate(attempt.created_at) }}
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <div class="px-4 py-1.5 rounded-lg font-black text-sm" 
                                        :class="(attempt.score / attempt.total_questions) >= 0.5 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                                        {{ attempt.score }} / {{ attempt.total_questions }}
                                    </div>

                                    <Link :href="route('teacher.attempt.review', attempt.id)">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>

                                    <Button @click="deleteAttempt(attempt.id)" variant="ghost" size="icon" 
                                        class="h-8 w-8 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-lg">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <div v-if="student.history.length === 0" class="text-center py-4 text-slate-400 text-sm font-medium">
                                No attempts recorded yet.
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AppSidebarLayout>
</template>