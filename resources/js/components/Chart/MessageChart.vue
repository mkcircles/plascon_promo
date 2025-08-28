<template>
    <Bar
        :chart-options="chartOptions"
        :chart-data="chartData"
        :chart-id="chartId"
        :dataset-id-key="datasetIdKey"
        :plugins="plugins"
        :css-classes="cssClasses"
        :styles="styles"
        :width="width"
        :height="height"
    />
</template>

<script>
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

export default {
    name: "MessageChart",
    components: { Bar },
    props: {
        chartId: {
            type: String,
            default: "bar-chart",
        },
        datasetIdKey: {
            type: String,
            default: "label",
        },
        width: {
            type: Number,
            default: 400,
        },
        height: {
            type: Number,
            default: 200,
        },
        cssClasses: {
            default: "",
            type: String,
        },
        styles: {
            type: Object,
            default: () => {},
        },
        plugins: {
            type: Object,
            default: () => {},
        },
    },
    data() {
        const chartData = ref({
            labels: [],
            datasets: [{ data: [] }],
        });
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

        return {
            chartData,
            chartOptions: {
                responsive: true,
            },
        };
    },
};
</script>
