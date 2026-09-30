<template>
  <div class="bg-white rounded-3xl shadow-xl p-6 border border-slate-100 transition-all duration-300">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
      <div>
        <div class="flex items-center gap-3">
          <div class="w-2.5 h-6 bg-gradient-to-b from-blue-600 to-[#e31b23] rounded-full"></div>
          <div>
            <h3 class="text-lg font-bold text-slate-800">Daily Codes Activity</h3>
            <p class="text-sm text-slate-500">Track daily total, valid, and invalid code submissions</p>
          </div>
        </div>

        <!-- Quick Summary Metrics -->
        <div v-if="!loading && summary" class="flex flex-wrap items-center gap-2 mt-3 pt-1">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            Total: {{ Number(summary.total || 0).toLocaleString() }}
          </span>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Valid: {{ Number(summary.valid || 0).toLocaleString() }}
          </span>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            Invalid: {{ Number(summary.invalid || 0).toLocaleString() }}
          </span>
        </div>
      </div>

      <!-- Timeframe Selector -->
      <div class="flex bg-slate-100/90 rounded-2xl p-1 text-xs font-semibold border border-slate-200/50 self-end lg:self-center">
        <button 
          @click="changeDays(7)" 
          :class="[days === 7 ? 'bg-white text-blue-600 shadow-md shadow-slate-200/80 font-bold' : 'text-slate-600 hover:text-slate-900', 'px-4 py-2 rounded-xl transition-all duration-150']"
        >
          7 Days
        </button>
        <button 
          @click="changeDays(30)" 
          :class="[days === 30 ? 'bg-white text-blue-600 shadow-md shadow-slate-200/80 font-bold' : 'text-slate-600 hover:text-slate-900', 'px-4 py-2 rounded-xl transition-all duration-150']"
        >
          30 Days
        </button>
      </div>
    </div>

    <!-- Chart Body -->
    <div v-if="loading" class="h-[360px] flex items-center justify-center">
      <div class="flex flex-col items-center gap-3">
        <svg class="animate-spin h-9 w-9 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm text-slate-500 font-medium">Loading codes activity...</span>
      </div>
    </div>

    <div v-else class="w-full">
      <apexchart 
        type="area" 
        height="360" 
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
const summary = ref(null);
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
  colors: ['#2563eb', '#10b981', '#ef4444'],
  stroke: {
    curve: 'smooth',
    width: [3, 2.5, 2.5]
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.35,
      opacityTo: 0.05,
      stops: [0, 90, 100]
    }
  },
  markers: {
    size: 0,
    hover: {
      size: 5
    }
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
      },
      formatter: function (val) {
        return Math.round(val);
      }
    },
    min: 0
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 4
  },
  legend: {
    position: 'top',
    horizontalAlign: 'right',
    fontSize: '12px',
    markers: {
      radius: 12
    }
  },
  tooltip: {
    theme: 'light',
    shared: true,
    intersect: false,
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
    const res = await axios.get(`/api/daily-codes-chart?days=${days.value}`, {
      headers: {
        Authorization: "Bearer " + useAuthStore().token,
      },
    });

    summary.value = res.data.summary || {
      total: (res.data.total || []).reduce((a, b) => a + b, 0),
      valid: (res.data.valid || []).reduce((a, b) => a + b, 0),
      invalid: (res.data.invalid || []).reduce((a, b) => a + b, 0),
    };

    series.value = [
      {
        name: "Total Codes",
        data: res.data.total || []
      },
      {
        name: "Valid Codes",
        data: res.data.valid || []
      },
      {
        name: "Invalid Codes",
        data: res.data.invalid || []
      }
    ];

    chartOptions.value = {
      ...chartOptions.value,
      xaxis: {
        ...chartOptions.value.xaxis,
        categories: res.data.dates || []
      }
    };
  } catch (error) {
    console.error("Error fetching daily codes chart data:", error);
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
