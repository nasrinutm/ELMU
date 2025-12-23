<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3'; 
import { route } from 'ziggy-js'; 
import { Button } from '@/components/ui/button'; 
import { Plus, FileText, CheckCircle, Pencil, Trash2, X, Settings, Check, Upload } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    activities: any;
    filters: any;
    can: {
        manage_activities: boolean;
    }
}>();

// 1. Setup Page and Flash Control
const page = usePage();
const showFlash = ref(false);

// 2. Setup Management Mode
const isManaging = ref(false);

const toggleManageMode = () => {
    isManaging.value = !isManaging.value;
};

// 3. CONFIRMATION LOGIC
const confirmEdit = (id: number) => {
    if (confirm('Are you sure you want to edit this activity?')) {
        router.get(route('activities.edit', id));
    }
};

const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this activity? This action cannot be undone.')) {
        router.delete(route('activities.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                // The watcher below will catch the flash message and show the green box
            }
        });
    }
};

// 4. Computed Property for Message
const successMessage = computed(() => page.props.flash?.success);

// 5. Auto-Hide Flash Logic
watch(successMessage, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => {
            showFlash.value = false;
        }, 3000); 
    }
}, { immediate: true });

// Helper to Group Activities
const assignments = computed(() => props.activities.data.filter((a: any) => a.type === 'Assignment'));
const submissions = computed(() => props.activities.data.filter((a: any) => a.type === 'Submission'));
const exercises = computed(() => props.activities.data.filter((a: any) => a.type === 'Exercise'));

</script>

