<template>
    <div class="relative flex flex-wrap">
        <div class="w-full relative">
            <div class="mt-6">
                <div
                    class="mb-5 pb-1border-b-2 text-center font-base text-gray-700"
                >
                    <img
                        src="images/logo.png"
                        alt="logo"
                        class="w-1/2 mx-auto"
                    />
                </div>
                <!-- <div class="text-center font-semibold text-black">
            Lorem ipsum dolor, sit amet?
          </div> -->
                <div v-if="authError">
                    <div class="flex bg-red-100 rounded-lg p-4 mb-4 text-sm text-red-700" role="alert">
                      <svg class="w-5 h-5 inline mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                      <div>
                          <span class="font-medium">Error!</span> {{ authError }}
                      </div>
                  </div>
                </div>

                <form class="mt-8" @submit.prevent="login">
                    <div class="mx-auto max-w-lg">
                        <div class="py-2">
                            <span class="px-1 text-sm text-gray-600"
                                >Username</span
                            >
                            <input
                                placeholder=""
                                type="text"
                                v-model="user.username"
                                required
                                class="text-md block px-3 py-2 rounded-lg w-full bg-white border-2 border-gray-300 placeholder-gray-600 shadow-md focus:placeholder-gray-500 focus:bg-white focus:border-gray-600 focus:outline-none"
                            />
                        </div>
                        <div class="py-2" x-data="{ show: true }">
                            <span class="px-1 text-sm text-gray-600"
                                >Password</span
                            >
                            <div class="relative">
                                <input
                                    placeholder=""
                                    v-model="user.password"
                                    type="password"
                                    required
                                    class="text-md block px-3 py-2 rounded-lg w-full bg-white border-2 border-gray-300 placeholder-gray-600 shadow-md focus:placeholder-gray-500 focus:bg-white focus:border-gray-600 focus:outline-none"
                                />
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <label class="block text-gray-500 font-bold my-4"
                                ><input
                                    type="checkbox"
                                    class="leading-loose text-pink-600"
                                />
                                <span
                                    class="py-2 text-sm text-gray-600 leading-snug"
                                >
                                    Remember Me
                                </span></label
                            >
                            <label class="block text-gray-500 font-bold my-4"
                                ><a
                                    href="#"
                                    class="cursor-pointer tracking-tighter text-black border-b-2 border-gray-200 hover:border-gray-400"
                                    ><span>Forgot Password?</span></a
                                ></label
                            >
                        </div>
                        <button
                            type="submit"
                            class="mt-3 text-lg font-semibold bg-gray-800 w-full text-white rounded-lg px-6 py-3 block shadow-xl hover:text-white hover:bg-black"
                        >
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "@vue/reactivity";
import { useAuthStore } from "@/store/authStore";
import router from "@/routes/index";

let authError = ref("");
let user = ref({
    username: "",
    password: "",
});

const store = useAuthStore();

const login = async () => {
    if (user.username == "" || user.password == "") {
        authError.value = "Please fill all fields";
        return;
    }
    store
        .login(user.value)
        .then(() => {
            console.log("login success");
            router.push({ name: "dashboard" });
        })
        .catch((err) => {
            authError.value = err.response.data.message;
        });
};
</script>

<style></style>
