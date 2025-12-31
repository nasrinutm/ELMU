<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ArrowLeft, CheckCircle, RotateCcw, XCircle, Check, Clock, Info, BarChart3, Gamepad2 } from 'lucide-vue-next';
import { route } from 'ziggy-js';

// Define Props
const props = defineProps<{
    quiz: {
        id: number;
        title: string;
        duration?: number;
        questions: Array<{
            id: number;
            text: string;
            correct_option_id: string;
            explanation?: string;
            options: Array<{ id: string; text: string }>;
        }>;
    };
}>();

// --- STATE ---
const answers = ref<Record<number, string>>({});
const submitted = ref(false);
const score = ref(0);
const timeLeft = ref(props.quiz.duration || 600); 
let timerInterval: any = null;

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

const submitQuiz = (force = false) => {
    if (!force) {
        const answeredCount = Object.keys(answers.value).length;
        if (answeredCount < props.quiz.questions.length) {
            alert("Please answer all questions!");
            return;
        }
    }
    stopTimer();
    let tempScore = 0;
    props.quiz.questions.forEach(q => {
        if (answers.value[q.id] === q.correct_option_id) tempScore++;
    });
    score.value = tempScore;
    submitted.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });

    router.post(route('quiz.submit'), {
        quiz_id: props.quiz.id,
        quiz_title: props.quiz.title,
        score: tempScore,
        total_questions: props.quiz.questions.length
    });
};

const retryQuiz = () => {
    answers.value = {};
    submitted.value = false;
    score.value = 0;
    timeLeft.value = props.quiz.duration || 600;
    startTimer();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <Head :title="quiz.title" />

    <AppLayout>
        <div class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm py-4 px-6 flex justify-between items-center rounded-b-3xl mx-2 mt-2">
            <div class="flex items-center gap-3">
                <Gamepad2 class="w-6 h-6 text-teal-600" />
                <h2 class="text-lg font-bold text-slate-900 truncate mr-4">{{ quiz.title }}</h2>
            </div>
            <div class="flex items-center gap-2 px-5 py-2 rounded-full font-mono text-xl font-bold border border-teal-200 bg-teal-50 text-teal-700 shrink-0"
                :class="{ 'bg-rose-50 border-rose-200 text-rose-600 animate-pulse': timeLeft < 60 }">
                <Clock class="h-5 w-5" /> {{ formattedTime }}
            </div>
        </div>

        <div class="flex flex-col gap-6 p-6 bg-slate-50 min-h-screen items-center">
            
            <div v-if="submitted" class="w-full max-w-3xl animate-in slide-in-from-top duration-500">
                <Card class="border-none bg-white text-slate-900 shadow-xl overflow-hidden rounded-[2rem] border border-slate-200">
                    <div class="p-10 flex flex-col items-center text-center gap-4">
                        <div class="rounded-full p-4 mb-2" :class="scorePercentage >= 50 ? 'bg-emerald-100' : 'bg-rose-100'">
                            <CheckCircle v-if="scorePercentage >= 50" class="h-14 w-14 text-emerald-600" />
                            <XCircle v-else class="h-14 w-14 text-rose-600" />
                        </div>
                        
                        <h2 class="text-3xl font-black text-slate-900">
                            {{ scorePercentage >= 70 ? 'Great Job!' : 'Keep Practicing!' }}
                        </h2>
                        
                        <div class="bg-teal-600 text-white rounded-[2rem] px-12 py-8 my-4 shadow-lg shadow-teal-600/20">
                            <div class="text-6xl font-black">{{ scorePercentage }}%</div>
                            <div class="text-sm opacity-80 uppercase tracking-widest mt-2 font-bold">Your Score</div>
                        </div>

                        <div class="flex gap-4 mt-6">
                            <Link :href="route('quiz.history', quiz.id)">
                                <Button variant="outline" class="border-slate-200 text-slate-600 hover:bg-slate-50 rounded-2xl px-8 py-6">
                                    <BarChart3 class="mr-2 h-5 w-5" /> History
                                </Button>
                            </Link>
                            <Button @click="retryQuiz" class="bg-teal-600 hover:bg-teal-700 text-white rounded-2xl px-8 py-6 font-bold shadow-md">
                                <RotateCcw class="mr-2 h-5 w-5" /> Retake
                            </Button>
                        </div>
                    </div>
                </Card>
                <h3 class="text-slate-400 text-sm font-black mt-12 mb-6 text-center uppercase tracking-[0.2em]">Question Review</h3>
            </div>

            <div class="w-full max-w-3xl flex flex-col gap-8 pb-16">
                <div v-for="(question, index) in quiz.questions" :key="question.id">
                    <Card class="border border-slate-200 shadow-sm overflow-hidden transition-all bg-white text-slate-900 rounded-[2rem]">
                        <CardHeader class="pb-4 border-b border-slate-50 px-8 pt-10">
                            <CardTitle class="text-lg font-medium flex items-start gap-3">
                                <span class="text-teal-600 font-bold tracking-wider uppercase text-xs">Question {{ index + 1 }}</span>
                            </CardTitle>
                            <p class="text-xl font-bold mt-3 text-slate-900 leading-snug">{{ question.text }}</p>
                        </CardHeader>

                        <CardContent class="px-8 pb-10 pt-8">
                            <div class="flex flex-col gap-4">
                                <div v-for="option in question.options" :key="option.id" 
                                    class="relative flex items-center p-5 rounded-2xl border transition-all"
                                    :class="[
                                        submitted && option.id === question.correct_option_id 
                                            ? 'bg-emerald-50 border-emerald-500 text-emerald-700' 
                                            : submitted && answers[question.id] === option.id 
                                                ? 'bg-rose-50 border-rose-500 text-rose-700' 
                                                : answers[question.id] === option.id 
                                                    ? 'bg-teal-50 border-teal-600 ring-1 ring-teal-600' 
                                                    : 'bg-slate-50 border-slate-100 hover:border-teal-300'
                                    ]"
                                    @click="!submitted && (answers[question.id] = option.id)"
                                >
                                    <div class="mr-5 h-6 w-6 rounded-full border-2 border-slate-200 flex items-center justify-center transition-colors"
                                        :class="{ 'border-teal-600': answers[question.id] === option.id }">
                                        <div class="h-3 w-3 rounded-full bg-teal-600" v-if="answers[question.id] === option.id"></div>
                                    </div>
                                    <span class="font-semibold text-lg">{{ option.text }}</span>
                                </div>
                            </div>

                            <div v-if="submitted && question.explanation" class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-5 rounded-r-2xl">
                                <div class="flex items-start gap-3">
                                    <Info class="h-5 w-5 text-blue-500 mt-0.5 shrink-0" />
                                    <div>
                                        <p class="text-sm font-bold text-blue-800 mb-1">Explanation:</p>
                                        <p class="text-sm text-blue-700 leading-relaxed">{{ question.explanation }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div v-if="!submitted" class="mt-8 flex justify-center w-full">
                    <Button @click="() => submitQuiz(false)" 
                        class="w-full md:w-3/4 bg-teal-600 hover:bg-teal-700 text-white font-black px-16 py-10 rounded-3xl text-xl shadow-lg transition-all active:scale-95 uppercase tracking-widest">
                        Submit Answers <CheckCircle class="ml-3 h-6 w-6" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>