<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

// Compute whether we should show the avatar image
const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);

const userRole = computed(() => {
    if (props.user?.roles && props.user.roles.length > 0) {
        const role = props.user.roles[0];
        // Capitalize it (e.g., "admin" -> "Admin")
        return role.charAt(0).toUpperCase() + role.slice(1);
    }
    return 'User'; // A fallback if no role is found
});

const avatarFallback = computed(() => {
    if (props.user.username) {
        return props.user.username.substring(0, 2).toUpperCase();
    }
    return props.user.name.substring(0, 2).toUpperCase();
});

const mainText = computed(() => {
    if (props.showEmail) {
        return props.user.name; // In dropdown, show Full Name
    }
    //return props.user.username ? `@${props.user.username}` : props.user.name; // In sidebar, show Username
    return '@'+props.user.username;
});

// 2. Compute the subtitle based on the state
const subtitle = computed(() => {
    if (props.showEmail) {
        return props.user.email; // In dropdown, show Email
    }
    return userRole.value; // In sidebar, show Role
});
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="user.name" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ avatarFallback }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ mainText }}</span>

        <span class="truncate text-xs text-muted-foreground">
            {{ subtitle }}
        </span>
    </div>
</template>
