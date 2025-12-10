<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Play, RefreshCw, Trophy, Crown, XCircle } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const props = defineProps<{
    activity: any;
    leaderboard: any[]; 
}>();

// --- GAME STATE ---
const isPlaying = ref(false);
const isGameOver = ref(false);
const showWrongModal = ref(false);
const isWaveSpawning = ref(false); // <--- CRITICAL FIX: Prevents infinite spawning
const score = ref(0);
const lives = ref(3);
const currentQuestionIndex = ref(0);
const gameLoop = ref<any>(null);

// --- PLAYER ---
const playerLane = ref(1); 
const maxLanes = 4;

// --- FALLING ITEMS ---
const fallingItems = ref<any[]>([]);
const fallSpeed = 3; 

// --- PARSE QUESTIONS ---
const questions = computed(() => {
    return props.activity.quiz_data ? JSON.parse(props.activity.quiz_data) : [];
});

const currentQuestion = computed(() => {
    if (currentQuestionIndex.value >= questions.value.length) return null;
    return questions.value[currentQuestionIndex.value];
});

// --- CONTROLS ---
const moveLeft = () => { if (playerLane.value > 0 && !showWrongModal.value) playerLane.value--; };
const moveRight = () => { if (playerLane.value < maxLanes - 1 && !showWrongModal.value) playerLane.value++; };

const handleKeydown = (e: KeyboardEvent) => {
    if (!isPlaying.value || showWrongModal.value) return;
    if (e.key === 'ArrowLeft') moveLeft();
    if (e.key === 'ArrowRight') moveRight();
};

// --- GAME LOGIC ---
const startGame = () => {
    isPlaying.value = true;
    isGameOver.value = false;
    showWrongModal.value = false;
    isWaveSpawning.value = false;
    score.value = 0;
    lives.value = 3;
    currentQuestionIndex.value = 0;
    fallingItems.value = [];
    playerLane.value = 1;
    
    spawnWave();
    gameLoop.value = setInterval(updateGame, 20); // 50 FPS
};

const spawnWave = () => {
    if (!currentQuestion.value) return;
    
    // Prevent double spawning
    isWaveSpawning.value = true;

    const options = currentQuestion.value.options;
    const correctIndex = currentQuestion.value.correct_answer;
    
    // Create items for A, B, C, D
    options.forEach((opt: string, index: number) => {
        fallingItems.value.push({
            id: Math.random(),
            text: opt,
            lane: index,
            // FIX: Start exactly same height, off-screen
            y: -150,  
            isCorrect: index === parseInt(correctIndex),
            hit: false
        });
    });

    // Reset spawn lock after a moment
    setTimeout(() => {
        isWaveSpawning.value = false;
    }, 1000);
};

const updateGame = () => {
    if (isGameOver.value || showWrongModal.value) return;

    // Move Items
    fallingItems.value.forEach(item => {
        item.y += fallSpeed;
    });

    // Collision Detection
    fallingItems.value.forEach(item => {
        // Player Hitbox (approx y=480 to 560)
        if (!item.hit && item.y > 480 && item.y < 560) { 
            if (item.lane === playerLane.value) {
                item.hit = true; 
                handleCollision(item);
            }
        }
    });

    // Clean up off-screen items
    const initialCount = fallingItems.value.length;
    fallingItems.value = fallingItems.value.filter(item => item.y < 650);

    // LOGIC: If items fell off screen (Missed), respawn same question
    if (fallingItems.value.length === 0 && initialCount > 0 && !showWrongModal.value && !isGameOver.value) {
        // Missed the answer? Lose heart and retry
        lives.value--;
        if (lives.value <= 0) {
            endGame();
        } else {
            // Respawn same question
            if (!isWaveSpawning.value) spawnWave();
        }
    } 
    // Logic: If successfully cleared (caught correct one), next spawn handled in handleCollision
};

