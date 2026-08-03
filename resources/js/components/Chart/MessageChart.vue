<template>
    <Bar :chart-options="chartOptions" :chart-data="chartData" />
</template>

<script setup>
import { Bar } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from "chart.js";
import { onMounted, ref } from "vue";
import axios from "axios";
import { useAuthStore } from "../../store/authStore";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
);

const chartData = ref({
    labels: [],
    datasets: [{ data: [] }],
});

const chartOptions = {
    responsive: true,
};

onMounted(() => {
    axios
        .get("/api/chart", {
            headers: {
                Authorization: `Bearer ${useAuthStore().token}`,
            },
        })
        .then((response) => {
            chartData.value = {
                labels: response.data.dates,
                datasets: [
                    {
                        label: "Messages",
                        backgroundColor: "#f87979",
                        data: response.data.counts,
                    },
                ],
            };
        });
});
</script>
