<script setup lang="ts">
import { ref, nextTick, computed, onMounted, onUnmounted } from 'vue';
import { Bot, X, Send, User } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import axios from 'axios';

// --- STATE ---
const page = usePage();
const isOpen = ref(false);
const isLoading = ref(false);
const newMessage = ref('');
const messages = ref<{ from: 'user' | 'ai'; text: string; isError?: boolean }[]>([
    { from: 'ai', text: 'Hai! Saya adalah pembantu AI anda. Bagaimana saya boleh membantu anda hari ini?' }
]);
const messagesContainer = ref<HTMLElement | null>(null);

// --- RESIZE STATE ---
// We start with pixels, but we will use CSS constraints to handle the zoom automatically
const width = ref(384);  
const height = ref(600); 

const dynamicIcon = computed(() => Bot);

// --- RESIZE LOGIC (FIREFOX OPTIMIZED) ---
const initResize = (e: MouseEvent) => {
    e.preventDefault();
    
    const startX = e.clientX;
    const startY = e.clientY;
    const startWidth = width.value;
    const startHeight = height.value;

    const doResize = (moveEvent: MouseEvent) => {
        // Calculate the delta movement
        const deltaX = moveEvent.clientX - startX;
        const deltaY = moveEvent.clientY - startY;

        // Since the box is fixed to bottom-right, moving mouse LEFT increases width
        // and moving mouse UP increases height.
        width.value = Math.max(320, startWidth - deltaX);
        height.value = Math.max(400, startHeight - deltaY);
    };

    const stopResize = () => {
        window.removeEventListener('mousemove', doResize);
        window.removeEventListener('mouseup', stopResize);
        document.body.style.cursor = 'default';
    };

    window.addEventListener('mousemove', doResize);
    window.addEventListener('mouseup', stopResize);
    document.body.style.cursor = 'nwse-resize';
};

// --- CHAT LOGIC ---
const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) scrollToBottom();
};

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior: 'smooth'
        });
    }
};

const sendMessage = async () => {
    const text = newMessage.value.trim();
    if (!text || isLoading.value) return;

    messages.value.push({ from: 'user', text: text });
    newMessage.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await axios.post(route('chat.send'), { message: text }, {
            withCredentials: true,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
            }
        });
        messages.value.push({ from: 'ai', text: response.data.reply });
    } catch (error: any) {
        console.error('Chat error:', error);
        let errorMessage = 'Maaf, sesuatu yang tidak kena berlaku. Sila cuba lagi.';
        if (error.response?.status === 419) errorMessage = 'Sesi telah tamat. Sila muat semula halaman.';
        messages.value.push({ from: 'ai', text: errorMessage, isError: true });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end space-y-4 font-sans no-print">

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div
                v-if="isOpen"
                :style="{ 
                    width: width + 'px', 
                    height: height + 'px' 
                }"
                class="relative bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-gray-200 
                       max-w-[calc(100vw-3rem)] max-h-[calc(100vh-8rem)] min-w-[320px] min-h-[400px]"
            >
                <div 
                    class="absolute top-0 left-0 w-8 h-8 cursor-nwse-resize z-50 flex items-center justify-center group"
                    @mousedown="initResize"
                >
                    <div class="w-3 h-3 border-t-2 border-l-2 border-gray-400 group-hover:border-action transition-colors rounded-tl-sm"></div>
                </div>

                <div class="bg-action text-white p-4 flex justify-between items-center shadow-sm shrink-0">
                    <div class="flex items-center space-x-2 text-white ml-2">
                        <component :is="dynamicIcon" class="w-6 h-6" />
                        <h3 class="font-bold text-lg tracking-tight uppercase">ELMU-Bot</h3>
                    </div>
                    <button @click="toggleChat" class="hover:bg-white/20 p-1.5 rounded-full transition-colors">
                        <X class="w-5 h-5 text-white" />
                    </button>
                </div>

                <div
                    ref="messagesContainer"
                    class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50"
                >
                    <div
                        v-for="(msg, index) in messages"
                        :key="index"
                        class="flex items-start gap-3"
                        :class="msg.from === 'user' ? 'flex-row-reverse' : ''"
                    >
                        <div
                            class="w-8 h-8 flex items-center justify-center shrink-0 shadow-sm border rounded-full"
                            :class="[
                                msg.from === 'user'
                                    ? 'bg-action/10 text-action border-action/20'
                                    : 'bg-white text-emerald-600 border-gray-200',
                                msg.isError ? '!bg-red-100 !text-red-600 !border-red-200' : ''
                            ]"
                        >
                            <User v-if="msg.from === 'user'" class="w-4 h-4" />
                            <component :is="dynamicIcon" v-else class="w-4 h-4" />
                        </div>

                        <div
                            class="px-4 py-2.5 rounded-2xl text-sm max-w-[85%] shadow-sm leading-relaxed whitespace-pre-wrap"
                            :class="[
                                msg.from === 'user'
                                    ? 'bg-action text-white rounded-tr-none'
                                    : 'bg-white border border-gray-100 rounded-tl-none text-slate-700',
                                msg.isError ? '!bg-red-50 !text-red-600 !border-red-200' : ''
                            ]"
                            v-html="msg.text"
                        >
                        </div>
                    </div>

                    <div v-if="isLoading" class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-white border border-gray-200 text-emerald-600 flex items-center justify-center shrink-0 shadow-sm rounded-full">
                            <component :is="dynamicIcon" class="w-4 h-4" />
                        </div>
                        <div class="bg-white border border-gray-100 px-4 py-3 rounded-2xl rounded-tl-none shadow-sm flex space-x-1.5 text-slate-700">
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white border-t border-gray-100 shrink-0">
                    <form @submit.prevent="sendMessage" class="relative">
                        <input
                            v-model="newMessage"
                            type="text"
                            placeholder="Tanya saya apa-apa..."
                            class="w-full pl-4 pr-12 py-3 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-action transition-all shadow-inner"
                            :disabled="isLoading"
                        />
                        <button
                            type="submit"
                            :disabled="!newMessage.trim() || isLoading"
                            class="absolute right-2 top-2 p-2 text-action hover:bg-action/5 rounded-lg transition-colors disabled:opacity-30"
                        >
                            <Send class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>
        </transition>

        <button
            @click="toggleChat"
            class="w-14 h-14 bg-action hover:brightness-110 text-white flex items-center justify-center shadow-2xl transition-all hover:scale-110 active:scale-95 border-2 border-white rounded-full focus:outline-none ring-offset-2 focus:ring-2 focus:ring-action"
        >
            <component :is="isOpen ? X : dynamicIcon" class="w-7 h-7 text-white" />
        </button>
    </div>
</template>

<style scoped>
.cursor-nwse-resize {
    user-select: none;
}
/* Ensure the transition doesn't jitter during resize */
.transition {
    transition-property: opacity, transform;
}
</style>