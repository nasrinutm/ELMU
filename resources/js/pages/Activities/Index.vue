<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3'; 
import { route } from 'ziggy-js'; 
import { Button } from '@/components/ui/button'; 
import { Plus, FileText, Pencil, Trash2, X, Search, Calendar, Download, Eye, CheckCircle } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    activities: any;
    can: {
        manage_activities: boolean;
    }
}>();

// Page & Flash Config
const page = usePage();
const showFlash = ref(false);
const searchQuery = ref('');

// Confirmation Logic
const confirmDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this activity? This action cannot be undone.')) {
        router.delete(route('activities.destroy', id), {
            preserveScroll: true
        });
    }
};

// Flash Message Logic
const successMessage = computed(() => page.props.flash?.success);
watch(successMessage, (newMessage) => {
    if (newMessage) {
        showFlash.value = true;
        setTimeout(() => showFlash.value = false, 3000); 
    }
}, { immediate: true });

// Filter Logic (Search Only)
const filteredActivities = computed(() => {
    let data = props.activities.data;
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter((a: any) => 
            a.title.toLowerCase().includes(query) || 
            a.description.toLowerCase().includes(query)
        );
    }
    return data;
});
</script>

<template>
    <Head title="Classroom Activities" />

    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Classroom Activities', href: route('activities.index') }
    ]">
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
                        class="mb-6 bg-green-500/10 border border-green-500/50 text-green-500 px-4 py-3 rounded-lg shadow-sm flex justify-between items-center backdrop-blur-sm">
                        <span class="font-medium">{{ successMessage }}</span>
                        <button @click="showFlash = false" class="hover:text-green-400 transition">
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </transition>

                <div class="bg-[#ffffff] overflow-hidden shadow-xl sm:rounded-lg border border-[#004080]">
                    
                    <div class="p-6 border-b border-[#004080] flex flex-col sm:flex-row justify-between items-center gap-4 bg-[#ffffff]">
                        <h1 class="text-2xl font-bold text-[#212121]">Classroom Activities</h1>
                        
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <div class="relative w-full sm:w-64">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#212121]" />
                                <input 
                                    v-model="searchQuery"
                                    type="text" 
                                    placeholder="Search activities..." 
                                    class="w-full pl-9 pr-4 py-2 bg-[#ffffff] border border-gray-400 text-[#212121] rounded-md text-sm placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                />
                            </div>

                            <template v-if="can.manage_activities">
                                <Link :href="route('activities.create')">
                                    <Button class="bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md whitespace-nowrap">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Create New
                                    </Button>
                                </Link>
                            </template>
                        </div>
                    </div>

                    <div class="p-0">
                        <div v-if="filteredActivities.length > 0" class="divide-y divide-[#004080]">
                            
                            <div v-for="activity in filteredActivities" :key="activity.id" 
                                class="p-6 transition duration-150 ease-in-out flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 group">
                                
                                <div class="flex items-start gap-4">
                                    <div class="p-3 rounded-lg bg-[#002244] border border-[#004080] group-hover:border-blue-500/50 transition">
                                        <FileText class="w-6 h-6 text-blue-300" />
                                    </div>

                                    <div>
                                        <Link :href="route('activities.show', activity.id)" class="group/title">
                                            <h3 class="font-bold text-lg text-[#212121] group-hover:text-[#808080] group-hover/title:underline transition mb-1 cursor-pointer">
                                                {{ activity.title }}
                                            </h3>
                                        </Link>
                                        
                                        <p class="text-sm text-gray-700 mb-2 line-clamp-2">{{ activity.description }}</p>
                                        
                                        <div class="flex items-center gap-4 text-xs text-gray-400">
                                            <span v-if="activity.due_date" class="flex items-center text-red-300 font-semibold">
                                                <Calendar class="w-3 h-3 mr-1" />
                                                Due: {{ new Date(activity.due_date).toLocaleDateString() }}
                                            </span>
                                            <span class="font-semibold">Posted: {{ new Date(activity.created_at).toLocaleDateString() }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3 self-end sm:self-center">
                                    
                                    <a v-if="activity.file_path && activity.type && activities.show !== 'Submission'" 
                                       :href="route('activities.show', activity.id)" 
                                       class="flex items-center px-4 py-2 bg-[#0060bf] hover:bg-[#004080] text-blue-200 border border-[#004080] rounded-md text-sm font-medium transition"
                                    >
                                        <Eye class="w-4 h-4 mr-2" />
                                        View Resource
                                    </a>

                                    <template v-if="activity.type === 'Submission'">
                                        <div v-if="activity.my_submission" class="flex flex-col items-end">
                                            <button disabled class="bg-green-900/30 text-green-400 border border-green-600/50 font-bold py-1 px-3 rounded-md text-sm shadow-sm cursor-not-allowed flex items-center">
                                                <CheckCircle class="w-4 h-4 mr-2" />
                                                Submitted
                                            </button>
                                            <Link :href="route('activities.show', activity.id)" class="text-[10px] text-blue-400 hover:text-white underline mt-1 transition">
                                                View/Edit
                                            </Link>
                                        </div>

                                        <!-- <Link v-else :href="route('activities.show', activity.id)" 
                                            class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-md text-sm font-semibold shadow hover:shadow-lg transition flex items-center border border-blue-500/50" 
                                            title="View Activity">
                                            <Eye class="w-4 h-4 mr-2" />
                                            View
                                        </Link> -->
                                    </template>

                                    <!-- <Link v-if="activity.type === 'Quiz' || activity.type === 'Exercise'" 
                                          :href="route('activities.play', activity.id)" 
                                          class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-md text-sm font-semibold shadow hover:shadow-lg transition flex items-center border border-purple-500/50" 
                                          title="Play Quiz">
                                        <Gamepad2 class="w-4 h-4 mr-2" />
                                        Play
                                    </Link> -->

                                    <div v-if="can.manage_activities" class="flex items-center gap-1 pl-3 border-l border-[#004080] ml-2">
                                        <Link :href="route('activities.edit', activity.id)" class="p-2 text-gray-400 hover:text-[#FFD700] hover:bg-[#002244] rounded-md transition" title="Edit Activity">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                        <button @click="confirmDelete(activity.id)" class="p-2 text-gray-400 hover:text-red-400 hover:bg-[#002244] rounded-md transition" title="Delete Activity">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="bg-[#002244] p-4 rounded-full mb-4">
                                <Search class="w-8 h-8 text-gray-500" />
                            </div>
                            <h3 class="text-lg font-medium text-white">No activities found</h3>
                            <p class="text-gray-400 mt-1">Try adjusting your search terms.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>