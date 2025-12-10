<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Trash2, Save } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    activity: any;
}>();

// Helper to format the date
const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return dateString.split(' ')[0];
};

const questionCountToGenerate = ref(1);

const form = useForm({
    title: props.activity.title || '',
    type: props.activity.type || 'Assignment',
    description: props.activity.description || '',
    due_date: formatDate(props.activity.due_date),
    time_limit: props.activity.time_limit || '',
    questions: props.activity.quiz_data ? JSON.parse(props.activity.quiz_data) : [],
    file: null as File | null,
    _method: 'PUT',
});

const generateQuestions = () => {
    for (let i = 0; i < questionCountToGenerate.value; i++) {
        form.questions.push({
            question_text: '',
            options: ['', '', '', ''],
            correct_answer: 0
        });
    }
};

const removeQuestion = (index: number) => {
    form.questions.splice(index, 1);
};

const submit = () => {
    form.post(route('activities.update', props.activity.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Activity" />

    <AppLayout :breadcrumbs="[{ title: 'Activities', href: route('activities.index') }, { title: 'Edit', href: '#' }]">
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card class="bg-white shadow-lg border-none">
                    <CardHeader class="border-b bg-yellow-50/50 pb-4">
                        <CardTitle class="text-xl font-bold text-yellow-700">Edit Activity</CardTitle>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div>
                                <Label>Activity Type</Label>
                                <select v-model="form.type" class="w-full rounded-md border-gray-300 px-3 py-2">
                                    <option value="Assignment">Assignment</option>
                                    <option value="Quiz">Quiz</option>
                                    <option value="Game">Game</option>
                                </select>
                            </div>

                            <div>
                                <Label>Title</Label>
                                <Input v-model="form.title" class="bg-white" required />
                            </div>

                            <div>
                                <Label>Description</Label>
                                <textarea v-model="form.description" rows="3" class="w-full rounded-md border-gray-300 bg-white px-3 py-2"></textarea>
                            </div>

                            <div v-if="form.type === 'Quiz' || form.type === 'Game'" class="p-4 bg-blue-50 rounded-lg border border-blue-100 space-y-4">
                                <h3 class="font-bold text-[#003366]">Settings</h3>
                                <div>
                                    <Label>Time Limit (Minutes)</Label>
                                    <Input type="number" v-model="form.time_limit" class="bg-white w-32" />
                                </div>
                                <div class="flex items-end gap-2 pt-2">
                                    <div class="w-full">
                                        <Label>Add more questions?</Label>
                                        <Input type="number" v-model="questionCountToGenerate" min="1" class="bg-white" />
                                    </div>
                                    <Button type="button" @click="generateQuestions" class="bg-[#003366]">Add</Button>
                                </div>
                            </div>

                            <div v-if="form.type === 'Quiz' || form.type === 'Game'" class="space-y-6">
                                
                                <div v-if="form.questions.length === 0" class="text-center p-6 bg-gray-50 border border-dashed rounded-lg text-gray-500 italic">
                                    No questions have been added to this quiz yet. <br>
                                    Use the "Add" button above to generate questions.
                                </div>

                                <div v-for="(question, qIndex) in form.questions" :key="qIndex" class="border p-4 rounded-lg bg-gray-50 relative">
                                    <button type="button" @click="removeQuestion(qIndex)" class="absolute top-4 right-4 text-red-500 hover:text-red-700">
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                    
                                    <h4 class="font-bold text-gray-700 mb-2">Question {{ qIndex + 1 }}</h4>
                                    
                                    <div class="mb-3">
                                        <Input v-model="question.question_text" placeholder="Question..." class="bg-white font-bold" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div v-for="(option, oIndex) in question.options" :key="oIndex" class="flex items-center gap-2">
                                            <span class="text-sm font-bold">{{ ['A','B','C','D'][oIndex] }}.</span>
                                            <Input v-model="question.options[oIndex]" class="bg-white text-sm" />
                                        </div>
                                    </div>

                                    <select v-model="question.correct_answer" class="w-full rounded-md border-gray-300 text-sm">
                                        <option :value="0">Answer: Option A</option>
                                        <option :value="1">Answer: Option B</option>
                                        <option :value="2">Answer: Option C</option>
                                        <option :value="3">Answer: Option D</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                                <div>
                                    <Label>Due Date</Label>
                                    <Input type="date" v-model="form.due_date" class="bg-white" />
                                </div>
                                <div>
                                    <Label>Replace File (Optional)</Label>
                                    <Input type="file" @input="(e: any) => form.file = e.target.files[0]" class="bg-white" />
                                </div>
                            </div>

                            <Button type="submit" :disabled="form.processing" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2">
                                <Save class="w-4 h-4 mr-2" /> Save Changes
                            </Button>

                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>