<template>
    <Head title="Activity" />

    <AppLayout :breadcrumbs="[{ title: 'Activity', href: route('activities.index') }]">
        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                
                <transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="transform opacity-0 -translate-y-2"
                    enter-to-class="transform opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-300"
                    leave-from-class="transform opacity-100 translate-y-0"
                    leave-to-class="transform opacity-0 -translate-y-2"
                >
                    <div v-if="successMessage && showFlash" 
                        class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-md flex justify-between items-center">
                        <div class="flex items-center">
                            <CheckCircle class="w-5 h-5 mr-2" />
                            <span class="font-bold mr-1">Success!</span>
                            <span>{{ successMessage }}</span>
                        </div>
                        <button @click="showFlash = false" class="text-green-700 hover:text-green-900">
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </transition>

                <div class="bg-[#003366] overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div class="p-6 border-b border-[#004080] flex justify-between items-center bg-[#002244]">
                        <h1 class="text-2xl font-bold text-[#FFD700]">Classroom Activities</h1>
                        
                        <div v-if="can.manage_activities" class="flex gap-3">
                            <Button @click="toggleManageMode" 
                                class="font-bold shadow-md transition border"
                                :class="isManaging ? 'bg-green-600 hover:bg-green-700 text-white border-green-500' : 'bg-transparent hover:bg-[#003366] text-[#FFD700] border-[#FFD700]'">
                                <component :is="isManaging ? Check : Settings" class="w-4 h-4 mr-2" />
                                {{ isManaging ? 'Done' : 'Manage Activity' }}
                            </Button>

                            <Link :href="route('activities.create')">
                                <Button class="bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md">
                                    <Plus class="w-4 h-4 mr-2" />
                                    Create New
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <div class="p-6 space-y-10">

                        <section>
                            <div class="flex items-center gap-2 mb-4 border-b border-[#004080] pb-2">
                                <FileText class="w-5 h-5 text-blue-400" />
                                <h2 class="text-xl font-bold text-[#FFD700]">Assignments</h2>
                            </div>
                            
                            <div v-if="assignments.length > 0" class="bg-[#1a202c] rounded-lg overflow-hidden border border-gray-700">
                                <div v-for="activity in assignments" :key="activity.id" 
                                    class="p-4 border-b border-gray-700 last:border-b-0 hover:bg-[#2d3748] transition flex justify-between items-center group">
                                    <div>
                                        <h3 class="font-bold text-lg text-white group-hover:text-blue-300 transition">{{ activity.title }}</h3>
                                        <p class="text-sm text-gray-400">{{ activity.description }}</p>
                                        <p v-if="activity.due_date" class="text-xs text-red-400 mt-1 font-medium">Due: {{ new Date(activity.due_date).toLocaleDateString() }}</p>
                                    </div>
                                    
                                    <div class="flex gap-2 items-center">
                                        <a v-if="activity.file_path" 
                                           :href="route('activities.download', activity.id)" 
                                           class="bg-blue-900 hover:bg-blue-800 text-blue-100 px-3 py-1 rounded text-sm font-semibold border border-blue-700" 
                                           title="Download File">
                                            Download
                                        </a>

                                        <div v-if="can.manage_activities && isManaging" class="flex gap-2 ml-4 border-l border-gray-600 pl-4 animate-in fade-in slide-in-from-right-2 duration-200">
                                            <button @click="confirmEdit(activity.id)" class="text-yellow-500 hover:text-yellow-400 p-1 transition hover:scale-110" title="Edit">
                                                <Pencil class="w-4 h-4" />
                                            </button>
                                            <button @click="confirmDelete(activity.id)" class="text-red-500 hover:text-red-400 p-1 transition hover:scale-110" title="Delete">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-400 italic text-sm p-4 bg-[#1a202c] rounded border border-gray-700">No assignments currently active.</p>
                        </section>

                        <section>
                            <div class="flex items-center gap-2 mb-4 border-b border-[#004080] pb-2">
                                <Pencil class="w-5 h-5 text-purple-400" />
                                <h2 class="text-xl font-bold text-[#FFD700]">Exercise</h2>
                            </div>

                            <div v-if="exercises.length > 0" class="bg-[#1a202c] rounded-lg overflow-hidden border border-gray-700">
                                <div v-for="activity in exercises" :key="activity.id" 
                                    class="p-4 border-b border-gray-700 last:border-b-0 hover:bg-[#2d3748] transition flex justify-between items-center group">
                                    <div>
                                        <h3 class="font-bold text-lg text-white group-hover:text-purple-300 transition">{{ activity.title }}</h3>
                                        <p class="text-sm text-gray-400">{{ activity.description }}</p>
                                    </div>
                                    
                                    <div class="flex gap-2 items-center">
                                        <a v-if="activity.file_path" 
                                           :href="route('activities.download', activity.id)" 
                                           class="bg-blue-900 hover:bg-blue-800 text-blue-100 px-3 py-1 rounded text-sm font-semibold border border-blue-700" 
                                           title="Download File">
                                            Download
                                        </a>

                                        <div v-if="can.manage_activities && isManaging" class="flex gap-2 ml-4 border-l border-gray-600 pl-4 animate-in fade-in slide-in-from-right-2 duration-200">
                                            <button @click="confirmEdit(activity.id)" class="text-yellow-500 hover:text-yellow-400 p-1 transition hover:scale-110" title="Edit">
                                                <Pencil class="w-4 h-4" />
                                            </button>
                                            <button @click="confirmDelete(activity.id)" class="text-red-500 hover:text-red-400 p-1 transition hover:scale-110" title="Delete">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-400 italic text-sm p-4 bg-[#1a202c] rounded border border-gray-700">No exercises added yet.</p>
                        </section>

                        <section>
                            <div class="flex items-center gap-2 mb-4 border-b border-[#004080] pb-2">
                                <Upload class="w-5 h-5 text-orange-400" />
                                <h2 class="text-xl font-bold text-[#FFD700]">Submissions</h2>
                            </div>
                            
                            <div v-if="submissions.length > 0" class="bg-[#1a202c] rounded-lg overflow-hidden border border-gray-700">
                                <div v-for="activity in submissions" :key="activity.id" 
                                    class="p-4 border-b border-gray-700 last:border-b-0 hover:bg-[#2d3748] transition flex justify-between items-center group">
                                    <div>
                                        <h3 class="font-bold text-lg text-white group-hover:text-orange-300 transition">{{ activity.title }}</h3>
                                        <p class="text-sm text-gray-400">{{ activity.description }}</p>
                                        <p v-if="activity.due_date" class="text-xs text-red-400 mt-1 font-medium">Due: {{ new Date(activity.due_date).toLocaleDateString() }}</p>
                                    </div>
                                    
                                    <div class="flex gap-2 items-center">
                                        <Link :href="route('activities.show', activity.id)" 
                                           class="bg-blue-900 hover:bg-blue-800 text-blue-100 px-3 py-1 rounded text-sm font-semibold border border-blue-700 flex items-center" 
                                           title="Submit Work">
                                            <Upload class="w-4 h-4 mr-2" />
                                            Submit
                                        </Link>

                                        <div v-if="can.manage_activities && isManaging" class="flex gap-2 ml-4 border-l border-gray-600 pl-4 animate-in fade-in slide-in-from-right-2 duration-200">
                                            <button @click="confirmEdit(activity.id)" class="text-yellow-500 hover:text-yellow-400 p-1 transition hover:scale-110" title="Edit">
                                                <Pencil class="w-4 h-4" />
                                            </button>
                                            <button @click="confirmDelete(activity.id)" class="text-red-500 hover:text-red-400 p-1 transition hover:scale-110" title="Delete">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-400 italic text-sm p-4 bg-[#1a202c] rounded border border-gray-700">No submission activities created.</p>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>