const handleCollision = (item: any) => {
    if (item.isCorrect) {
        // CORRECT
        score.value += 100;
        currentQuestionIndex.value++;
        fallingItems.value = []; // Clear screen
        
        if (currentQuestionIndex.value >= questions.value.length) {
            endGame(); // Win
        } else {
            // Spawn next question after short delay
            if (!isWaveSpawning.value) {
                isWaveSpawning.value = true;
                setTimeout(() => {
                    isWaveSpawning.value = false;
                    spawnWave();
                }, 500);
            }
        }
    } else {
        // WRONG
        lives.value--; 
        clearInterval(gameLoop.value); // Pause

        if (lives.value <= 0) {
            endGame(); 
        } else {
            showWrongModal.value = true; 
        }
    }
};

const retryCurrentLevel = () => {
    showWrongModal.value = false; 
    fallingItems.value = []; 
    playerLane.value = 1; 
    
    // Resume
    gameLoop.value = setInterval(updateGame, 20);
    spawnWave();
};

const endGame = () => {
    clearInterval(gameLoop.value);
    isPlaying.value = false;
    isGameOver.value = true;
    showWrongModal.value = false;

    router.post(route('activities.score', props.activity.id), {
        score: score.value
    }, {
        preserveScroll: true,
        preserveState: false 
    });
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    clearInterval(gameLoop.value);
});
</script>

