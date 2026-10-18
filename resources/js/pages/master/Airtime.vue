<template>
  <div class="container mx-auto py-8 px-4 max-w-7xl">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#005cb4] border border-blue-200/60 mb-2">
          <SignalIcon class="w-4 h-4 text-[#005cb4]" />
          <span>Automated Disbursements</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
          Sent Airtime
        </h1>
        <p class="text-slate-500 text-sm mt-1 max-w-2xl">
          Search, audit, and track promotional airtime rewards dispatched to participants across all telecom networks.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="refreshData"
          :disabled="isLoading"
          class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold shadow-sm transition-all active:scale-[0.98] disabled:opacity-50"
          title="Refresh List"
        >
          <ArrowPathIcon class="w-4 h-4 text-slate-500" :class="{ 'animate-spin': isLoading }" />
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Summary Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Total Dispatched -->
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#005cb4] flex items-center justify-center shrink-0">
          <SignalIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Dispatches</p>
          <div class="flex items-baseline gap-2 mt-0.5">
            <span class="text-2xl font-black text-slate-800">{{ (summary.total_count || entries.total || 0).toLocaleString() }}</span>
            <span class="text-xs text-slate-500">records</span>
          </div>
        </div>
      </div>

      <!-- Total Amount -->
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
          <BanknotesIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Disbursed</p>
          <div class="flex items-baseline gap-2 mt-0.5">
            <span class="text-2xl font-black text-slate-800">UGX {{ Number(summary.total_amount || 0).toLocaleString() }}</span>
          </div>
        </div>
      </div>

      <!-- Successful / Sent -->
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
          <CheckCircleIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Delivered / Sent</p>
          <div class="flex items-center gap-2 mt-1">
            <span class="flex h-2 w-2 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-xl font-bold text-slate-800">{{ (summary.sent_count || 0).toLocaleString() }}</span>
            <span class="text-xs text-emerald-600 font-medium">delivered</span>
          </div>
        </div>
      </div>

      <!-- Pending / Failed -->
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
          <ClockIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending / Retries</p>
          <div class="flex items-baseline gap-2 mt-0.5">
            <span class="text-xl font-bold text-slate-800">{{ (summary.pending_count || 0).toLocaleString() }}</span>
            <span class="text-xs text-slate-400 font-normal">pending</span>
            <span v-if="summary.failed_count > 0" class="text-xs text-rose-600 font-semibold ml-1">
              ({{ summary.failed_count }} failed)
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
      <!-- Search & Filters Toolbar -->
      <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 bg-slate-50/50">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-lg">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <MagnifyingGlassIcon class="w-5 h-5" />
          </div>
          <input
            type="text"
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            placeholder="Search by phone (e.g. 078..., 256...), transaction ID, response ID..."
            class="block w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-sans"
          />
          <button
            v-if="searchQuery"
            @click="clearSearchInput"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
            title="Clear text"
          >
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap items-center gap-2.5">
          <!-- Status Filter Pills -->
          <div class="inline-flex bg-white p-1 rounded-xl border border-slate-200 shadow-sm text-xs font-semibold text-slate-600">
            <button
              @click="setStatusFilter('all')"
              class="px-3 py-1.5 rounded-lg transition-all"
              :class="selectedStatus === 'all' ? 'bg-[#005cb4] text-white shadow-sm' : 'hover:text-slate-900'"
            >
              All
            </button>
            <button
              @click="setStatusFilter('sent')"
              class="px-3 py-1.5 rounded-lg transition-all"
              :class="selectedStatus === 'sent' ? 'bg-emerald-600 text-white shadow-sm' : 'hover:text-slate-900'"
            >
              Delivered
            </button>
            <button
              @click="setStatusFilter('pending')"
              class="px-3 py-1.5 rounded-lg transition-all"
              :class="selectedStatus === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'hover:text-slate-900'"
            >
              Pending
            </button>
            <button
              @click="setStatusFilter('failed')"
              class="px-3 py-1.5 rounded-lg transition-all"
              :class="selectedStatus === 'failed' ? 'bg-rose-600 text-white shadow-sm' : 'hover:text-slate-900'"
            >
              Failed
            </button>
          </div>

          <!-- Search Button -->
          <button
            @click="handleSearch"
            class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all active:scale-[0.98]"
          >
            Search
          </button>

          <!-- Clear All Filters Button -->
          <button
            v-if="activeSearch || selectedStatus !== 'all'"
            @click="resetAllFilters"
            class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition-all"
          >
            Clear Filter
          </button>
        </div>
      </div>

      <!-- Active Filter Banner -->
      <div
        v-if="activeSearch || selectedStatus !== 'all'"
        class="bg-blue-50/70 border-b border-blue-100 px-6 py-2.5 flex items-center justify-between text-xs text-blue-900"
      >
        <div class="flex items-center gap-2 flex-wrap">
          <span v-if="activeSearch">
            Results matching: <strong class="font-bold">"{{ activeSearch }}"</strong>
          </span>
          <span v-if="activeSearch && selectedStatus !== 'all'" class="text-blue-300">•</span>
          <span v-if="selectedStatus !== 'all'">
            Status: <strong class="font-bold capitalize">{{ selectedStatus }}</strong>
          </span>
          <span class="text-blue-600">({{ entries.total || 0 }} matches found)</span>
        </div>
        <button
          @click="resetAllFilters"
          class="font-semibold text-blue-700 hover:text-blue-900 underline ml-4"
        >
          Reset to all
        </button>
      </div>

      <!-- Table View -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
              <th scope="col" class="py-3.5 px-6">Phone Number</th>
              <th scope="col" class="py-3.5 px-6">Network</th>
              <th scope="col" class="py-3.5 px-6">Status</th>
              <th scope="col" class="py-3.5 px-6">Amount</th>
              <th scope="col" class="py-3.5 px-6">Dispatched At</th>
              <th scope="col" class="py-3.5 px-6">Channel / Response</th>
              <th scope="col" class="py-3.5 px-6 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-sm">
            <!-- Loading Skeleton -->
            <template v-if="isLoading">
              <tr v-for="n in 5" :key="'skeleton-' + n" class="animate-pulse">
                <td class="py-4 px-6">
                  <div class="h-4 bg-slate-200 rounded w-36 mb-1.5"></div>
                  <div class="h-3 bg-slate-100 rounded w-24"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-5 bg-slate-200 rounded-md w-14"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-6 bg-slate-200 rounded-full w-20"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-4 bg-slate-200 rounded w-16"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-4 bg-slate-200 rounded w-32 mb-1"></div>
                  <div class="h-3 bg-slate-100 rounded w-20"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-4 bg-slate-200 rounded w-44"></div>
                </td>
                <td class="py-4 px-6 text-right">
                  <div class="h-7 bg-slate-200 rounded w-16 ml-auto"></div>
                </td>
              </tr>
            </template>

            <!-- Data Rows -->
            <template v-else-if="entries.data && entries.data.length > 0">
              <tr
                v-for="td in entries.data"
                :key="td.id"
                class="hover:bg-slate-50/60 transition-colors group cursor-pointer"
                @click="openDetails(td)"
              >
                <!-- Phone Number -->
                <td class="py-4 px-6">
                  <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-slate-800 tracking-wide text-sm">
                      {{ formatPhoneNumber(td.msisdn) }}
                    </span>
                    <button
                      @click.stop="copyToClipboard(td.msisdn, td.id)"
                      class="text-slate-300 hover:text-slate-600 transition-colors p-1 rounded"
                      :title="copiedId === td.id ? 'Copied!' : 'Copy number'"
                    >
                      <CheckIcon v-if="copiedId === td.id" class="w-4 h-4 text-emerald-600" />
                      <ClipboardDocumentIcon v-else class="w-4 h-4" />
                    </button>
                  </div>
                  <div class="text-[11px] text-slate-400 font-mono">
                    MSISDN: {{ td.msisdn }}
                  </div>
                </td>

                <!-- Network Carrier -->
                <td class="py-4 px-6">
                  <span
                    class="px-2.5 py-1 rounded-md text-xs font-semibold border"
                    :class="getCarrierBadgeClass(detectCarrier(td.msisdn))"
                  >
                    {{ detectCarrier(td.msisdn) }}
                  </span>
                </td>

                <!-- Status -->
                <td class="py-4 px-6">
                  <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                    :class="getStatusMeta(td.status).badgeClass"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="getStatusMeta(td.status).dotClass"></span>
                    {{ getStatusMeta(td.status).label }}
                  </span>
                </td>

                <!-- Amount -->
                <td class="py-4 px-6">
                  <span class="font-bold text-slate-800 font-mono text-sm">
                    {{ formatAmount(td.amount) }}
                  </span>
                </td>

                <!-- Dispatched Date -->
                <td class="py-4 px-6 text-slate-600 text-xs">
                  <div class="font-medium text-slate-700">{{ changeDateFormat(td.created_at) }}</div>
                  <div class="text-[11px] text-slate-400 mt-0.5">{{ formatRelativeDate(td.created_at) }}</div>
                </td>

                <!-- Channel / Response snippet -->
                <td class="py-4 px-6">
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-semibold text-slate-700">
                      {{ td.channel || 'Airtime Gateway' }}
                    </span>
                  </div>
                  <div
                    v-if="td.response"
                    class="text-[11px] text-slate-500 max-w-xs truncate mt-0.5"
                    :title="td.response"
                  >
                    {{ td.response }}
                  </div>
                  <div v-else-if="td.transactionId" class="text-[11px] text-slate-400 font-mono truncate max-w-xs mt-0.5">
                    Ref: {{ td.transactionId.substring(0, 13) }}...
                  </div>
                </td>

                <!-- Actions -->
                <td class="py-4 px-6 text-right" @click.stop>
                  <button
                    @click="openDetails(td)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all active:scale-95"
                    title="View transaction details"
                  >
                    <EyeIcon class="w-3.5 h-3.5 text-slate-500" />
                    <span>Details</span>
                  </button>
                </td>
              </tr>
            </template>

            <!-- Empty State -->
            <template v-else>
              <tr>
                <td colspan="7" class="py-16 text-center">
                  <div class="max-w-md mx-auto flex flex-col items-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 mb-4 border border-slate-100">
                      <SignalIcon class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-slate-800">
                      {{ activeSearch || selectedStatus !== 'all' ? 'No matching airtime dispatches' : 'No airtime records found' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 mb-5">
                      <span v-if="activeSearch">
                        We couldn't find any airtime records matching "{{ activeSearch }}". Try searching with a different phone number or transaction ID.
                      </span>
                      <span v-else-if="selectedStatus !== 'all'">
                        No airtime dispatches found with status "{{ selectedStatus }}". Try clearing the filter.
                      </span>
                      <span v-else>
                        No airtime rewards have been dispatched yet. When participants redeem winning codes, records will appear here.
                      </span>
                    </p>
                    <div class="flex items-center gap-3">
                      <button
                        v-if="activeSearch || selectedStatus !== 'all'"
                        @click="resetAllFilters"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                      >
                        Clear Filters
                      </button>
                      <button
                        @click="refreshData"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#005cb4] hover:bg-blue-700 text-white shadow-sm transition-colors"
                      >
                        Refresh Page
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div
        v-if="entries.links && entries.links.length > 3"
        class="p-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-4 bg-slate-50/50"
      >
        <div class="text-xs text-slate-500 font-medium">
          Showing <strong class="font-bold text-slate-700">{{ entries.from || 0 }}</strong> to
          <strong class="font-bold text-slate-700">{{ entries.to || 0 }}</strong> of
          <strong class="font-bold text-slate-700">{{ entries.total || 0 }}</strong> results
        </div>
        <div>
          <ThePaginator
            :links="entries.links"
            :currentPage="currentPage"
            :lastPage="lastPage"
            @pagechanged="handlePageChange"
          />
        </div>
      </div>
    </div>

    <!-- Transaction Details Modal -->
    <TransitionRoot appear :show="isDetailsOpen" as="template">
      <Dialog as="div" @close="closeDetails" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-3xl bg-white p-6 sm:p-7 text-left align-middle shadow-2xl transition-all border border-slate-100">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#005cb4] flex items-center justify-center shrink-0">
                      <SignalIcon class="w-5 h-5" />
                    </div>
                    <div>
                      <DialogTitle as="h3" class="text-lg font-extrabold text-slate-800">
                        Airtime Dispatch Details
                      </DialogTitle>
                      <p class="text-xs text-slate-400 mt-0.5">
                        Transaction Record #{{ selectedAirtime?.id }}
                      </p>
                    </div>
                  </div>
                  <button
                    @click="closeDetails"
                    class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-50 transition-colors"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>

                <!-- Modal Body -->
                <div v-if="selectedAirtime" class="mt-5 space-y-4">
                  <!-- Status & Amount Header -->
                  <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                    <div>
                      <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Amount Dispatched</span>
                      <div class="text-2xl font-black text-slate-800 font-mono mt-0.5">
                        {{ formatAmount(selectedAirtime.amount) }}
                      </div>
                    </div>
                    <div>
                      <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                        :class="getStatusMeta(selectedAirtime.status).badgeClass"
                      >
                        <span class="w-2 h-2 rounded-full" :class="getStatusMeta(selectedAirtime.status).dotClass"></span>
                        {{ getStatusMeta(selectedAirtime.status).label }}
                      </span>
                    </div>
                  </div>

                  <!-- Details Grid -->
                  <div class="grid grid-cols-2 gap-3 text-xs">
                    <!-- Phone -->
                    <div class="p-3 bg-white border border-slate-200/80 rounded-xl">
                      <span class="text-slate-400 font-medium">Recipient Phone</span>
                      <div class="font-mono font-bold text-slate-800 mt-1 flex items-center justify-between">
                        <span>{{ formatPhoneNumber(selectedAirtime.msisdn) }}</span>
                        <button
                          @click="copyToClipboard(selectedAirtime.msisdn, 'modal-phone')"
                          class="text-slate-400 hover:text-slate-700"
                        >
                          <CheckIcon v-if="copiedId === 'modal-phone'" class="w-3.5 h-3.5 text-emerald-600" />
                          <ClipboardDocumentIcon v-else class="w-3.5 h-3.5" />
                        </button>
                      </div>
                      <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                        Raw: {{ selectedAirtime.msisdn }}
                      </div>
                    </div>

                    <!-- Network -->
                    <div class="p-3 bg-white border border-slate-200/80 rounded-xl">
                      <span class="text-slate-400 font-medium">Network Carrier</span>
                      <div class="mt-1">
                        <span
                          class="px-2.5 py-0.5 rounded-md text-xs font-semibold border inline-block"
                          :class="getCarrierBadgeClass(detectCarrier(selectedAirtime.msisdn))"
                        >
                          {{ detectCarrier(selectedAirtime.msisdn) }}
                        </span>
                      </div>
                    </div>

                    <!-- Channel -->
                    <div class="p-3 bg-white border border-slate-200/80 rounded-xl">
                      <span class="text-slate-400 font-medium">Gateway / Channel</span>
                      <div class="font-bold text-slate-800 mt-1">
                        {{ selectedAirtime.channel || 'Standard Gateway' }}
                      </div>
                    </div>

                    <!-- InMessage Reference -->
                    <div class="p-3 bg-white border border-slate-200/80 rounded-xl">
                      <span class="text-slate-400 font-medium">InMessage Reference</span>
                      <div class="font-mono font-bold text-slate-800 mt-1">
                        #{{ selectedAirtime.inMessageId || '-' }}
                      </div>
                    </div>
                  </div>

                  <!-- Transaction ID -->
                  <div v-if="selectedAirtime.transactionId" class="p-3 bg-white border border-slate-200/80 rounded-xl text-xs">
                    <div class="flex items-center justify-between text-slate-400 font-medium mb-1">
                      <span>Transaction UUID</span>
                      <button
                        @click="copyToClipboard(selectedAirtime.transactionId, 'modal-tx')"
                        class="text-slate-400 hover:text-slate-700 inline-flex items-center gap-1"
                      >
                        <CheckIcon v-if="copiedId === 'modal-tx'" class="w-3.5 h-3.5 text-emerald-600" />
                        <ClipboardDocumentIcon v-else class="w-3.5 h-3.5" />
                        <span class="text-[10px]">{{ copiedId === 'modal-tx' ? 'Copied' : 'Copy' }}</span>
                      </button>
                    </div>
                    <div class="font-mono text-slate-800 bg-slate-50 p-2 rounded-lg break-all text-[11px]">
                      {{ selectedAirtime.transactionId }}
                    </div>
                  </div>

                  <!-- Provider Request ID -->
                  <div v-if="selectedAirtime.responseId" class="p-3 bg-white border border-slate-200/80 rounded-xl text-xs">
                    <div class="flex items-center justify-between text-slate-400 font-medium mb-1">
                      <span>Provider Response / Request ID</span>
                      <button
                        @click="copyToClipboard(selectedAirtime.responseId, 'modal-req')"
                        class="text-slate-400 hover:text-slate-700 inline-flex items-center gap-1"
                      >
                        <CheckIcon v-if="copiedId === 'modal-req'" class="w-3.5 h-3.5 text-emerald-600" />
                        <ClipboardDocumentIcon v-else class="w-3.5 h-3.5" />
                        <span class="text-[10px]">{{ copiedId === 'modal-req' ? 'Copied' : 'Copy' }}</span>
                      </button>
                    </div>
                    <div class="font-mono text-slate-800 bg-slate-50 p-2 rounded-lg break-all text-[11px]">
                      {{ selectedAirtime.responseId }}
                    </div>
                  </div>

                  <!-- Provider Description / Response -->
                  <div class="p-3 bg-white border border-slate-200/80 rounded-xl text-xs">
                    <span class="text-slate-400 font-medium block mb-1">Gateway Response Message</span>
                    <div class="text-slate-700 bg-slate-50 p-2.5 rounded-lg text-xs leading-relaxed">
                      {{ selectedAirtime.response || 'No description returned by provider.' }}
                    </div>
                  </div>

                  <!-- Timestamps -->
                  <div class="flex items-center justify-between text-[11px] text-slate-400 px-1 pt-1">
                    <span>Dispatched: {{ changeDateFormat(selectedAirtime.created_at) }}</span>
                    <span v-if="selectedAirtime.nextAttempt">
                      Next Attempt: {{ changeDateFormat(selectedAirtime.nextAttempt) }}
                    </span>
                  </div>
                </div>

                <!-- Modal Actions -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                  <button
                    @click="closeDetails"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors"
                  >
                    Close
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import moment from 'moment';
import { useAuthStore } from '@/store/authStore';
import ThePaginator from '@/components/UI/ThePaginator.vue';
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue';
import {
  MagnifyingGlassIcon,
  XMarkIcon,
  ArrowPathIcon,
  CheckIcon,
  CheckCircleIcon,
  ClockIcon,
  SignalIcon,
  BanknotesIcon,
  EyeIcon,
  ClipboardDocumentIcon,
} from '@heroicons/vue/24/outline';

const route = useRoute();
const authStore = useAuthStore();

// State
const entries = ref({ data: [], links: [], total: 0, from: 0, to: 0 });
const currentPage = ref(1);
const lastPage = ref(1);
const isLoading = ref(false);
const searchQuery = ref('');
const activeSearch = ref('');
const selectedStatus = ref('all');
const copiedId = ref(null);

// Summary stats
const summary = ref({
  total_count: 0,
  total_amount: 0,
  sent_count: 0,
  pending_count: 0,
  failed_count: 0,
});

// Modal state
const isDetailsOpen = ref(false);
const selectedAirtime = ref(null);

// Get Auth Token
const getAuthToken = () => {
  return authStore.token || localStorage.getItem('token');
};

// Carrier Detection for Uganda Phone Numbers
const detectCarrier = (phone) => {
  if (!phone) return 'National';
  const clean = String(phone).replace(/\D/g, '');
  if (
    clean.startsWith('25677') ||
    clean.startsWith('25678') ||
    clean.startsWith('25676') ||
    clean.startsWith('077') ||
    clean.startsWith('078') ||
    clean.startsWith('076')
  ) {
    return 'MTN';
  } else if (
    clean.startsWith('25670') ||
    clean.startsWith('25675') ||
    clean.startsWith('25674') ||
    clean.startsWith('070') ||
    clean.startsWith('075') ||
    clean.startsWith('074')
  ) {
    return 'Airtel';
  } else if (clean.startsWith('25672') || clean.startsWith('072')) {
    return 'Lycamobile';
  } else if (clean.startsWith('25671') || clean.startsWith('071')) {
    return 'UTL';
  }
  return 'National';
};

const getCarrierBadgeClass = (carrier) => {
  switch (carrier) {
    case 'MTN':
      return 'bg-amber-100 text-amber-800 border-amber-200/60';
    case 'Airtel':
      return 'bg-red-50 text-red-700 border-red-200/60';
    case 'Lycamobile':
      return 'bg-blue-50 text-blue-700 border-blue-200/60';
    case 'UTL':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200/60';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200';
  }
};

const formatPhoneNumber = (phone) => {
  if (!phone) return '-';
  const clean = String(phone).replace(/\D/g, '');
  if (clean.length === 12 && clean.startsWith('256')) {
    return `+256 ${clean.slice(3, 6)} ${clean.slice(6, 9)} ${clean.slice(9)}`;
  }
  if (clean.length === 10 && clean.startsWith('0')) {
    return `+256 ${clean.slice(1, 4)} ${clean.slice(4, 7)} ${clean.slice(7)}`;
  }
  return phone;
};

const formatAmount = (amount) => {
  if (!amount && amount !== 0) return '0 UGX';
  const num = Number(amount);
  return isNaN(num) ? `${amount} UGX` : `${num.toLocaleString()} UGX`;
};

const getStatusMeta = (status) => {
  const norm = String(status || '').toLowerCase();
  if (norm === 'sent' || norm === 'success') {
    return {
      label: 'Delivered',
      badgeClass: 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
      dotClass: 'bg-emerald-500',
    };
  } else if (norm === 'pending') {
    return {
      label: 'Pending',
      badgeClass: 'bg-amber-50 text-amber-700 border border-amber-200/60',
      dotClass: 'bg-amber-500',
    };
  } else if (norm === 'failed') {
    return {
      label: 'Failed',
      badgeClass: 'bg-rose-50 text-rose-700 border border-rose-200/60',
      dotClass: 'bg-rose-500',
    };
  }
  return {
    label: status || 'Unknown',
    badgeClass: 'bg-blue-50 text-blue-700 border border-blue-200/60',
    dotClass: 'bg-blue-500',
  };
};

const changeDateFormat = (date) => {
  if (!date) return '-';
  return moment(date).format('YYYY-MM-DD HH:mm:ss');
};

const formatRelativeDate = (date) => {
  if (!date) return '';
  return moment(date).fromNow();
};

const copyToClipboard = async (text, id = null) => {
  try {
    await navigator.clipboard.writeText(text);
    copiedId.value = id || text;
    setTimeout(() => {
      if (copiedId.value === (id || text)) {
        copiedId.value = null;
      }
    }, 2000);
  } catch (err) {
    console.error('Failed to copy to clipboard:', err);
  }
};

// Fetch Airtime Dispatches
const fetchAirtime = async (page = 1) => {
  isLoading.value = true;
  try {
    const token = getAuthToken();
    const params = { page };

    if (activeSearch.value) {
      params.search = activeSearch.value;
    }

    if (selectedStatus.value && selectedStatus.value !== 'all') {
      params.status = selectedStatus.value;
    }

    const response = await axios.get('/api/airtime', {
      params,
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    entries.value = response.data;
    currentPage.value = response.data.current_page || 1;
    lastPage.value = response.data.last_page || 1;
  } catch (err) {
    console.error('Failed to fetch airtime records:', err);
  } finally {
    isLoading.value = false;
  }
};

// Fetch Summary Stats
const fetchSummary = async () => {
  try {
    const token = getAuthToken();
    const res = await axios.get('/api/airtime/summary', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    summary.value = res.data;
  } catch (err) {
    console.error('Failed to fetch summary stats:', err);
  }
};

// Search handling
const handleSearch = () => {
  activeSearch.value = searchQuery.value.trim();
  currentPage.value = 1;
  fetchAirtime(1);
};

const clearSearchInput = () => {
  searchQuery.value = '';
  if (activeSearch.value) {
    activeSearch.value = '';
    currentPage.value = 1;
    fetchAirtime(1);
  }
};

const resetAllFilters = () => {
  searchQuery.value = '';
  activeSearch.value = '';
  selectedStatus.value = 'all';
  currentPage.value = 1;
  fetchAirtime(1);
};

const setStatusFilter = (status) => {
  selectedStatus.value = status;
  currentPage.value = 1;
  fetchAirtime(1);
};

const handlePageChange = (page) => {
  fetchAirtime(page);
};

const refreshData = () => {
  fetchSummary();
  fetchAirtime(currentPage.value);
};

// Modal Details
const openDetails = (airtime) => {
  selectedAirtime.value = airtime;
  isDetailsOpen.value = true;
};

const closeDetails = () => {
  isDetailsOpen.value = false;
  selectedAirtime.value = null;
};

onMounted(() => {
  // Check if router passed search in query string
  if (route.query && route.query.search) {
    searchQuery.value = String(route.query.search);
    activeSearch.value = String(route.query.search);
  }
  fetchSummary();
  fetchAirtime(1);
});
</script>
