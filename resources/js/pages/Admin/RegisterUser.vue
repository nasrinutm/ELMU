<template>
  <div class="p-6 max-w-md mx-auto bg-white rounded shadow">
    <h2 class="text-xl font-semibold mb-4">Register New User</h2>
    <form @submit.prevent="submit">
      <div class="mb-3">
        <label class="block">Name</label>
        <input v-model="form.name" type="text" class="border p-2 w-full" />
      </div>
      <div class="mb-3">
        <label class="block">Email</label>
        <input v-model="form.email" type="email" class="border p-2 w-full" />
      </div>
      <div class="mb-3">
        <label class="block">Password</label>
        <input v-model="form.password" type="password" class="border p-2 w-full" />
      </div>
      <div class="mb-3">
        <label class="block">Confirm Password</label>
        <input v-model="form.password_confirmation" type="password" class="border p-2 w-full" />
      </div>
      <div class="mb-3">
        <label class="block">Role</label>
        <select v-model="form.role" class="border p-2 w-full">
          <option value="admin">Admin</option>
          <option value="teacher">Teacher</option>
          <option value="student">Student</option>
        </select>
      </div>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        Register
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
})

function submit() {
  router.post('/admin/register', form)
}
</script>
