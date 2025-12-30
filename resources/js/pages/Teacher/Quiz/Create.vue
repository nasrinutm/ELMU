<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Trash2, Save, ArrowLeft } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const form = useForm({
    title: '',
    description: '',
    duration: 600,
    difficulty: 'Medium',
    questions: [
        {
            text: '',
            explanation: '',
            options: [
                { text: '', is_correct: false },
                { text: '', is_correct: false },
            ]
        }
    ]
});

const addQuestion = () => {
    form.questions.push({ text: '', explanation: '', options: [{ text: '', is_correct: false }, { text: '', is_correct: false }] });
};

const addOption = (qIndex: number) => {
    form.questions[qIndex].options.push({ text: '', is_correct: false });
};

const setCorrectOption = (qIndex: number, oIndex: number) => {
    form.questions[qIndex].options.forEach((opt, idx) => { opt.is_correct = idx === oIndex; });
};

// --- NEW VALIDATION LOGIC ---
const submit = () => {
    // Loop through all questions to check for a correct answer
    for (let i = 0; i < form.questions.length; i++) {
        const question = form.questions[i];
        
        // Check if at least one option is marked as correct
        const hasCorrectAnswer = question.options.some(opt => opt.is_correct);

        if (!hasCorrectAnswer) {
            // SHOW POPUP WARNING
            alert(`⚠️ Warning: Question ${i + 1} does not have a correct answer selected.\n\nPlease click the circle next to the correct option before saving.`);
            return; // STOP the function here. Do not save.
        }
    }

    // If we get here, everything is good. Save data.
    form.post(route('teacher.quiz.store')); 
};
</script>

<template>
    <Head title="Create Quiz" />

    <AppLayout>
        <div class="p-6 bg-[#002B5C] min-h-screen text-white">
            <div class="max-w-4xl mx-auto space-y-6">
                
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-[#FFD700]">Create New Quiz</h1>
                    <Link :href="route('teacher.quiz.index')">
                        <Button variant="outline" class="border-[#FFD700] text-[#FFD700] hover:bg-[#FFD700] hover:text-[#002B5C] font-bold">
                             <ArrowLeft class="mr-2 h-4 w-4" /> Cancel
                        </Button>
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <Card class="text-[#002B5C] bg-white">
                        <CardHeader><CardTitle class="text-xl font-bold">Quiz Details</CardTitle></CardHeader>
                        <CardContent class="grid gap-5">
                            <div class="grid gap-2">
                                <Label class="text-[#002B5C] font-bold">Title</Label>
                                <Input v-model="form.title" placeholder="e.g. Advanced Laravel Security" required class="bg-white text-black border-gray-400 focus:border-[#002B5C] font-medium" />
                            </div>
                            <div class="grid gap-2">
                                <Label class="text-[#002B5C] font-bold">Description</Label>
                                <Textarea v-model="form.description" placeholder="Brief summary..." class="bg-white text-black border-gray-400 focus:border-[#002B5C] font-medium" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <Label class="text-[#002B5C] font-bold">Duration (seconds)</Label>
                                    <Input type="number" v-model="form.duration" class="bg-white text-black border-gray-400 focus:border-[#002B5C] font-medium" />
                                </div>
                                <div class="grid gap-2">
                                    <Label class="text-[#002B5C] font-bold">Difficulty</Label>
                                    <select v-model="form.difficulty" class="flex h-10 w-full items-center justify-between rounded-md border border-gray-400 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002B5C] focus:border-transparent text-black font-medium">
                                        <option value="Easy">Easy</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Hard">Hard</option>
                                    </select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div v-for="(question, qIndex) in form.questions" :key="qIndex" class="relative">
                        <Card class="border-l-4 border-l-[#FFD700] text-[#002B5C] bg-white">
                            <CardHeader class="pb-2">
                                <div class="flex justify-between items-center">
                                    <CardTitle class="text-lg font-bold">Question {{ qIndex + 1 }}</CardTitle>
                                    <Button type="button" variant="ghost" class="text-red-600 hover:text-red-700 hover:bg-red-100 font-bold" 
                                        @click="form.questions.splice(qIndex, 1)" v-if="form.questions.length > 1">
                                        <Trash2 class="h-4 w-4" /> Remove
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent class="grid gap-5">
                                <div class="grid gap-2">
                                    <Label class="text-[#002B5C] font-bold">Question Text</Label>
                                    <Input v-model="question.text" placeholder="Enter question..." required class="bg-white text-black border-gray-400 focus:border-[#002B5C] font-bold text-md" />
                                </div>
                                
                                <div class="space-y-3 pl-4 border-l-4 border-gray-200 py-2">
                                    <Label class="text-sm uppercase text-[#002B5C] font-black tracking-wider">Answer Options (Click circle to mark correct)</Label>
                                    <div v-for="(option, oIndex) in question.options" :key="oIndex" class="flex items-center gap-2">
                                        <div @click="setCorrectOption(qIndex, oIndex)" 
                                            class="cursor-pointer h-7 w-7 rounded-full border-4 flex items-center justify-center transition-colors shrink-0"
                                            :class="option.is_correct ? 'border-green-600 bg-green-600' : 'border-gray-300 hover:border-green-600'">
                                            <div v-if="option.is_correct" class="h-2 w-2 bg-white rounded-full"></div>
                                        </div>
                                        
                                        <Input v-model="option.text" placeholder="Option text" class="flex-1 bg-white text-black border-gray-400 focus:border-[#002B5C] font-medium" required />
                                        
                                        <Button type="button" variant="ghost" size="icon" class="h-9 w-9 text-gray-500 hover:text-red-600 hover:bg-red-100"
                                            @click="question.options.splice(oIndex, 1)" v-if="question.options.length > 2">
                                            <Trash2 class="h-5 w-5" />
                                        </Button>
                                    </div>
                                    <Button type="button" variant="link" size="sm" class="px-0 text-[#002B5C] hover:text-[#004080] font-bold text-md" @click="addOption(qIndex)">
                                        + Add Option
                                    </Button>
                                </div>

                                <div class="grid gap-2 mt-2">
                                    <Label class="text-[#002B5C] font-bold">Feedback / Explanation (Optional)</Label>
                                    <Textarea v-model="question.explanation" placeholder="Explain the answer..." rows="2" class="bg-white text-black border-gray-400 focus:border-[#002B5C] font-medium" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Button type="button" variant="outline" class="w-full border-dashed border-2 py-8 text-[#FFD700] border-[#FFD700] hover:bg-[#FFD700]/10 font-bold text-lg" @click="addQuestion">
                        <Plus class="mr-2 h-6 w-6" /> Add Another Question
                    </Button>

                    <div class="sticky bottom-4 bg-white p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] rounded-lg border-t-2 border-[#002B5C] flex justify-between items-center text-[#002B5C]">
                        <div class="text-lg font-bold">
                            Total Questions: <strong>{{ form.questions.length }}</strong>
                        </div>
                        <Button type="submit" class="bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200] px-8 py-6 font-black text-lg" :disabled="form.processing">
                            <Save class="mr-2 h-5 w-5" /> Save Quiz
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>