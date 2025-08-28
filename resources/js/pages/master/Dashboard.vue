<template>
    <div class="relative pt-16 pb-16 bg-lightBlue-500">
        <div class="px-4 md:px-6 mx-auto w-full container mx-auto">
            <div>
                <div class="flex flex-wrap">
                    <div class="w-full lg:w-6/12 xl:w-3/12 px-4">
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white rounded-lg mb-6 xl:mb-0 shadow-lg"
                        >
                            <div class="flex-auto p-4">
                                <div class="flex flex-wrap">
                                    <div
                                        class="relative w-full pr-4 max-w-full flex-grow flex-1"
                                    >
                                        <h5
                                            class="text-blueGray-400 uppercase font-bold text-xs"
                                        >
                                            Total Codes
                                        </h5>
                                        <span class="font-bold text-xl">{{
                                            codes
                                        }}</span>
                                    </div>
                                    <div
                                        class="relative w-auto pl-4 flex-initial"
                                    >
                                        <div
                                            class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-red-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="#FFFFFF"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M3 3v18h18" />
                                                <path
                                                    d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-blueGray-500 mt-4">
                                    <span class="text-emerald-500 mr-2"
                                        ><i class="fas fa-arrow-up"></i>
                                        {{ valid_codes }}</span
                                    ><span class="whitespace-nowrap"
                                        >Used Codes</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full lg:w-6/12 xl:w-3/12 px-4">
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white rounded-lg mb-6 xl:mb-0 shadow-lg"
                        >
                            <div class="flex-auto p-4">
                                <div class="flex flex-wrap">
                                    <div
                                        class="relative w-full pr-4 max-w-full flex-grow flex-1"
                                    >
                                        <h5
                                            class="text-blueGray-400 uppercase font-bold text-xs"
                                        >
                                            Used Codes
                                        </h5>
                                        <span class="font-bold text-xl">{{
                                            valid_codes
                                        }}</span>
                                    </div>
                                    <div
                                        class="relative w-auto pl-4 flex-initial"
                                    >
                                        <div
                                            class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-orange-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="#ffffff"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path
                                                    d="M21.21 15.89A10 10 0 1 1 8 2.83"
                                                ></path>
                                                <path
                                                    d="M22 12A10 10 0 0 0 12 2v10z"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-blueGray-500 mt-4">
                                    <span class="text-red-500 mr-2"
                                        ><i class="fas fa-arrow-down"></i>
                                        {{ airtime_winner }}</span
                                    ><span class="whitespace-nowrap"
                                        >Won Airtime</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full lg:w-6/12 xl:w-3/12 px-4">
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white rounded-lg mb-6 xl:mb-0 shadow-lg"
                        >
                            <div class="flex-auto p-4">
                                <div class="flex flex-wrap">
                                    <div
                                        class="relative w-full pr-4 max-w-full flex-grow flex-1"
                                    >
                                        <h5
                                            class="text-blueGray-400 uppercase font-bold text-xs"
                                        >
                                            Airtime Balance (ugx)
                                        </h5>
                                        <span class="font-bold text-xl">{{
                                            airtime
                                        }}</span>
                                    </div>
                                    <div
                                        class="relative w-auto pl-4 flex-initial"
                                    >
                                        <div
                                            class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-pink-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="#ffffff"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path
                                                    d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"
                                                ></path>
                                                <circle
                                                    cx="8.5"
                                                    cy="7"
                                                    r="4"
                                                ></circle>
                                                <polyline
                                                    points="17 11 19 13 23 9"
                                                ></polyline>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-blueGray-500 mt-4">
                                    <span class="text-orange-500 mr-2"
                                        ><i class="fas fa-arrow-down"></i>
                                        {{ airtime_given }}</span
                                    ><span class="whitespace-nowrap"
                                        >Airtime Given Out</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full lg:w-6/12 xl:w-3/12 px-4">
                        <div
                            class="relative flex flex-col min-w-0 break-words bg-white rounded-lg mb-6 xl:mb-0 shadow-lg"
                        >
                            <div class="flex-auto p-4">
                                <div class="flex flex-wrap">
                                    <div
                                        class="relative w-full pr-4 max-w-full flex-grow flex-1"
                                    >
                                        <h5
                                            class="text-blueGray-400 uppercase font-bold text-xs"
                                        >
                                            Received SMS
                                        </h5>
                                        <span class="font-bold text-xl">{{
                                            received_messages
                                        }}</span>
                                    </div>
                                    <div
                                        class="relative w-auto pl-4 flex-initial"
                                    >
                                        <div
                                            class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow-lg rounded-full bg-lightBlue-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="#000000"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path
                                                    d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-blueGray-500 mt-4">
                                    <span class="text-emerald-500 mr-2"
                                        ><i class="fas fa-arrow-up"></i>
                                        {{ valid_messages }}</span
                                    ><span class="whitespace-nowrap"
                                        >Valid Codes</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</template>

<script setup>
import { useAuthStore } from "@/store/authStore";
import axios from "axios";
import { ref, onMounted, computed } from "vue";
import MessageChart from "@/components/Chart/MessageChart.vue";

let codes = ref();
let valid_codes = ref();
let airtime = ref();
let received_messages = ref();
let valid_messages = ref();
let airtime_winner = ref();
let airtime_given = ref();


const summary = axios.get("/api/summary", {
    headers: {
        Authorization: "Bearer " + useAuthStore().token,
    },
});


onMounted(() => {
    summary
        .then((res) => {
            codes.value = res.data.codes;
            valid_codes.value = res.data.valid_codes;
            airtime.value = res.data.airtime;
            received_messages.value = res.data.received_messages;
            valid_messages.value = res.data.valid_messages;
            airtime_winner.value = res.data.airtime_winner;
            airtime_given.value = res.data.airtime_given;
        })
        .catch((err) => {
            console.log(err);
        })
        .finally(() => {});
});


</script>

<style></style>
