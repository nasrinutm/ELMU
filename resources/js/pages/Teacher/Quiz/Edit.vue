<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Trash2, Save, ArrowLeft, BrainCircuit, Clock, HelpCircle, AlertTriangle } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import { ref } from 'vue';

const props = defineProps<{
    quiz: {
        id: number;
        title: string;
        description: string;
        duration: number;
        difficulty: string;
        questions: Array<{
            text: string;
            explanation: string;
            options: Array<{ text: string; is_correct: boolean | number }>;
        }>;
    };
}>();

const form = useForm({
    title: props.quiz.title,
    description: props.quiz.description,
    duration: props.quiz.duration,
    difficulty: props.quiz.difficulty,
    questions: props.quiz.questions.map(q => ({
        text: q.text,
        explanation: q.explanation,
        options: q.options.map(o => ({
            text: o.text,
            is_correct: Boolean(o.is_correct)
        }))
    }))
});

// --- MODAL STATE ---
const modal = ref({
    show: false,
    type: 'update' as 'update' | 'delete',
    title: '',
    message: '',
    confirmText: '',
    action: () => {}
});

// Trigger UPDATE Modal (Yellow)
const confirmUpdate = () => {
    modal.value = {
        show: true,
        type: 'update',
        title: 'Confirm Update',
        message: 'Proceed with saving these changes? This will update the material details for all students.',
        confirmText: 'Yes, Proceed',
        action: () => form.put(route('teacher.quiz.update', props.quiz.id), {
            onSuccess: () => modal.value.show = false
        })
    };
};

// Trigger DELETE Modal (Red)
const confirmDelete = () => {
    modal.value = {
        show: true,
        type: 'delete',
        title: 'Confirm Deletion',
        message: 'Are you sure you want to remove this resource permanently? This action cannot be undone.',
        confirmText: 'Delete',
        action: () => form.delete(route('teacher.quiz.destroy', props.quiz.id), {
            onSuccess: () => modal.value.show = false
        })
    };
};

const executeAction = () => {
    modal.value.action();
};
// -------------------

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Quiz Management', href: route('teacher.quiz.index') },
    { title: 'Edit Quiz', href: '#' },
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
</script>

