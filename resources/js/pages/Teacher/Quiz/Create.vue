<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'; // Correct Layout
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Trash2, Save, ArrowLeft, BrainCircuit, Clock, HelpCircle } from 'lucide-vue-next';
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

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Quiz Management', href: route('teacher.quiz.index') },
    { title: 'Create Quiz', href: '#' },
];

const addQuestion = () => {
    form.questions.push({ text: '', explanation: '', options: [{ text: '', is_correct: false }, { text: '', is_correct: false }] });
};

const addOption = (qIndex: number) => {
    form.questions[qIndex].options.push({ text: '', is_correct: false });
};

const setCorrectOption = (qIndex: number, oIndex: number) => {
    form.questions[qIndex].options.forEach((opt, idx) => { opt.is_correct = idx === oIndex; });
};

const submit = () => {
    for (let i = 0; i < form.questions.length; i++) {
        const question = form.questions[i];
        const hasCorrectAnswer = question.options.some(opt => opt.is_correct);
        if (!hasCorrectAnswer) {
            alert(`⚠️ Warning: Question ${i + 1} does not have a correct answer selected.`);
            return;
        }
    }
    form.post(route('teacher.quiz.store')); 
};
</script>

<template>
    <Head title="Create Quiz" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-50 p-6 space-y-8">
            
            <div class="max-w-4xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('teacher.quiz.index')" class="bg-white p-3 rounded-2xl border border-slate-200 text-slate-400 hover:text-teal-600 transition-all shadow-sm">
                        <ArrowLeft class="w-6 h-6" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">Create New Quiz</h1>
                        <p class="text-slate-500 font-medium mt-1">Design an assessment for your students.</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="max-w-4xl mx-auto space-y-8 pb-24">
                
                <Card class="bg-white border-slate-200 rounded-[2rem] shadow-sm overflow-hidden">
                    <CardHeader class="border-b border-slate-50 p-8">
                        <CardTitle class="text-xl font-black text-slate-900 flex items-center gap-2">
                            <BrainCircuit class="w-6 h-6 text-teal-600" /> Quiz Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-8 grid gap-6">
                        <div class="grid gap-2">
                            <Label class="text-slate-700 font-bold ml-1">Quiz Title</Label>
                            <Input v-model="form.title" placeholder="e.g. Advanced Laravel Security" required 
                                class="h-12 bg-slate-50 border-slate-200 rounded-xl focus:bg-white focus:ring-teal-500 font-medium" />
                        </div>
                        <div class="grid gap-2">
                            <Label class="text-slate-700 font-bold ml-1">Description</Label>
                            <Textarea v-model="form.description" placeholder="Brief summary for students..." rows="3"
                                class="bg-slate-50 border-slate-200 rounded-xl focus:bg-white focus:ring-teal-500 font-medium px-4 py-3" />
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="grid gap-2">
                                <Label class="text-slate-700 font-bold ml-1 flex items-center gap-2">
                                    <Clock class="w-4 h-4 text-teal-500" /> Duration (secs)
                                </Label>
                                <Input type="number" v-model="form.duration" class="h-12 bg-slate-50 border-slate-200 rounded-xl font-medium" />
                            </div>
                            <div class="grid gap-2">
                                <Label class="text-slate-700 font-bold ml-1 flex items-center gap-2">
                                    <HelpCircle class="w-4 h-4 text-teal-500" /> Difficulty
                                </Label>
                                <select v-model="form.difficulty" 
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium focus:ring-2 focus:ring-teal-500">
                                    <option value="Easy">Easy</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Hard">Hard</option>
                                </select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div v-for="(question, qIndex) in form.questions" :key="qIndex" class="group">
                    <Card class="bg-white border-slate-200 rounded-[2rem] shadow-sm overflow-hidden transition-all hover:border-teal-300">
                        <CardHeader class="border-b border-slate-50 p-8">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <span class="bg-teal-600 text-white h-8 w-8 rounded-lg flex items-center justify-center font-black text-sm">
                                        {{ qIndex + 1 }}
                                    </span>
                                    <CardTitle class="text-lg font-black text-slate-900">Question Content</CardTitle>
                                </div>
                                <Button type="button" variant="ghost" class="text-rose-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl px-4" 
                                    @click="form.questions.splice(qIndex, 1)" v-if="form.questions.length > 1">
                                    <Trash2 class="h-4 w-4 mr-2" /> Remove
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="p-8 space-y-8">
                            <div class="grid gap-3">
                                <Label class="text-slate-700 font-bold ml-1">Question Text</Label>
                                <Input v-model="question.text" placeholder="What is the result of...?" required 
                                    class="h-12 bg-slate-50 border-slate-200 rounded-xl focus:bg-white font-bold" />
                            </div>
                            
                            <div class="space-y-4">
                                <Label class="text-xs uppercase text-slate-400 font-black tracking-widest ml-1">Answer Options</Label>
                                <div class="grid gap-3">
                                    <div v-for="(option, oIndex) in question.options" :key="oIndex" class="flex items-center gap-3 group/opt">
                                        <div @click="setCorrectOption(qIndex, oIndex)" 
                                            class="cursor-pointer h-8 w-8 rounded-xl border-2 flex items-center justify-center transition-all shrink-0"
                                            :class="option.is_correct ? 'border-emerald-500 bg-emerald-500 shadow-lg shadow-emerald-500/20' : 'border-slate-200 hover:border-emerald-400'">
                                            <div v-if="option.is_correct" class="h-2 w-2 bg-white rounded-full"></div>
                                        </div>
                                        
                                        <Input v-model="option.text" placeholder="Option text" required
                                            class="h-12 bg-slate-50 border-slate-200 rounded-xl focus:bg-white font-medium flex-1" />
                                        
                                        <Button type="button" variant="ghost" size="icon" class="h-10 w-10 text-slate-300 hover:text-rose-500 rounded-xl"
                                            @click="question.options.splice(oIndex, 1)" v-if="question.options.length > 2">
                                            <Trash2 class="h-5 w-5" />
                                        </Button>
                                    </div>
                                </div>
                                <Button type="button" variant="ghost" size="sm" class="text-teal-600 hover:bg-teal-50 font-black text-sm rounded-lg" @click="addOption(qIndex)">
                                    <Plus class="mr-2 h-4 w-4" /> Add Option
                                </Button>
                            </div>

                            <div class="grid gap-3 pt-4 border-t border-slate-50">
                                <Label class="text-slate-700 font-bold ml-1">Feedback / Explanation (Optional)</Label>
                                <Textarea v-model="question.explanation" placeholder="Why is this answer correct?" rows="2" 
                                    class="bg-slate-50 border-slate-200 rounded-xl font-medium" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Button type="button" variant="outline" class="w-full border-dashed border-2 py-10 rounded-[2rem] text-slate-400 border-slate-200 hover:border-teal-400 hover:text-teal-600 hover:bg-teal-50 transition-all font-black text-lg" @click="addQuestion">
                    <Plus class="mr-2 h-7 w-7" /> Add Question {{ form.questions.length + 1 }}
                </Button>

                <div class="fixed bottom-8 left-1/2 -translate-x-1/2 w-full max-w-4xl px-6 z-40">
                    <div class="bg-white/80 backdrop-blur-md p-4 shadow-2xl rounded-[2rem] border border-white flex justify-between items-center px-8 ring-1 ring-black/5">
                        <div class="flex items-center gap-3">
                            <span class="text-slate-400 text-sm font-bold uppercase tracking-widest">Total:</span>
                            <span class="text-2xl font-black text-slate-900">{{ form.questions.length }}</span>
                        </div>
                        <Button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-12 py-7 rounded-2xl font-black text-lg shadow-lg shadow-teal-600/30 transition-all active:scale-95" :disabled="form.processing">
                            <Save class="mr-3 h-5 w-5" /> Create Quiz
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppSidebarLayout>
</template>