<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Bot, Save } from 'lucide-vue-next';

// Props from Controller
const props = defineProps({
    currentInstruction: String,
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'AI Details', href: route('chatbot.details') },
    { title: 'Edit Prompt', href: route('chatbot.prompt.edit') },
];

const form = useForm({
    instruction: props.currentInstruction,
});

const submit = () => {
    form.put(route('chatbot.prompt.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: Show a temporary success message via flash/toast if implemented
        }
    });
};
</script>

<template>
    <Head title="Edit AI Prompt" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 max-w-4xl mx-auto w-full">
            
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 ">
                        <Bot class="w-6 h-6 inline mr-2 text-blue-600" />
                        AI Instruction Prompt
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Customize the strict instruction given to the AI before every query.
                    </p>
                </div>
                <Link 
                    :href="route('chatbot.details')" 
                    class="flex items-center gap-2 text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg text-sm font-medium transition border border-gray-300"
                >
                    Back to AI Details
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label for="instruction" class="block text-sm font-medium text-gray-700">
                                Strict Instruction Prompt
                            </label>
                            <textarea
                                id="instruction"
                                v-model="form.instruction" 
                                rows="8"
                                autocomplete="off"
                                spellcheck="false"
                                data-1p-ignore
                                data-lpignore="true"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-3 bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="e.g., Answer in Malay. Provide a concise answer in a numbered list."
                            ></textarea>
                            <p class="mt-2 text-xs text-gray-500">
                                This text is prepended to the user's question to control the AI's output format and tone.
                            </p>
                            <div v-if="form.errors.instruction" class="text-red-500 text-xs mt-1">{{ form.errors.instruction }}</div>
                        </div>

                        <div v-if="$page.props.flash &&$page.props.flash.success" class="p-3 rounded-md bg-green-50 text-green-700 text-sm font-medium">
                            {{ $page.props.flash.success }}
                        </div>

                        <div class="flex justify-end">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex items-center gap-2 bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-medium shadow-md"
                            >
                                <Save class="w-4 h-4" />
                                {{ form.processing ? 'Saving...' : 'Save Instruction' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>