<template>
    <Head title="Edit Quiz" />

    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        
        <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="fixed inset-0 transform transition-all cursor-pointer" @click="modal.show = false">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white max-w-md w-full p-10 shadow-2xl border border-slate-200 rounded-none z-50 relative flex flex-col items-center text-center">
                
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-6"
                    :class="modal.type === 'delete' ? 'bg-red-50' : 'bg-amber-50'">
                    <AlertTriangle class="w-8 h-8 stroke-2" 
                        :class="modal.type === 'delete' ? 'text-red-500' : 'text-amber-500'" />
                </div>

                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-900 mb-2">
                    {{ modal.title }}
                </h3>

                <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed">
                    {{ modal.message }}
                </p>

                <div class="flex gap-4 w-full">
                    <button @click="modal.show = false" type="button" 
                        class="flex-1 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 border border-slate-100 hover:bg-slate-50 transition focus:outline-none">
                        No, Cancel
                    </button>
                    
                    <button @click="executeAction" type="button" 
                        class="flex-1 py-4 text-white text-[10px] font-bold uppercase tracking-widest shadow-lg transition duration-200 focus:outline-none"
                        :class="modal.type === 'delete' 
                            ? 'bg-red-600 hover:bg-red-700' 
                            : 'bg-slate-900 hover:bg-[#0d9488]'">
                        {{ modal.confirmText }}
                    </button>
                </div>
            </div>
        </div>
        <div class="min-h-screen bg-slate-50 p-6 space-y-8">
            
            <div class="max-w-4xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('teacher.quiz.index')" class="bg-white p-3 rounded-2xl border border-slate-200 text-slate-400 hover:text-teal-600 transition-all shadow-sm">
                        <ArrowLeft class="w-6 h-6" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">Edit Quiz</h1>
                        <p class="text-slate-500 font-medium mt-1">Update assessment: <span class="text-teal-600 font-bold">{{ quiz.title }}</span></p>
                    </div>
                </div>

                <button type="button" @click="confirmDelete" class="flex items-center gap-2 text-rose-500 font-bold hover:bg-rose-50 px-4 py-2 rounded-xl transition-all">
                    <Trash2 class="w-5 h-5" /> Delete Quiz
                </button>
            </div>

            <form @submit.prevent="confirmUpdate" class="max-w-4xl mx-auto space-y-8 pb-32">
                
                <Card class="bg-white border-slate-200 rounded-[2rem] shadow-sm overflow-hidden">
                    <CardHeader class="border-b border-slate-50 p-8">
                        <CardTitle class="text-xl font-black text-slate-900 flex items-center gap-2">
                            <BrainCircuit class="w-6 h-6 text-teal-600" /> Quiz Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-8 grid gap-6">
                        
                        <div class="grid gap-2">
                            <Label class="text-slate-700 font-bold ml-1">
                                Quiz Title <span class="text-red-500">*</span>
                            </Label>
                            <Input 
                                v-model="form.title" 
                                placeholder="Title..."
                                class="h-12 bg-slate-50 rounded-xl font-medium focus:bg-white transition-all"
                                :class="{ 
                                    'border-red-500 focus:ring-red-500': form.errors.title, 
                                    'border-slate-200 focus:ring-teal-500': !form.errors.title 
                                }"
                            />
                            <p v-if="form.errors.title" class="text-red-500 text-sm ml-1">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-slate-700 font-bold ml-1">Description</Label>
                            <Textarea v-model="form.description" rows="3" placeholder="Description..."
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
                                <Input 
                                    v-model="question.text" 
                                    placeholder="Question Text..."
                                    class="h-12 bg-slate-50 rounded-xl focus:bg-white font-bold transition-all"
                                    :class="{ 
                                        'border-red-500 focus:ring-red-500': form.errors[`questions.${qIndex}.text`], 
                                        'border-slate-200': !form.errors[`questions.${qIndex}.text`] 
                                    }"
                                />
                                <p v-if="form.errors[`questions.${qIndex}.text`]" class="text-red-500 text-sm ml-1">
                                    {{ form.errors[`questions.${qIndex}.text`] }}
                                </p>
                            </div>
                            
                            <div class="space-y-4">
                                <Label class="text-xs uppercase text-slate-400 font-black tracking-widest ml-1">Answers (Select one correct)</Label>
                                <div class="grid gap-3">
                                    <div v-for="(option, oIndex) in question.options" :key="oIndex" class="flex items-center gap-3">
                                        <div @click="setCorrectOption(qIndex, oIndex)" 
                                            class="cursor-pointer h-8 w-8 rounded-xl border-2 flex items-center justify-center transition-all shrink-0"
                                            :class="option.is_correct ? 'border-emerald-500 bg-emerald-500 shadow-lg shadow-emerald-500/20' : 'border-slate-200 hover:border-emerald-400'">
                                            <div v-if="option.is_correct" class="h-2 w-2 bg-white rounded-full"></div>
                                        </div>
                                        
                                        <div class="flex-1">
                                            <Input 
                                                v-model="option.text"
                                                placeholder="Option Text..."
                                                class="h-12 bg-slate-50 rounded-xl focus:bg-white font-medium transition-all"
                                                :class="{ 
                                                    'border-red-500 focus:ring-red-500': form.errors[`questions.${qIndex}.options.${oIndex}.text`], 
                                                    'border-slate-200': !form.errors[`questions.${qIndex}.options.${oIndex}.text`] 
                                                }"
                                            />
                                            <p v-if="form.errors[`questions.${qIndex}.options.${oIndex}.text`]" class="text-red-500 text-sm mt-1">
                                                {{ form.errors[`questions.${qIndex}.options.${oIndex}.text`] }}
                                            </p>
                                        </div>
                                        
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
                                <Label class="text-slate-700 font-bold ml-1">Explanation</Label>
                                <Textarea v-model="question.explanation" rows="2" placeholder="Explanation..."
                                    class="bg-slate-50 border-slate-200 rounded-xl font-medium" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Button type="button" variant="outline" class="w-full border-dashed border-2 py-10 rounded-[2rem] text-slate-400 border-slate-200 hover:border-teal-400 hover:text-teal-600 hover:bg-teal-50 transition-all font-black text-lg" @click="addQuestion">
                    <Plus class="mr-2 h-7 w-7" /> Add New Question
                </Button>

                <div class="fixed bottom-8 left-1/2 -translate-x-1/2 w-full max-w-4xl px-6 z-40">
                    <div class="bg-white/90 backdrop-blur-md p-4 shadow-2xl rounded-[2rem] border border-white flex justify-between items-center px-8 ring-1 ring-black/5">
                        <div class="flex items-center gap-3 font-bold text-slate-500">
                            Editing Quiz ID: <span class="text-slate-900 font-black">{{ quiz.id }}</span>
                        </div>
                        
                        <Button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-12 py-7 rounded-2xl font-black text-lg shadow-lg shadow-teal-600/30 transition-all active:scale-95" :disabled="form.processing">
                            <Save class="mr-3 h-5 w-5" /> Update Changes
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppSidebarLayout>
</template>