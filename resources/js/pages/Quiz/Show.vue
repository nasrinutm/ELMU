<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ArrowLeft, CheckCircle, RotateCcw, XCircle, Check, Clock, Info, BarChart3 } from 'lucide-vue-next';
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
// Default to 10 mins if duration is missing to prevent crash
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
            submitQuiz(true); // Auto-submit when time hits 0
        }
    }, 1000);
};

const stopTimer = () => {
    if (timerInterval) clearInterval(timerInterval);
};

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    stopTimer();
});

// --- SUBMIT FUNCTION (UPDATED) ---
const submitQuiz = (force = false) => {
    // 1. Validation (Skip if forced by timer)
    if (!force) {
        const answeredCount = Object.keys(answers.value).length;
        const totalCount = props.quiz.questions.length;
        if (answeredCount < totalCount) {
            alert(`Please answer all questions! (${answeredCount}/${totalCount} answered)`);
            return;
        }
    }

    stopTimer();
    
    // 2. Calculate Score
    let tempScore = 0;
    props.quiz.questions.forEach(q => {
        if (answers.value[q.id] === q.correct_option_id) {
            tempScore++;
        }
    });

    score.value = tempScore;
    submitted.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // 3. Save to Database
    router.post(route('quiz.submit'), {
        quiz_id: props.quiz.id,
        quiz_title: props.quiz.title,
        score: tempScore,
        total_questions: props.quiz.questions.length
    }, {
        preserveScroll: true, // Keep user at top to see results
        onSuccess: () => {
            console.log("Quiz result saved successfully!");
        },
        onError: (errors) => {
            console.error("Failed to save result:", errors);
        }
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
        <div class="sticky top-0 z-50 bg-[#002B5C] border-b border-[#FFD700]/30 shadow-md py-3 px-6 flex justify-between items-center text-white">
            <h2 class="text-lg font-bold text-[#FFD700]">{{ quiz.title }}</h2>
            <div class="flex items-center gap-2 px-4 py-1.5 rounded-full font-mono text-xl font-bold border"
                :class="timeLeft < 60 ? 'bg-red-900/50 border-red-500 text-red-400 animate-pulse' : 'bg-[#004080] border-[#FFD700] text-white'">
                <Clock class="h-5 w-5" /> {{ formattedTime }}
            </div>
        </div>

        <div class="flex flex-col gap-6 p-6 bg-[#002B5C] min-h-screen text-white items-center">
            
            <div v-if="submitted" class="w-full max-w-3xl animate-in slide-in-from-top duration-500">
                <Card class="border-none bg-white text-[#002B5C] shadow-xl overflow-hidden">
                    <div class="p-8 flex flex-col items-center text-center gap-4">
                        <div class="rounded-full p-4 mb-2" :class="scorePercentage >= 50 ? 'bg-green-100' : 'bg-red-100'">
                            <CheckCircle v-if="scorePercentage >= 50" class="h-12 w-12 text-green-600" />
                            <XCircle v-else class="h-12 w-12 text-red-600" />
                        </div>
                        
                        <h2 class="text-2xl font-bold">
                            {{ scorePercentage >= 70 ? 'Great Job!' : (scorePercentage >= 50 ? 'Good Effort!' : 'Keep Practicing!') }}
                        </h2>
                        <p class="text-gray-500">
                            You scored {{ score }} out of {{ quiz.questions.length }} questions correctly
                        </p>

                        <div class="bg-[#002B5C] text-white rounded-lg px-10 py-6 my-2 shadow-lg">
                            <div class="text-5xl font-black">{{ scorePercentage }}%</div>
                            <div class="text-sm opacity-80 uppercase tracking-wider mt-1">Your Score</div>
                        </div>

                        <div class="flex gap-4 mt-4 w-full justify-center">
                            <Link :href="route('quiz.history', quiz.id)">
                                <Button variant="secondary" class="bg-gray-100 text-[#002B5C] hover:bg-gray-200 border border-gray-200">
                                    <BarChart3 class="mr-2 h-4 w-4" /> History
                                </Button>
                            </Link>
                            
                            <Button @click="retryQuiz" class="bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200]">
                                <RotateCcw class="mr-2 h-4 w-4" /> Retake Quiz
                            </Button>
                        </div>
                        
                        <div class="mt-2">
                            <Link :href="route('quiz.index')">
                                <Button variant="link" class="text-gray-500 hover:text-[#002B5C]">
                                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to All Topics
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Card>
                
                <h3 class="text-[#FFD700] text-xl font-bold mt-8 mb-4">Question Review</h3>
            </div>

            <div class="w-full max-w-3xl flex flex-col gap-6 pb-10">
                <div v-for="(question, index) in quiz.questions" :key="question.id">
                    <Card class="border-none shadow-md overflow-hidden" 
                        :class="submitted ? 'bg-white text-[#002B5C]' : 'bg-[#004080] text-white'">
                        
                        <CardHeader class="pb-2">
                            <CardTitle class="text-lg font-medium flex items-start gap-3">
                                <div v-if="submitted">
                                    <CheckCircle v-if="answers[question.id] === question.correct_option_id" class="h-6 w-6 text-green-600 shrink-0" />
                                    <XCircle v-else class="h-6 w-6 text-red-600 shrink-0" />
                                </div>
                                <span :class="submitted ? 'text-[#002B5C]' : 'text-[#FFD700]'">
                                    Question {{ index + 1 }}
                                </span>
                            </CardTitle>
                            <p class="text-lg font-semibold mt-1 pl-9" :class="submitted ? 'text-gray-800' : 'text-white'">
                                {{ question.text }}
                            </p>
                        </CardHeader>

                        <CardContent class="pl-9">
                            <div class="flex flex-col gap-3">
                                <div v-for="option in question.options" :key="option.id" 
                                    class="relative flex items-center p-3 rounded-lg border transition-all"
                                    :class="[
                                        submitted && option.id === question.correct_option_id 
                                            ? 'bg-green-50 border-green-500 text-green-900' 
                                            : submitted && answers[question.id] === option.id && option.id !== question.correct_option_id
                                                ? 'bg-red-50 border-red-500 text-red-900' 
                                                : submitted 
                                                    ? 'bg-gray-50 border-gray-200 text-gray-400 opacity-70' 
                                                    : answers[question.id] === option.id 
                                                        ? 'bg-[#002B5C] border-[#FFD700]' 
                                                        : 'bg-[#002B5C]/50 border-transparent hover:border-gray-500'
                                    ]"
                                >
                                    <input type="radio" :name="`q-${question.id}`" :value="option.id"
                                        v-model="answers[question.id]" :disabled="submitted" class="peer sr-only" />
                                    
                                    <label class="flex items-center w-full cursor-pointer" @click="!submitted && (answers[question.id] = option.id)">
                                        <div v-if="!submitted" class="mr-3 h-5 w-5 rounded-full border-2 border-gray-400 flex items-center justify-center peer-checked:border-[#FFD700]">
                                            <div class="h-2.5 w-2.5 rounded-full bg-[#FFD700]" v-if="answers[question.id] === option.id"></div>
                                        </div>
                                        <div v-else class="mr-3">
                                            <Check v-if="option.id === question.correct_option_id" class="h-5 w-5 text-green-600" />
                                            <span v-else-if="answers[question.id] === option.id" class="text-red-600 font-bold">✕</span>
                                        </div>
                                        <span class="font-medium">
                                            {{ option.text }} 
                                            <span v-if="submitted && option.id === question.correct_option_id" class="ml-2 text-xs font-bold text-green-600 uppercase tracking-wider">✓ Correct Answer</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div v-if="submitted && question.explanation" class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-md">
                                <div class="flex items-start gap-3">
                                    <Info class="h-5 w-5 text-blue-600 mt-0.5 shrink-0" />
                                    <div>
                                        <p class="text-sm font-bold text-blue-800 mb-1">Feedback:</p>
                                        <p class="text-sm text-blue-700 leading-relaxed">{{ question.explanation }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div v-if="!submitted" class="mt-4 flex justify-center">
                    <Button @click="() => submitQuiz(false)" class="w-full md:w-auto bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200] font-bold px-12 py-6 text-lg shadow-lg hover:shadow-xl transition-all active:scale-95">
                        Submit Answers <CheckCircle class="ml-2 h-5 w-5" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>