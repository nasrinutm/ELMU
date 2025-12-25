<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { MessageCircle, X, Send, Bot, User, RefreshCw } from 'lucide-vue-next'; 
import { route } from 'ziggy-js';
import axios from 'axios';

// State
const isOpen = ref(false);
const isLoading = ref(false);
const newMessage = ref('');
const messages = ref<{ from: 'user' | 'ai'; text: string; isError?: boolean }[]>([
    { from: 'ai', text: 'Hai! Saya adalah pembantu AI anda. Bagaimana saya boleh membantu anda hari ini?' }
]);
const messagesContainer = ref<HTMLElement | null>(null);

// Toggle chat window
const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) scrollToBottom();
};

// Auto-scroll to bottom of chat
const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

// Send message logic
const sendMessage = async () => {
    const text = newMessage.value.trim();
    if (!text || isLoading.value) return;

    // 1. Add User Message immediately
    messages.value.push({ from: 'user', text: text });
    newMessage.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        // FIX: Manually grab the token from the meta tag if cookies fail
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // 2. Call Laravel Backend with explicit headers
        const response = await axios.post(route('chat.send'), {
            message: text
        }, {
            withCredentials: true, // Still required for session cookies
            headers: {
                'X-Requested-With': 'XMLHttpRequest', // vital for Laravel to return JSON errors
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}) // Explicitly attach token
            }
        });

        // 3. Add AI Response
        messages.value.push({ from: 'ai', text: response.data.reply });

    } catch (error: any) {
        console.error('Chat error:', error);

        let errorMessage = 'Sorry, something went wrong. Please try again.';

        // Specific handling for 419 (Session Expired/CSRF)
        if (error.response && error.response.status === 419) {
            errorMessage = 'Session expired. Please refresh the page to reconnect.';
        }

        messages.value.push({ 
            from: 'ai', 
            text: errorMessage,
            isError: true
        });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end space-y-4 font-sans">
        
        <!-- Chat Window -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div 
                v-if="isOpen" 
                class="w-80 md:w-96 h-[500px] bg-white dark:bg-zinc-900 border dark:border-zinc-700 rounded-2xl shadow-2xl flex flex-col overflow-hidden"
            >
                <!-- Header -->
                <div class="bg-blue-600 text-white p-4 flex justify-between items-center shadow-sm shrink-0">
                    <div class="flex items-center space-x-2">
                        <Bot class="w-6 h-6" />
                        <h3 class="font-bold">LMS Assistant</h3>
                    </div>
                    <button @click="toggleChat" class="hover:bg-blue-700 p-1 rounded transition">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Messages Area -->
                <div 
                    ref="messagesContainer" 
                    class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-zinc-950"
                >
                    <div 
                        v-for="(msg, index) in messages" 
                        :key="index" 
                        class="flex items-start gap-3"
                        :class="msg.from === 'user' ? 'flex-row-reverse' : ''"
                    >
                        <!-- Icon -->
                        <div 
                            class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-xs"
                            :class="[
                                msg.from === 'user' ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600',
                                msg.isError ? '!bg-red-100 !text-red-600' : ''
                            ]"
                        >
                            <User v-if="msg.from === 'user'" class="w-5 h-5" />
                            <Bot v-else class="w-5 h-5" />
                        </div>

                        <!-- Bubble -->
                        <div 
                            class="px-4 py-2 rounded-2xl text-sm max-w-[85%] shadow-sm leading-relaxed"
                            :class="[
                                msg.from === 'user' 
                                    ? 'bg-blue-600 text-white rounded-tr-none' 
                                    : 'bg-white dark:bg-zinc-800 dark:text-gray-100 border border-gray-100 dark:border-zinc-700 rounded-tl-none',
                                msg.isError ? '!bg-red-50 !text-red-600 !border-red-200' : ''
                            ]"
                        >
                            {{ msg.text }}
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div v-if="isLoading" class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <Bot class="w-5 h-5" />
                        </div>
                        <div class="bg-white dark:bg-zinc-800 border border-gray-100 dark:border-zinc-700 px-4 py-3 rounded-2xl rounded-tl-none shadow-sm">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-4 bg-white dark:bg-zinc-900 border-t border-gray-100 dark:border-zinc-800 shrink-0">
                    <form @submit.prevent="sendMessage" class="relative">
                        <input
                            v-model="newMessage"
                            type="text"
                            placeholder="Ask me anything..."
                            class="w-full pl-4 pr-12 py-3 rounded-full border border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm transition-all"
                            :disabled="isLoading"
                        />
                        <button 
                            type="submit"
                            :disabled="!newMessage.trim() || isLoading"
                            class="absolute right-2 top-1.5 p-1.5 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <Send class="w-4 h-4" />
                        </button>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Floating Toggle Button -->
        <button 
            @click="toggleChat" 
            class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-lg transition-transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-blue-300"
        >
            <MessageCircle v-if="!isOpen" class="w-7 h-7" />
            <X v-else class="w-7 h-7" />
        </button>
    </div>
</template>