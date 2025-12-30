<script setup lang="ts">
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { 
    Gamepad2, CheckCircle, ChevronLeft, Clock, Trophy, 
    User as UserIcon, Download, PencilLine, Trash2, X 
} from 'lucide-vue-next';

const props = defineProps<{
    student: { id: number; name: string; email: string; };
    activities: any[];
    quizzes: any[];
    existingReport: any | null;
}>();

const page = usePage();
const teacherName = page.props.auth.user.name;

const isModalOpen = ref(false);
const form = useForm({
    student_id: props.student.id,
    comments: props.existingReport ? props.existingReport.comments : '',
});

// Watch for changes (e.g., after a successful save) to update the local form state
watch(() => props.existingReport, (newVal) => {
    form.comments = newVal ? newVal.comments : '';
});

const submitRemark = () => {
    form.post(route('reports.remark.save'), { 
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};

const deleteRemark = () => {
    if (!props.existingReport) return;

    if (confirm('Delete this evaluation?')) {
        form.delete(route('reports.remark.delete', props.existingReport.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.comments = ''; // Clear local form on delete
            }
        });
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};

const generatePDF = () => window.print();
</script>

<template>
    <Head :title="student.name + ' - Performance'" />
    <AppLayout>
        <div class="min-h-screen bg-[#001f3f] text-white p-print">
            <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="flex justify-between items-center mb-6 no-print">
                    <Link :href="route('reports.index')" class="flex items-center text-sm text-gray-400 hover:text-[#ffcc00]">
                        <ChevronLeft class="w-4 h-4 mr-1" /> Back to Directory
                    </Link>
                    <div class="flex gap-3">
                        <button @click="isModalOpen = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2 hover:bg-blue-500 transition-colors">
                            <PencilLine class="w-4 h-4" /> {{ existingReport ? 'Edit Remark' : '' }}
                        </button>
                        <button @click="generatePDF" class="bg-[#ffcc00] text-[#001f3f] px-4 py-2 rounded-lg font-bold flex items-center gap-2 hover:bg-yellow-400 transition-colors">
                            <Download class="w-4 h-4" /> 
                        </button>
                    </div>
                </div>

                <div class="bg-[#0a192f] rounded-xl border border-white/10 p-6 mb-8 border-print shadow-lg">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div class="flex items-center gap-4">
                            <div class="bg-blue-500/20 p-4 rounded-full no-print">
                                <UserIcon class="w-8 h-8 text-blue-400" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-[#ffcc00] print-black">{{ student.name }}</h1>
                                <p class="text-gray-400">{{ student.email }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4 no-print">
                            <div class="bg-[#001f3f] border border-white/10 rounded-lg p-3 min-w-[100px] text-center">
                                <span class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Activities</span>
                                <span class="text-2xl font-bold text-blue-400">{{ activities.length }}</span>
                            </div>
                            <div class="bg-[#001f3f] border border-white/10 rounded-lg p-3 min-w-[100px] text-center">
                                <span class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Quizzes</span>
                                <span class="text-2xl font-bold text-[#ffcc00]">{{ quizzes.length }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="existingReport" class="mt-6 p-5 bg-[#001f3f] rounded-lg border-l-4 border-[#ffcc00] relative">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[#ffcc00] text-xs font-bold uppercase mb-2">Official Teacher Evaluation</h3>
                            <button @click="deleteRemark" class="no-print text-gray-500 hover:text-red-500 transition-colors" title="Delete Evaluation">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                        <p class="text-gray-200 italic font-medium whitespace-pre-wrap">"{{ existingReport.comments }}"</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    <div class="bg-[#0a192f] rounded-xl border border-white/10 overflow-hidden shadow-xl border-print">
                        <div class="p-5 border-b border-white/10 bg-[#0d223f] flex items-center gap-2">
                            <Gamepad2 class="w-5 h-5 text-blue-400 no-print" />
                            <h2 class="text-lg font-bold text-[#ffcc00] print-black">Activities</h2>
                        </div>
                        <ul v-if="activities.length > 0" class="divide-y divide-white/5">
                            <li v-for="act in activities" :key="act.id" class="p-4 flex justify-between">
                                <span class="text-gray-200 print-black font-medium">{{ act.title }}</span>
                                <span class="text-xs text-gray-500">{{ formatDate(act.completed_at) }}</span>
                            </li>
                        </ul>
                        <div v-else class="p-10 text-center text-gray-500 italic">No activities recorded.</div>
                    </div>

                    <div class="bg-[#0a192f] rounded-xl border border-white/10 overflow-hidden shadow-xl border-print">
                        <div class="p-5 border-b border-white/10 bg-[#0d223f] flex items-center gap-2">
                            <CheckCircle class="w-5 h-5 text-green-400 no-print" />
                            <h2 class="text-lg font-bold text-[#ffcc00] print-black">Quizzes</h2>
                        </div>
                        <ul v-if="quizzes.length > 0" class="divide-y divide-white/5">
                            <li v-for="quiz in quizzes" :key="quiz.id" class="p-4 flex justify-between">
                                <span class="text-gray-200 print-black font-medium">{{ quiz.title }}</span>
                                <div class="text-[#ffcc00] font-bold print-black">{{ quiz.score }}%</div>
                            </li>
                        </ul>
                        <div v-else class="p-10 text-center text-gray-500 italic">No quizzes recorded.</div>
                    </div>
                </div>

                <div class="hidden print:flex justify-between mt-20 pt-10 border-t border-gray-300 text-black">
                    <div>
                        <p class="font-bold text-sm">Prepared by:</p>
                        <div class="h-16 mt-2 border-b border-black w-48"></div>
                        <p class="mt-2 text-sm font-semibold">{{ teacherName }}</p>
                        <p class="text-xs text-gray-500">Teacher</p>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Verified by:</p>
                        <div class="h-16 mt-2 border-b border-black w-48"></div>
                        <p class="mt-2 text-sm font-semibold">Principal / Head of Department</p>
                        <p class="text-xs text-gray-500">Date: ________________</p>
                    </div>
                </div>

            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm">
            <div class="bg-[#0a192f] w-full max-w-lg rounded-xl border border-white/20 shadow-2xl">
                <div class="p-6 border-b border-white/10 flex justify-between items-center bg-[#0d223f]">
                    <h2 class="text-xl font-bold text-[#ffcc00]">Teacher Evaluation</h2>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-white transition-colors"><X /></button>
                </div>
                <form @submit.prevent="submitRemark" class="p-6">
                    <textarea 
                        v-model="form.comments" 
                        rows="6" 
                        class="w-full bg-[#001f3f] border border-white/10 rounded-lg text-white p-4 focus:ring-2 focus:ring-[#ffcc00] focus:outline-none" 
                        placeholder="Enter remarks..."
                        required
                    ></textarea>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="isModalOpen = false" class="text-gray-400 hover:text-white transition-colors">Cancel</button>
                        <button 
                            type="submit" 
                            class="bg-[#ffcc00] text-[#001f3f] px-6 py-2 rounded-lg font-bold hover:bg-yellow-400 transition-colors disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<!-- <style>
@media print {
    .no-print, button, .backdrop-blur-sm { display: none !important; }
    body { background: white !important; }
    .bg-[#001f3f], .bg-[#0a192f], .bg-[#0d223f] { background: transparent !important; color: black !important; box-shadow: none !important; }
    .text-white, .text-gray-200, .text-gray-400, .text-[#ffcc00] { color: black !important; }
    .print-black { color: black !important; }
    .border-print { border: 1px solid #ddd !important; border-radius: 8px !important; margin-bottom: 20px !important; }
    
    /* Show signature only in print */
    .print\:flex { display: flex !important; }
    .hidden { display: none; }
}
</style> -->

<style scoped> /* Added scoped for better practice */
@media print {
    .no-print, button, .backdrop-blur-sm { display: none !important; }
    body { background: white !important; }
    
    /* Use standard class names or attribute selectors to avoid escape issues */
    .bg-\[\#001f3f\], .bg-\[\#0a192f\], .bg-\[\#0d223f\] { 
        background: transparent !important; 
        color: black !important; 
        box-shadow: none !important; 
    }
    
    .text-white, .text-gray-200, .text-gray-400, .text-\[\#ffcc00\] { 
        color: black !important; 
    }
    
    .print-black { color: black !important; }
    
    .border-print { 
        border: 1px solid #ddd !important; 
        border-radius: 8px !important; 
        margin-bottom: 20px !important; 
    }
    
    /* Target the element directly or use a simpler class if VS Code keeps complaining */
    [class*="print:flex"] { 
        display: flex !important; 
    }
}
</style>