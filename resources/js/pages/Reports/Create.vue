<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{ students: Array<{id: number, name: string}> }>();

const form = useForm({
    student_id: '',
    subject: '',
    score: '',
    comments: ''
});

const submit = () => {
    form.post(route('reports.store'));
};
</script>

<template>
    <Head title="Create Report" />
    <AppLayout>
        <div class="min-h-screen bg-[#001f3f] p-8">
            <div class="max-w-2xl mx-auto bg-[#0a192f] border border-white/10 rounded-xl p-8">
                <h1 class="text-2xl font-bold text-[#ffcc00] mb-6">Create Student Report</h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-[#ffcc00] mb-2">Select Student</label>
                        <select v-model="form.student_id" class="w-full bg-[#001f3f] text-white border-white/20 rounded-lg focus:border-[#ffcc00]">
                            <option value="" disabled>Choose a student</option>
                            <option v-for="student in students" :key="student.id" :value="student.id">
                                {{ student.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[#ffcc00] mb-2">Subject</label>
                        <input v-model="form.subject" type="text" class="w-full bg-[#001f3f] text-white border-white/20 rounded-lg focus:border-[#ffcc00]" placeholder="e.g. Mathematics">
                    </div>

                    <div>
                        <label class="block text-[#ffcc00] mb-2">Score</label>
                        <input v-model="form.score" type="number" class="w-full bg-[#001f3f] text-white border-white/20 rounded-lg focus:border-[#ffcc00]" placeholder="0-100">
                    </div>

                    <div>
                        <label class="block text-[#ffcc00] mb-2">Comments</label>
                        <textarea v-model="form.comments" class="w-full bg-[#001f3f] text-white border-white/20 rounded-lg focus:border-[#ffcc00]" rows="4"></textarea>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full bg-[#ffcc00] text-[#001f3f] font-bold py-3 rounded-lg hover:bg-yellow-400 transition-colors">
                        Save Report
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>