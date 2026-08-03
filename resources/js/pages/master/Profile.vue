<template>
  <div class="container mx-auto py-8 px-4 max-w-4xl">
    <div class="mb-8">
      <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Your Profile</h2>
      <p class="text-slate-500 mt-1">Manage your account details and update your security settings.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- User Info Info Card -->
      <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 text-center flex flex-col items-center justify-center">
        <div class="w-24 h-24 bg-gradient-to-tr from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-lg shadow-blue-500/20 mb-4">
          {{ userInitials }}
        </div>
        <h3 class="text-xl font-bold text-slate-800">{{ currentUser.name }}</h3>
        <p class="text-sm text-slate-500 mt-1">{{ currentUser.email }}</p>
        <span class="mt-4 px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold uppercase tracking-wider">
          Administrator
        </span>
      </div>

      <!-- Password Change Card -->
      <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 md:col-span-2">
        <h3 class="text-xl font-bold text-slate-800 mb-2">Change Password</h3>
        <p class="text-sm text-slate-400 mb-6">Ensure your account is using a long, random password to stay secure.</p>

        <!-- Status Alerts -->
        <div v-if="successMsg" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6 flex gap-3 text-emerald-800 text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ successMsg }}</span>
        </div>

        <div v-if="errorMsg" class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex gap-3 text-red-800 text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ errorMsg }}</span>
        </div>

        <!-- Form -->
        <form @submit.prevent="updatePassword" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Current Password</label>
            <input 
              type="password" 
              v-model="form.current_password" 
              required
              placeholder="••••••••"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">New Password</label>
            <input 
              type="password" 
              v-model="form.new_password" 
              required
              placeholder="••••••••"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Confirm New Password</label>
            <input 
              type="password" 
              v-model="form.new_password_confirmation" 
              required
              placeholder="••••••••"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
            />
          </div>

          <div class="pt-4">
            <button 
              type="submit" 
              :disabled="loading"
              class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-semibold py-3 px-4 rounded-2xl shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 active:scale-[0.98] transition-all"
            >
              <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Updating...' : 'Save New Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import { useAuthStore } from "@/store/authStore";

const authStore = useAuthStore();
const currentUser = computed(() => authStore.user || { name: "User", email: "" });

const userInitials = computed(() => {
  if (!currentUser.value.name) return "U";
  return currentUser.value.name
    .split(" ")
    .map(n => n[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();
});

const form = ref({
  current_password: "",
  new_password: "",
  new_password_confirmation: ""
});

const loading = ref(false);
const successMsg = ref("");
const errorMsg = ref("");

const updatePassword = async () => {
  loading.value = true;
  successMsg.value = "";
  errorMsg.value = "";

  try {
    const res = await axios.post("/api/change-password", form.value, {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    });

    if (res.data.status) {
      successMsg.value = res.data.message;
      // Reset form
      form.value.current_password = "";
      form.value.new_password = "";
      form.value.new_password_confirmation = "";
    }
  } catch (error) {
    if (error.response && error.response.data) {
      errorMsg.value = error.response.data.message || "Failed to update password.";
      if (error.response.data.errors) {
        // Grab first validation error
        const firstErrKey = Object.keys(error.response.data.errors)[0];
        errorMsg.value = error.response.data.errors[firstErrKey][0];
      }
    } else {
      errorMsg.value = "An unexpected error occurred.";
    }
  } finally {
    loading.value = false;
  }
};
</script>
