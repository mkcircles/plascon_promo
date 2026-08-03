<template>
    <div class="backdrop-blur-xl bg-white/70 border border-white/45 rounded-3xl shadow-2xl p-8 sm:p-10 transition-all duration-300 hover:shadow-blue-500/10">
        <!-- Logo and header -->
        <div class="text-center mb-8">
            <div class="relative inline-block mb-4 group">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-red-600 rounded-full blur opacity-25 group-hover:opacity-40 transition duration-500"></div>
                <img src="images/logo.png" alt="logo" class="relative w-32 mx-auto drop-shadow-md transition-transform duration-500 group-hover:scale-105" />
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-800">Promo Portal</h2>
            <p v-if="mode === 'login'" class="text-sm text-slate-600 mt-1">Sign in to your administration panel</p>
            <p v-else-if="mode === 'forgot'" class="text-sm text-slate-600 mt-1">Request a password reset code</p>
            <p v-else-if="mode === 'reset'" class="text-sm text-slate-600 mt-1">Enter code to reset your password</p>
        </div>

        <!-- Success Alert -->
        <div v-if="successMessage" class="mb-6 transform translate-y-0 transition-all duration-300">
            <div class="flex items-center bg-emerald-50/80 border border-emerald-200/50 rounded-xl p-4 text-sm text-emerald-600 backdrop-blur-sm" role="alert">
                <svg class="w-5 h-5 inline mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    {{ successMessage }}
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        <div v-if="authError" class="mb-6 transform translate-y-0 transition-all duration-300">
            <div class="flex items-center bg-red-50/80 border border-red-200/50 rounded-xl p-4 text-sm text-red-600 backdrop-blur-sm" role="alert">
                <svg class="w-5 h-5 inline mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <span class="font-semibold">Error:</span> {{ authError }}
                </div>
            </div>
        </div>

        <!-- Mode: LOGIN -->
        <form v-if="mode === 'login'" @submit.prevent="login" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2 px-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <input 
                        type="email" 
                        v-model="user.username" 
                        required
                        placeholder="Enter your email"
                        class="text-sm block pl-11 pr-4 py-3 rounded-xl w-full bg-white/60 border border-slate-300/60 placeholder-slate-400 text-slate-800 shadow-sm focus:placeholder-slate-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all duration-200" 
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2 px-1">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <input 
                        type="password" 
                        v-model="user.password" 
                        required
                        placeholder="••••••••"
                        class="text-sm block pl-11 pr-4 py-3 rounded-xl w-full bg-white/60 border border-slate-300/60 placeholder-slate-400 text-slate-800 shadow-sm focus:placeholder-slate-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all duration-200" 
                    />
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center text-slate-600 font-medium cursor-pointer">
                    <input 
                        type="checkbox"
                        class="rounded border-slate-300/80 text-blue-600 focus:ring-blue-500/30 focus:ring-offset-0 h-4 w-4 transition duration-150" 
                    />
                    <span class="ml-2 select-none">Remember Me</span>
                </label>
                <a href="#" @click.prevent="switchMode('forgot')" class="font-semibold text-blue-600 hover:text-blue-700 transition duration-150">
                    Forgot Password?
                </a>
            </div>

            <div class="pt-2">
                <button 
                    type="submit"
                    class="relative group w-full overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3.5 shadow-lg shadow-blue-500/20 hover:shadow-xl hover:shadow-blue-500/30 active:scale-[0.99] transition-all duration-150"
                >
                    <span class="relative z-10">Sign In</span>
                    <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-1000 ease-in-out"></div>
                </button>
            </div>
        </form>

        <!-- Mode: FORGOT PASSWORD -->
        <form v-else-if="mode === 'forgot'" @submit.prevent="sendResetCode" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2 px-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <input 
                        type="email" 
                        v-model="forgotEmail" 
                        required
                        placeholder="Enter your registered email"
                        class="text-sm block pl-11 pr-4 py-3 rounded-xl w-full bg-white/60 border border-slate-300/60 placeholder-slate-400 text-slate-800 shadow-sm focus:placeholder-slate-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all duration-200" 
                    />
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <a href="#" @click.prevent="switchMode('login')" class="font-semibold text-blue-600 hover:text-blue-700 transition duration-150">
                    &larr; Back to Login
                </a>
            </div>

            <div class="pt-2">
                <button 
                    type="submit"
                    :disabled="loading"
                    class="relative group w-full overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3.5 shadow-lg shadow-blue-500/20 hover:shadow-xl hover:shadow-blue-500/30 active:scale-[0.99] transition-all duration-150 disabled:opacity-50"
                >
                    <span class="relative z-10">{{ loading ? 'Sending...' : 'Send Reset Code' }}</span>
                </button>
            </div>
        </form>

        <!-- Mode: RESET PASSWORD -->
        <form v-else-if="mode === 'reset'" @submit.prevent="resetPassword" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2 px-1">Verification Code</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        v-model="resetToken" 
                        required
                        placeholder="Enter the 6-digit code"
                        class="text-sm block pl-11 pr-4 py-3 rounded-xl w-full bg-white/60 border border-slate-300/60 placeholder-slate-400 text-slate-800 shadow-sm focus:placeholder-slate-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all duration-200" 
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2 px-1">New Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <input 
                        type="password" 
                        v-model="newPassword" 
                        required
                        placeholder="••••••••"
                        class="text-sm block pl-11 pr-4 py-3 rounded-xl w-full bg-white/60 border border-slate-300/60 placeholder-slate-400 text-slate-800 shadow-sm focus:placeholder-slate-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all duration-200" 
                    />
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <a href="#" @click.prevent="switchMode('login')" class="font-semibold text-blue-600 hover:text-blue-700 transition duration-150">
                    &larr; Back to Login
                </a>
            </div>

            <div class="pt-2">
                <button 
                    type="submit"
                    :disabled="loading"
                    class="relative group w-full overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3.5 shadow-lg shadow-blue-500/20 hover:shadow-xl hover:shadow-blue-500/30 active:scale-[0.99] transition-all duration-150 disabled:opacity-50"
                >
                    <span class="relative z-10">{{ loading ? 'Resetting...' : 'Reset Password' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useAuthStore } from "@/store/authStore";
import router from "@/routes/index";
import axios from "axios";

const mode = ref("login");
const authError = ref("");
const successMessage = ref("");
const loading = ref(false);

const user = ref({
    username: "",
    password: "",
});

const forgotEmail = ref("");
const resetToken = ref("");
const newPassword = ref("");

const store = useAuthStore();

const switchMode = (newMode) => {
    mode.value = newMode;
    authError.value = "";
    successMessage.value = "";
    resetToken.value = "";
    newPassword.value = "";
};

const login = async () => {
    if (user.value.username === "" || user.value.password === "") {
        authError.value = "Please fill all fields";
        return;
    }
    authError.value = "";
    store
        .login(user.value)
        .then(() => {
            router.push({ name: "dashboard" });
        })
        .catch((err) => {
            authError.value = err.response?.data?.message || "Login failed";
        });
};

const sendResetCode = async () => {
    if (forgotEmail.value === "") {
        authError.value = "Please enter your email address";
        return;
    }
    loading.value = true;
    authError.value = "";
    successMessage.value = "";

    axios.post("/api/forgot-password", { email: forgotEmail.value })
        .then((res) => {
            successMessage.value = res.data.message;
            mode.value = "reset";
        })
        .catch((err) => {
            authError.value = err.response?.data?.message || "An error occurred. Make sure email exists.";
        })
        .finally(() => {
            loading.value = false;
        });
};

const resetPassword = async () => {
    if (resetToken.value === "" || newPassword.value === "") {
        authError.value = "Please fill all fields";
        return;
    }
    loading.value = true;
    authError.value = "";
    successMessage.value = "";

    axios.post("/api/reset-password", {
        email: forgotEmail.value,
        token: resetToken.value,
        password: newPassword.value
    })
        .then((res) => {
            successMessage.value = "Password reset successfully. You can now log in.";
            mode.value = "login";
            user.value.username = forgotEmail.value;
            user.value.password = "";
        })
        .catch((err) => {
            authError.value = err.response?.data?.message || "Reset failed. Please check your verification code.";
        })
        .finally(() => {
            loading.value = false;
        });
};
</script>

<style></style>
