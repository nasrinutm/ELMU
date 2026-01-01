<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    ArrowLeft, CheckCircle, RotateCcw, XCircle,
    Check, Clock, Info, BarChart3, Gamepad2, Send
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

// Define Props
const props = defineProps<{
    quiz: {
        id: number;
        title: string;
        time_limit: number; // Duration in seconds from Controller
        questions: Array<{
            id: number;
            text: string;
            correct_option_id: any;
            explanation?: string;
            options: Array<{ id: any; text: string }>;
        }>;
    };
}>();

const page = usePage();
// FIX: Dynamic role for theme application (Indigo for student)
const userRole = computed(() => page.props.auth.user?.roles[0] || 'student');

// --- STATE ---
const answers = ref<Record<number, any>>({});
const submitted = ref(false);
const score = ref(0);
const timeLeft = ref(props.quiz.time_limit || 600);
let timerInterval: any = null;

// --- BREADCRUMBS FIX ---
const breadcrumbs = [
    { title: 'Quizzes', href: route('quizzes.index') },
    { title: props.quiz.title, href: '#' }
];

// --- COMPUTED ---
const scorePercentage = computed(() => {
    if (!props.quiz.questions || props.quiz.questions.length === 0) return 0;
    return Math.round((score.value / props.quiz.questions.length) * 100);
});

const formattedTime = computed(() => {
    if (isNaN(timeLeft.value)) return "00:00";
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

// --- LOGIC ---
const startTimer = () => {
    stopTimer();
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            submitQuiz(true);
        }
    }, 1000);
};

const stopTimer = () => {
    if (timerInterval) clearInterval(timerInterval);
};

onMounted(() => startTimer());
onUnmounted(() => stopTimer());

// FIX: Handles submission logic without automatic redirection
const submitQuiz = (force = false) => {
    if (!force) {
        const answeredCount = Object.keys(answers.value).length;
        if (answeredCount < props.quiz.questions.length) {
            if (!confirm("You haven't answered all questions. Submit anyway?")) {
                return;
            }
        }
    }

    stopTimer();

    // Calculate Score locally for immediate feedback
    let tempScore = 0;
    props.quiz.questions.forEach(q => {
        if (answers.value[q.id] == q.correct_option_id) tempScore++;
    });

    score.value = tempScore;
    submitted.value = true;

    // Scroll to top to see the result card
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Submit to Backend pluralized quizzes.submit route
    // FIX: Using preserveScroll ensures the view stays on the result card
    router.post(route('quizzes.submit'), {
        quiz_id: props.quiz.id,
        score: tempScore,
        total_questions: props.quiz.questions.length,
        answers: answers.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            console.log("Evaluation data synchronized successfully.");
            // We DO NOT redirect here; we let the "submitted" state show the UI
        },
        onError: () => {
            alert("There was an error saving your results. Please check your connection.");
        }
    });
};

