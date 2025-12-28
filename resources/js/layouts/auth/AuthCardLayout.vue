<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js"; // <--- Import the route helper
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

// No need to import 'home' from "@/routes"
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-muted/40 p-4">
    <div class="w-full max-w-sm space-y-4">
      <div class="flex justify-center">
        <Link :href="route('home')" class="flex items-center gap-2 font-bold text-xl">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="h-8 w-8 text-primary"
          >
            <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3" />
          </svg>
          <span class="text-foreground">ELMU</span>
        </Link>
      </div>

      <Card>
        <CardHeader class="space-y-1">
          <slot name="header" /> 
        </CardHeader>
        <CardContent>
          <slot />
        </CardContent>
      </Card>
    </div>
  </div>
</template>