<template>
    <Head title="Play Game" />

    <AppLayout> <div class="min-h-screen bg-gray-900 flex flex-col md:flex-row items-center justify-center font-sans relative overflow-hidden p-4 gap-8">
            </div>
    </AppLayout>
    
    <div class="min-h-screen bg-gray-900 flex flex-col md:flex-row items-center justify-center font-sans relative overflow-hidden p-4 gap-8">
        
        <div class="absolute inset-0 bg-[#0a0a1a]"></div>
        <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 30px 30px;"></div>

        <div class="relative w-full max-w-2xl h-[600px] border-4 border-blue-900/50 bg-gray-800/80 shadow-2xl overflow-hidden rounded-xl order-2 md:order-1 backdrop-blur-sm">
            
            <div class="absolute top-0 w-full p-4 flex justify-between text-white z-20 bg-gradient-to-b from-black/60 to-transparent">
                <Link :href="route('activities.index')" class="flex items-center hover:text-blue-400 transition">
                    <ArrowLeft class="w-6 h-6 mr-2" /> Exit
                </Link>
                <div class="text-xl font-bold text-yellow-400 font-mono">Score: {{ score }}</div>
                <div class="flex gap-1">
                    <span v-for="n in 3" :key="n" class="text-2xl transition-all duration-300" :class="n <= lives ? 'text-red-500 scale-100' : 'text-gray-600 scale-75 grayscale opacity-50'">❤️</span>
                </div>
            </div>

            <div v-if="!isPlaying && !isGameOver" class="absolute inset-0 flex flex-col items-center justify-center z-30 bg-gray-900/95">
                <h1 class="text-5xl font-extrabold text-white mb-4 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-purple-500">
                    🚀 KNOWLEDGE RUN
                </h1>
                <p class="text-gray-300 mb-10 text-lg">Catch the correct answer.</p>
                
                <div class="flex flex-col gap-6 w-full max-w-xs">
                    <Button @click="startGame" class="bg-blue-600 hover:bg-blue-500 text-white py-6 text-xl rounded-full shadow-lg shadow-blue-500/30 hover:scale-105 transition w-full flex justify-center items-center">
                        <Play class="w-6 h-6 mr-2 fill-current" /> START GAME
                    </Button>

                    <Link :href="route('activities.index')" class="w-full">
                        <Button class="bg-transparent border-2 border-gray-600 text-gray-400 hover:text-white hover:border-gray-400 hover:bg-gray-800 py-4 rounded-full text-lg w-full transition flex justify-center items-center">
                            <Home class="w-5 h-5 mr-2" /> Back to Class
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="showWrongModal" class="absolute inset-0 flex flex-col items-center justify-center z-40 bg-red-950/95 backdrop-blur-md animate-in fade-in zoom-in duration-200">
                <XCircle class="w-24 h-24 text-red-500 mb-4 drop-shadow-[0_0_15px_rgba(239,68,68,0.5)]" />
                <h2 class="text-4xl font-bold text-white mb-2">WRONG ANSWER!</h2>
                <div class="flex gap-4 mt-6">
                    <Button @click="retryCurrentLevel" class="bg-white hover:bg-gray-100 text-red-600 font-bold px-8 py-4 rounded-full shadow-lg text-lg">
                        <RefreshCw class="w-5 h-5 mr-2" /> TRY AGAIN
                    </Button>
                </div>
            </div>

            <div v-if="isGameOver" class="absolute inset-0 flex flex-col items-center justify-center z-30 bg-gray-900/95">
                <h1 class="text-5xl font-bold mb-4" :class="lives > 0 ? 'text-green-400' : 'text-red-500'">
                    {{ lives > 0 ? '🎉 MISSION COMPLETE!' : '💀 GAME OVER' }}
                </h1>
                <p class="text-white text-3xl mb-8 font-bold font-mono">Final Score: {{ score }}</p>
                <div class="flex gap-4">
                    <Button @click="startGame" class="bg-green-600 hover:bg-green-500 text-white px-8 py-4 rounded-full text-lg">
                        <RefreshCw class="w-5 h-5 mr-2" /> Play Again
                    </Button>
                    <Link :href="route('activities.index')">
                        <Button class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-4 rounded-full text-lg">
                            Exit
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="isPlaying">
                <div class="absolute top-20 left-0 w-full text-center px-4 z-10">
                    <div class="bg-slate-800/90 backdrop-blur-md text-white py-4 px-8 rounded-2xl shadow-xl inline-block text-xl font-bold border border-slate-600/50 max-w-xl">
                        {{ currentQuestion?.question_text }}
                    </div>
                </div>

                <div class="absolute inset-0 grid grid-cols-4 pointer-events-none">
                    <div class="border-r border-blue-500/10 h-full"></div>
                    <div class="border-r border-blue-500/10 h-full"></div>
                    <div class="border-r border-blue-500/10 h-full"></div>
                </div>

                <div v-for="item in fallingItems" :key="item.id" 
                    class="absolute w-[22%] h-24 flex items-center justify-center text-center p-2 rounded-xl shadow-md border-b-4 transition-transform font-bold text-lg z-10 bg-indigo-600 border-indigo-800 text-white"
                    :style="{ left: (item.lane * 25) + 1.5 + '%', top: item.y + 'px' }">
                    {{ item.text }}
                </div>

                <div class="absolute bottom-4 w-[22%] h-24 bg-yellow-400 rounded-xl shadow-[0_0_25px_rgba(250,204,21,0.6)] flex items-center justify-center transition-all duration-150 ease-out border-b-4 border-yellow-600 z-20"
                    :style="{ left: (playerLane * 25) + 1.5 + '%' }">
                    <span class="text-5xl filter drop-shadow-lg transform -translate-y-1">🛸</span>
                </div>
            </div>
        </div>

        <div class="w-full md:w-80 bg-gray-800/80 backdrop-blur-sm rounded-xl border border-blue-900/50 p-6 shadow-xl h-[600px] flex flex-col order-1 md:order-2">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-700/50 pb-4">
                <Trophy class="w-6 h-6 text-yellow-500" />
                <h2 class="text-xl font-bold text-white">Leaderboard</h2>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 custom-scrollbar pr-2">
                <div v-for="(entry, index) in leaderboard" :key="index" 
                     class="flex items-center justify-between p-3 rounded-lg border border-gray-700/50 bg-gray-700/30 transition">
                    <div class="flex items-center gap-3">
                        <div class="font-bold text-lg w-8 text-center font-mono text-yellow-400">#{{ index + 1 }}</div>
                        <div class="text-gray-200 font-medium truncate max-w-[120px]">
                            {{ entry.name }}
                            <Crown v-if="index === 0" class="w-4 h-4 text-yellow-500 inline ml-2" />
                        </div>
                    </div>
                    <div class="font-mono text-yellow-400 font-bold text-lg">{{ entry.score }}</div>
                </div>
                <div v-if="leaderboard.length === 0" class="text-gray-500 text-center italic mt-10">No scores yet.</div>
            </div>
        </div>
    </div>

</template>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(31, 41, 55, 0.5); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(75, 85, 99, 0.8); border-radius: 4px; }
</style>