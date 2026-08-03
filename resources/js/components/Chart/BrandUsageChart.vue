<template>
  <div class="bg-white rounded-3xl shadow-xl p-6 border border-slate-100 transition-all duration-300">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div>
        <h3 class="text-lg font-bold text-slate-800">Used Codes by Brand</h3>
        <p class="text-sm text-slate-500">Track code activations per brand over time</p>
      </div>
      <div class="flex bg-slate-100 rounded-xl p-1 text-xs font-semibold">
        <button 
          @click="changeDays(7)" 
          :class="[days === 7 ? 'bg-white text-blue-600 shadow-md shadow-blue-500/5' : 'text-slate-600 hover:text-slate-900', 'px-4 py-2 rounded-lg transition-all duration-150']"
        >
          7 Days
        </button>
        <button 
          @click="changeDays(30)" 
          :class="[days === 30 ? 'bg-white text-blue-600 shadow-md shadow-blue-500/5' : 'text-slate-600 hover:text-slate-900', 'px-4 py-2 rounded-lg transition-all duration-150']"
        >
          30 Days
        </button>
      </div>
    </div>

    <div v-if="loading" class="h-[350px] flex items-center justify-center">
      <div class="flex flex-col items-center gap-2">
        <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm text-slate-500 font-medium">Loading data...</span>
      </div>
    </div>
    <div v-else class="w-full">
      <apexchart 
        type="line" 
        height="350" 
        :options="chartOptions" 
        :series="series" 
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useAuthStore } from "../../store/authStore";

const days = ref(7);
const loading = ref(true);
const series = ref([]);
const chartOptions = ref({
  chart: {
    fontFamily: 'Outfit, Inter, sans-serif',
    toolbar: {
      show: false
    },
    zoom: {
      enabled: false
    }
  },
  stroke: {
    curve: 'smooth',
    width: 3
  },
  xaxis: {
    categories: [],
    labels: {
      style: {
        colors: '#64748b',
        fontSize: '11px'
      }
    },
    axisBorder: {
      show: false
    },
    axisTicks: {
      show: false
    }
  },
  yaxis: {
    labels: {
      style: {
        colors: '#64748b',
        fontSize: '11px'
      }
    }
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 4
  },
  legend: {
    position: 'top',
    horizontalAlign: 'left',
    fontSize: '12px',
    markers: {
      radius: 12
    }
  },
  tooltip: {
    theme: 'light',
    y: {
      formatter: function (val) {
        return val + " codes";
      }
    }
  }
});

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/brand-usage-chart?days=${days.value}`, {
      headers: {
        Authorization: "Bearer " + useAuthStore().token,
      },
    });
    
    // Map datasets to apexcharts series format
    series.value = res.data.datasets.map(dataset => ({
      name: dataset.label,
      data: dataset.data,
      color: dataset.borderColor
    }));

    chartOptions.value = {
      ...chartOptions.value,
      xaxis: {
        ...chartOptions.value.xaxis,
        categories: res.data.dates
      }
    };
  } catch (error) {
    console.error("Error fetching brand usage chart data:", error);
  } finally {
    loading.value = false;
  }
};

const changeDays = (newDays) => {
  days.value = newDays;
  fetchData();
};

onMounted(() => {
  fetchData();
});
</script>
