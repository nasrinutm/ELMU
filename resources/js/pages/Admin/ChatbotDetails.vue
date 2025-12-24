<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Server, Database, Cpu, Trash2, FileText, Plus } from 'lucide-vue-next';

// Props from Controller
const props = defineProps({
    files: Array,
    systemInfo: Object,
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'AI Details', href: route('chatbot.details') },
];

// Delete Confirmation
const deleteFile = (fileName) => {
    if (confirm('Are you sure? The AI will forget this document immediately.')) {
        router.delete(route('upload.delete', fileName), {
            preserveScroll: true,
        });
    }
};

// Helper to format bytes
const formatBytes = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="AI Chatbot Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 max-w-7xl mx-auto w-full">
            
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI System Status</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Monitor the model and knowledge base.</p>
                </div>
                <Link 
                    :href="route('upload.create')" 
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                >
                    <Plus class="w-4 h-4" />
                    Add New Material
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl flex items-center gap-4 shadow-sm">
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
                        <Cpu class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Model</p>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ systemInfo.model }}</h3>
                    </div>
                </div>

                <div class="p-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl flex items-center gap-4 shadow-sm">
                    <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                        <Database class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Knowledge Store</p>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate w-32" :title="systemInfo.store_id">
                            {{ systemInfo.store_id.substring(0, 15) }}...
                        </h3>
                    </div>
                </div>

                <div class="p-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl flex items-center gap-4 shadow-sm">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                        <FileText class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Indexed Documents</p>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ files.length }} Files</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                    <h3 class="font-bold text-gray-900 dark:text-white">Active Knowledge Base</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-zinc-800/50 text-gray-500 dark:text-gray-400 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3">File Name</th>
                                <th class="px-6 py-3">Size</th>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Uploaded</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                            <tr v-for="file in files" :key="file.name" class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ file.display_name }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    {{ formatBytes(file.size_bytes) }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-zinc-700 rounded text-xs">
                                        {{ file.mime_type.split('/')[1] || 'File' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span 
                                        class="px-2 py-1 rounded-full text-xs font-bold"
                                        :class="file.state === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                    >
                                        {{ file.state }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    {{ file.created_at }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        @click="deleteFile(file.name)" 
                                        class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                        title="Delete from AI"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="files.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No files found. The AI has no custom knowledge yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>