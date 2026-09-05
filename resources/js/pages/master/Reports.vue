<template>
  <div class="container mx-auto py-8 px-4">
    <div class="mb-8">
      <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Reports Panel</h2>
      <p class="text-slate-500 mt-1">Generate and download CSV reports for code activations and inbound messages.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Codes Activation Report Card -->
      <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-800">Used Codes Report</h3>
              <p class="text-xs text-slate-400">Restricted to activated/used codes only</p>
            </div>
          </div>

          <div class="space-y-4 my-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">From Date</label>
                <input 
                  type="date" 
                  v-model="codesFilter.fromDate" 
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">To Date</label>
                <input 
                  type="date" 
                  v-model="codesFilter.todate" 
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Brand</label>
              <select 
                v-model="codesFilter.brand" 
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
              >
                <option value="all">All Brands</option>
                <option v-for="brand in dbBrands" :key="brand" :value="brand">{{ brand }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="mt-6 border-t border-slate-50 pt-6">
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 flex gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-xs text-amber-800 leading-normal">
              <strong>Security Notice:</strong> To prevent code leakages and misuse, you can only export codes that have been marked as <strong>used (activated)</strong>. Pending codes are excluded from CSV exports.
            </p>
          </div>

          <button 
            @click="downloadCodesReport" 
            :disabled="codesLoading"
            class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-semibold py-3 px-4 rounded-2xl shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 active:scale-[0.98] transition-all"
          >
            <svg v-if="codesLoading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            {{ codesLoading ? 'Generating Report...' : 'Download Used Codes CSV' }}
          </button>
        </div>
      </div>

      <!-- Inbound Messages Report Card -->
      <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-6 flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-800">Inbound Messages Report</h3>
              <p class="text-xs text-slate-400">All incoming SMS metrics and statuses</p>
            </div>
          </div>

          <div class="space-y-4 my-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">From Date</label>
                <input 
                  type="date" 
                  v-model="messagesFilter.fromDate" 
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">To Date</label>
                <input 
                  type="date" 
                  v-model="messagesFilter.todate" 
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Status</label>
              <select 
                v-model="messagesFilter.status" 
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"
              >
                <option value="Any">Any Status</option>
                <option value="valid">Valid Code Sent</option>
                <option value="invalid">Invalid Code Sent</option>
              </select>
            </div>
          </div>
        </div>

        <div class="mt-6 border-t border-slate-50 pt-6">
          <button 
            @click="downloadMessagesReport" 
            :disabled="messagesLoading"
            class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-300 text-white font-semibold py-3 px-4 rounded-2xl shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/20 active:scale-[0.98] transition-all"
          >
            <svg v-if="messagesLoading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            {{ messagesLoading ? 'Generating Report...' : 'Download Messages CSV' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useAuthStore } from "@/store/authStore";

// Initialize default date ranges (last 30 days)
const todayStr = new Date().toISOString().split("T")[0];
const thirtyDaysAgoStr = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split("T")[0];

const authStore = useAuthStore();
const dbBrands = ref([]);

const fetchBrands = async () => {
  if (!authStore.token) return;
  try {
    const res = await axios.get("/api/brands", {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });
    dbBrands.value = res.data;
  } catch (err) {
    console.error("Failed to fetch distinct brands:", err);
  }
};

onMounted(() => {
  fetchBrands();
});

const codesFilter = ref({
  fromDate: thirtyDaysAgoStr,
  todate: todayStr,
  brand: "all"
});

const messagesFilter = ref({
  fromDate: thirtyDaysAgoStr,
  todate: todayStr,
  status: "Any"
});

const codesLoading = ref(false);
const messagesLoading = ref(false);

const downloadCodesReport = async () => {
  codesLoading.value = true;
  try {
    const response = await axios.get("/api/reports/codes", {
      params: codesFilter.value,
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      responseType: "blob"
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `used_codes_report_${codesFilter.value.fromDate}_to_${codesFilter.value.todate}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error("Failed to download codes report:", error);
    alert("Failed to generate and download report. Please check date inputs and try again.");
  } finally {
    codesLoading.value = false;
  }
};

const downloadMessagesReport = async () => {
  messagesLoading.value = true;
  try {
    const response = await axios.get("/api/reports/in-messages", {
      params: messagesFilter.value,
      headers: {
        Authorization: `Bearer ${authStore.token}`
      },
      responseType: "blob"
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `in_messages_report_${messagesFilter.value.fromDate}_to_${messagesFilter.value.todate}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error("Failed to download messages report:", error);
    alert("Failed to generate and download report. Please check date inputs and try again.");
  } finally {
    messagesLoading.value = false;
  }
};
</script>
