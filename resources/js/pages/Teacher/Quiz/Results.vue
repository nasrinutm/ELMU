<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3'; // Added usePage
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Search, PlusCircle, CheckCircle } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { computed, ref, watch } from 'vue'; // Added watch
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

// Filter students
const filteredStudents = computed(() => {
    if (!props.studentData) return [];
    return props.studentData.filter(s => 
        s.user_name.toLowerCase().includes(search.value.toLowerCase()) ||
        s.email.toLowerCase().includes(search.value.toLowerCase())
    );
});

// Grant Extra Attempt
const grantAttempt = (userId: number, name: string, current: number, max: number) => {
    if (confirm(`Student has used ${current}/${max} attempts.\n\nGrant 1 more attempt to ${name}?`)) {
        router.post(route('teacher.attempt.grant', { quiz: props.quiz.id, user: userId }), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: You could show a local toast here if you wanted
                console.log('Attempt granted');
            }
        });
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Quiz Results" />

    <AppLayout>
        <div class="p-6 bg-[#002B5C] min-h-screen text-white">
            <div class="max-w-6xl mx-auto space-y-6">
                
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 border-b border-[#FFD700]/30 pb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-[#FFD700]">Student Results</h1>
                        <p class="text-gray-200 mt-1">Manage attempts for: <strong>{{ quiz.title }}</strong></p>
                    </div>
                    <Link :href="route('teacher.quiz.index')">
                        <Button variant="outline" class="border-[#FFD700] text-[#FFD700] hover:bg-[#FFD700] hover:text-[#002B5C] font-bold">
                             <ArrowLeft class="mr-2 h-4 w-4" /> Back to Dashboard
                        </Button>
                    </Link>
                </div>

                <div class="relative">
                    <Search class="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                    <Input v-model="search" placeholder="Search student name..." class="pl-10 bg-white text-black h-12 text-lg" />
                </div>

                <div class="grid gap-6">
                    <Card v-for="student in filteredStudents" :key="student.user_id" class="border-none bg-white text-[#002B5C] overflow-hidden shadow-lg">
                        
                        <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-4 w-full">
                                <div class="h-12 w-12 rounded-full bg-[#002B5C] text-white flex items-center justify-center font-bold text-lg">
                                    {{ student.user_name.charAt(0) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-xl">{{ student.user_name }}</h3>
                                    <div class="text-sm text-gray-500">{{ student.email }}</div>
                                </div>
                                
                                <div class="ml-auto flex items-center gap-3">
                                    <Badge v-if="student.extra_granted > 0" class="bg-[#FFD700] text-[#002B5C] text-md px-3 py-1">
                                        +{{ student.extra_granted }} Bonus Given
                                    </Badge>

                                    <div class="flex items-center gap-2 px-3 py-1 rounded-md font-bold border"
                                        :class="student.is_locked ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200'">
                                        <span class="text-sm uppercase tracking-wide">Attempts:</span>
                                        <span class="text-lg">{{ student.attempts_count }}</span>
                                        <span class="text-gray-400">/</span>
                                        <span class="text-lg">{{ student.max_allowed }}</span>
                                    </div>
                                </div>
                            </div>

                            <Button v-if="student.is_locked" 
                                @click="grantAttempt(student.user_id, student.user_name, student.attempts_count, student.max_allowed)" 
                                class="bg-[#002B5C] text-white hover:bg-[#004080] shrink-0 font-bold border-2 border-[#002B5C] hover:border-[#FFD700]">
                                <PlusCircle class="mr-2 h-5 w-5" /> Grant Another Attempt
                            </Button>
                            
                            <div v-else class="flex items-center text-green-600 font-bold bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                                <CheckCircle class="mr-2 h-5 w-5" /> Student Can Take Quiz
                            </div>
                        </div>

                        <div class="p-4 space-y-2">
                            <div v-for="(attempt, index) in student.history" :key="attempt.id" 
                                class="flex items-center justify-between p-3 rounded bg-gray-50 hover:bg-gray-100 transition-colors border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-gray-400">Attempt #{{ student.history.length - index }}</span>
                                    <div class="text-sm text-gray-600 font-medium">
                                        {{ formatDate(attempt.created_at) }}
                                    </div>
                                </div>
                                
                                <div class="font-mono font-bold text-lg" 
                                    :class="(attempt.score / attempt.total_questions) >= 0.5 ? 'text-green-600' : 'text-red-600'">
                                    Score: {{ attempt.score }} / {{ attempt.total_questions }}
                                </div>
                            </div>
                        </div>
                    </Card>

                    <div v-if="filteredStudents.length === 0" class="text-center py-12 text-gray-300 bg-[#004080]/50 rounded-lg border-2 border-dashed border-[#FFD700]/30">
                        No students found.
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>