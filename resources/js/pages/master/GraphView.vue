<template>
    <div class="relative pt-16 pb-16 bg-lightBlue-500">
        <div class="px-4 md:px-6 mx-auto w-full container mx-auto">
            <MessageChart />
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from "@/store/authStore";
import axios from "axios";
import { ref, onMounted, computed } from "vue";
import MessageChart from "@/components/Chart/MessageChart.vue";

//Chart Data
let datasets = ref();
let labels = ref();

const fetchData = axios.get("/api/chart", {
    headers: {
        Authorization: "Bearer " + useAuthStore().token,
    },
});

onMounted(() => {
    fetchData
        .then((res) => {
            labels.value = res.data.dates;
            datasets.value = res.data.counts;
        })
        .catch((err) => {
            console.log(err);
        })
        .finally(() => {});
});
</script>

<style></style>