const retryQuiz = () => {
    answers.value = {};
    submitted.value = false;
    score.value = 0;
    timeLeft.value = props.quiz.time_limit || 600;
    startTimer();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <Head :title="`Active Session: ${quiz.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div :class="`theme-${userRole}`" class="min-h-screen bg-slate-50 flex flex-col items-center">

            <div class="sticky top-0 z-50 w-full bg-white border-b border-slate-200 shadow-sm py-4 px-6 flex justify-between items-center rounded-none mx-0">
                <div class="flex items-center gap-3">
                    <Gamepad2 class="w-6 h-6 text-action" />
                    <h2 class="text-lg font-bold text-slate-900 truncate uppercase tracking-tight">{{ quiz.title }}</h2>
                </div>
                <div class="flex items-center gap-2 px-5 py-2 rounded-none font-mono text-xl font-bold border border-action/20 bg-action/5 text-action shrink-0"
                    :class="{ 'bg-rose-50 border-rose-200 text-rose-600 animate-pulse': timeLeft < 60 }">
                    <Clock class="h-5 w-5" /> {{ formattedTime }}
                </div>
            </div>

            <div class="w-full max-w-3xl p-6 space-y-8 pb-24">

                <div v-if="submitted" class="animate-in slide-in-from-top duration-500">
                    <Card class="border-none bg-white text-slate-900 shadow-xl overflow-hidden rounded-none border border-slate-200">
                        <div class="p-10 flex flex-col items-center text-center gap-4">
                            <div class="rounded-none p-4 mb-2" :class="scorePercentage >= 50 ? 'bg-emerald-100' : 'bg-rose-100'">
                                <CheckCircle v-if="scorePercentage >= 50" class="h-14 w-14 text-emerald-600" />
                                <XCircle v-else class="h-14 w-14 text-rose-600" />
                            </div>

                            <h2 class="text-3xl font-black text-slate-900 uppercase">
                                {{ scorePercentage >= 70 ? 'Excellent Performance!' : 'Keep Practicing!' }}
                            </h2>

                            <div class="bg-action text-white rounded-none px-12 py-8 my-4 shadow-lg shadow-action/20">
                                <div class="text-6xl font-black">{{ scorePercentage }}%</div>
                                <div class="text-xs opacity-80 uppercase tracking-widest mt-2 font-black">Your Score</div>
                            </div>

                            <div class="flex gap-4 mt-6">
                                <Link :href="route('quizzes.history', quiz.id)">
                                    <Button variant="outline" class="border-slate-200 text-slate-600 hover:bg-slate-50 rounded-none px-8 py-6 font-black uppercase tracking-widest">
                                        <BarChart3 class="mr-2 h-5 w-5" /> View History
                                    </Button>
                                </Link>
                                <Button @click="retryQuiz" class="btn-theme rounded-none px-8 py-6 font-black shadow-md uppercase tracking-widest">
                                    <RotateCcw class="mr-2 h-5 w-5" /> Retake Evaluation
                                </Button>
                            </div>
                        </div>
                    </Card>
                    <h3 class="text-slate-400 text-[10px] font-black mt-12 mb-6 text-center uppercase tracking-[0.3em]">Evaluation Breakdown</h3>
                </div>

                <div v-for="(question, index) in quiz.questions" :key="question.id">
                    <Card class="border border-slate-200 shadow-sm overflow-hidden transition-all bg-white text-slate-900 rounded-none">
                        <CardHeader class="pb-4 border-b border-slate-100 px-8 pt-10">
                            <CardTitle class="text-lg font-medium flex items-start gap-3">
                                <span class="text-action font-black tracking-wider uppercase text-[10px]">Item {{ index + 1 }}</span>
                            </CardTitle>
                            <p class="text-xl font-bold mt-3 text-slate-900 leading-snug">{{ question.text }}</p>
                        </CardHeader>

                        <CardContent class="px-8 pb-10 pt-8">
                            <div class="flex flex-col gap-4">
                                <div v-for="option in question.options" :key="option.id"
                                    class="relative flex items-center p-5 rounded-none border transition-all cursor-pointer"
                                    :class="[
                                        submitted && option.id == question.correct_option_id
                                            ? 'bg-emerald-50 border-emerald-500 text-emerald-700'
                                            : submitted && answers[question.id] == option.id
                                                ? 'bg-rose-50 border-rose-500 text-rose-700'
                                                : answers[question.id] == option.id
                                                    ? 'bg-action/5 border-action ring-1 ring-action'
                                                    : 'bg-slate-50 border-slate-100 hover:border-action/30'
                                    ]"
                                    @click="!submitted && (answers[question.id] = option.id)"
                                >
                                    <div class="mr-5 h-6 w-6 rounded-none border-2 border-slate-200 flex items-center justify-center transition-colors"
                                        :class="{ 'border-action': answers[question.id] == option.id }">
                                        <div class="h-3 w-3 rounded-none bg-action" v-if="answers[question.id] == option.id"></div>
                                    </div>
                                    <span class="font-bold text-lg">{{ option.text }}</span>
                                </div>
                            </div>

                            <div v-if="submitted && question.explanation" class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-5 rounded-none">
                                <div class="flex items-start gap-3">
                                    <Info class="h-5 w-5 text-blue-500 mt-0.5 shrink-0" />
                                    <div>
                                        <p class="text-xs font-black text-blue-800 mb-1 uppercase">Study Note:</p>
                                        <p class="text-sm text-blue-700 leading-relaxed">{{ question.explanation }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div v-if="!submitted" class="mt-8 flex justify-center w-full">
                    <Button @click="() => submitQuiz(false)"
                        class="btn-theme w-full md:w-full font-black px-16 py-10 rounded-none text-xl shadow-lg transition-all active:scale-95 uppercase tracking-widest">
                        Submit Quiz Session <Send class="ml-3 h-6 w-6" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
