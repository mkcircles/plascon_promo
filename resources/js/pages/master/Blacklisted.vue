<template>
  <div class="container mx-auto py-8 px-4 max-w-7xl">
    <!-- Toast Notification -->
    <transition enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="toast.show"
        class="fixed top-20 right-6 z-50 max-w-md w-full bg-white shadow-2xl rounded-2xl border pointer-events-auto p-4 flex items-start gap-3"
        :class="toast.type === 'error' ? 'border-rose-200 text-rose-900' : 'border-emerald-200 text-emerald-900'">
        <div class="p-2 rounded-xl shrink-0"
          :class="toast.type === 'error' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'">
          <XCircleIcon v-if="toast.type === 'error'" class="w-5 h-5" />
          <CheckCircleIcon v-else class="w-5 h-5" />
        </div>
        <div class="flex-1 text-sm font-medium pt-1">
          <p class="font-semibold" :class="toast.type === 'error' ? 'text-rose-950' : 'text-emerald-950'">
            {{ toast.title }}
          </p>
          <p class="text-xs mt-0.5" :class="toast.type === 'error' ? 'text-rose-700' : 'text-emerald-700'">
            {{ toast.message }}
          </p>
        </div>
        <button @click="toast.show = false"
          class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors">
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </transition>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
      <div>
        <div
          class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60 mb-2">
          <ShieldExclamationIcon class="w-4 h-4 text-rose-600" />
          <span>Security & Fraud Prevention</span>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
          Blacklisted Numbers
        </h1>
        <p class="text-slate-500 text-sm mt-1 max-w-2xl">
          Manage phone numbers barred from entering promotional draws, redeeming winning codes, and receiving automated
          airtime rewards.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-3">
        <button @click="getEntries(currentPage)" :disabled="isLoading"
          class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-semibold shadow-sm transition-all active:scale-[0.98] disabled:opacity-50"
          title="Refresh List">
          <ArrowPathIcon class="w-4 h-4 text-slate-500" :class="{ 'animate-spin': isLoading }" />
          <span>Refresh</span>
        </button>

        <button @click="openAddModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#005cb4] hover:bg-blue-700 text-white text-sm font-semibold shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all active:scale-[0.98]">
          <PlusIcon class="w-5 h-5" />
          <span>Add Number</span>
        </button>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
          <NoSymbolIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Blacklisted</p>
          <div class="flex items-baseline gap-2 mt-0.5">
            <span class="text-2xl font-black text-slate-800">
              {{ (entries.total || 0).toLocaleString() }}
            </span>
            <span class="text-xs text-slate-500">numbers</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
          <ShieldCheckIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Protection Status</p>
          <div class="flex items-center gap-2 mt-1">
            <span class="flex h-2 w-2 relative">
              <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-sm font-bold text-slate-800">Active Enforcement</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#005cb4] flex items-center justify-center shrink-0">
          <PhoneIcon class="w-6 h-6" />
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Coverage</p>
          <p class="text-sm font-bold text-slate-800 mt-0.5">MTN &bull; Airtel &bull; UTL</p>
        </div>
      </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
      <!-- Search & Filters Bar -->
      <div
        class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-slate-50/50">
        <div class="relative flex-1 max-w-md">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <MagnifyingGlassIcon class="w-5 h-5" />
          </div>
          <input type="text" v-model="searchQuery" @keyup.enter="handleSearch"
            placeholder="Search by phone number (e.g. 078..., 2567...)"
            class="block w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" />
          <button v-if="searchQuery" @click="clearSearch"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>

        <div class="flex items-center gap-2">
          <button @click="handleSearch"
            class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all active:scale-[0.98]">
            Search
          </button>
          <button v-if="activeSearch" @click="clearSearch"
            class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition-all">
            Clear Filter
          </button>
        </div>
      </div>

      <!-- Active Search Notice -->
      <div v-if="activeSearch"
        class="bg-blue-50/70 border-b border-blue-100 px-6 py-2.5 flex items-center justify-between text-xs text-blue-900">
        <span>
          Showing search results for: <strong class="font-bold">"{{ activeSearch }}"</strong> ({{ entries.total || 0 }}
          matches)
        </span>
        <button @click="clearSearch" class="font-semibold text-blue-700 hover:text-blue-900 underline">
          Reset to all numbers
        </button>
      </div>

      <!-- Table View -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr
              class="bg-slate-50/80 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
              <th scope="col" class="py-3.5 px-6">Phone Number</th>
              <th scope="col" class="py-3.5 px-6">Carrier</th>
              <th scope="col" class="py-3.5 px-6">Status</th>
              <th scope="col" class="py-3.5 px-6">Added By</th>
              <th scope="col" class="py-3.5 px-6">Date Added</th>
              <th scope="col" class="py-3.5 px-6 text-right">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100 text-sm">
            <!-- Loading Skeletons -->
            <template v-if="isLoading">
              <tr v-for="i in 5" :key="'skeleton-' + i" class="animate-pulse">
                <td class="py-4 px-6">
                  <div class="h-4 bg-slate-200 rounded w-36"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-4 bg-slate-200 rounded w-16"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-6 bg-slate-200 rounded-full w-24"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-4 bg-slate-200 rounded w-28"></div>
                </td>
                <td class="py-4 px-6">
                  <div class="h-4 bg-slate-200 rounded w-32"></div>
                </td>
                <td class="py-4 px-6 text-right">
                  <div class="h-7 bg-slate-200 rounded w-20 ml-auto"></div>
                </td>
              </tr>
            </template>

            <!-- Data Rows -->
            <template v-else-if="entries.data && entries.data.length > 0">
              <tr v-for="item in entries.data" :key="item.id" class="hover:bg-slate-50/60 transition-colors group">
                <!-- Phone Number -->
                <td class="py-4 px-6">
                  <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-slate-800 tracking-wide text-sm">
                      {{ formatPhone(item.msisdn) }}
                    </span>
                    <button @click="copyPhone(item.msisdn)"
                      class="text-slate-300 hover:text-slate-600 transition-colors p-1 rounded"
                      :title="copiedPhone === item.msisdn ? 'Copied!' : 'Copy number'">
                      <CheckCircleIcon v-if="copiedPhone === item.msisdn" class="w-4 h-4 text-emerald-600" />
                      <ClipboardDocumentIcon v-else class="w-4 h-4" />
                    </button>
                  </div>
                  <span class="text-[11px] text-slate-400 font-mono">MSISDN: {{ item.msisdn }}</span>
                </td>

                <!-- Carrier -->
                <td class="py-4 px-6">
                  <span class="px-2.5 py-1 rounded-md text-xs font-semibold" :class="getCarrierBadgeClass(item.msisdn)">
                    {{ getCarrier(item.msisdn) }}
                  </span>
                </td>

                <!-- Status -->
                <td class="py-4 px-6">
                  <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Blacklisted
                  </span>
                </td>

                <!-- Added By -->
                <td class="py-4 px-6">
                  <div class="flex items-center gap-2">
                    <div
                      class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase">
                      {{ getUserInitial(item.user?.name) }}
                    </div>
                    <span class="font-medium text-slate-700">
                      {{ item.user?.name || 'System Administrator' }}
                    </span>
                  </div>
                </td>

                <!-- Date Added -->
                <td class="py-4 px-6 text-slate-600 text-xs">
                  <div>{{ formatDate(item.created_at) }}</div>
                  <div class="text-[11px] text-slate-400 mt-0.5">{{ formatRelativeDate(item.created_at) }}</div>
                </td>

                <!-- Actions -->
                <td class="py-4 px-6 text-right">
                  <button @click="promptRemove(item)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 border border-rose-200/50 transition-all active:scale-95"
                    title="Remove from blacklist">
                    <TrashIcon class="w-3.5 h-3.5" />
                    <span>Remove</span>
                  </button>
                </td>
              </tr>
            </template>

            <!-- Empty State -->
            <template v-else>
              <tr>
                <td colspan="6" class="py-16 text-center">
                  <div class="max-w-sm mx-auto flex flex-col items-center">
                    <div
                      class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 mb-4 border border-slate-100">
                      <NoSymbolIcon class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-slate-800">
                      {{ activeSearch ? 'No matching blacklisted numbers' : 'No blacklisted numbers found' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 mb-5">
                      {{ activeSearch
                        ? `We couldn't find any numbers matching "${activeSearch}". Try another search or clear the
                      filter.`
                        : 'No numbers have been added to the blacklist yet. Numbers added here will be protected against
                      code abuse.'
                      }}
                    </p>
                    <div class="flex items-center gap-3">
                      <button v-if="activeSearch" @click="clearSearch"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                        Clear Filter
                      </button>
                      <button @click="openAddModal"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#005cb4] hover:bg-blue-700 text-white shadow-sm transition-colors">
                        + Add Number
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
      <div v-if="entries.links && entries.links.length > 3"
        class="p-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-4 bg-slate-50/50">
        <div class="text-xs text-slate-500 font-medium">
          Showing
          <strong class="font-bold text-slate-700">{{ entries.from || 0 }}</strong>
          to
          <strong class="font-bold text-slate-700">{{ entries.to || 0 }}</strong>
          of
          <strong class="font-bold text-slate-700">{{ entries.total || 0 }}</strong>
          results
        </div>
        <div>
          <ThePaginator :links="entries.links" :currentPage="currentPage" :lastPage="lastPage"
            @pagechanged="handlePageChange" />
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- Add Number to Blacklist Modal                            -->
    <!-- ======================================================== -->
    <TransitionRoot appear :show="isAddModalOpen" as="template">
      <Dialog as="div" @close="closeAddModal" class="relative z-50">
        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
          leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95">
              <DialogPanel
                class="w-full max-w-lg transform overflow-hidden rounded-3xl bg-white p-6 sm:p-7 text-left align-middle shadow-2xl transition-all border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                      <NoSymbolIcon class="w-5 h-5" />
                    </div>
                    <div>
                      <DialogTitle as="h3" class="text-lg font-extrabold text-slate-800">
                        Add to Blacklist
                      </DialogTitle>
                      <p class="text-xs text-slate-400 mt-0.5">
                        Bar phone numbers from winning promotions and receiving airtime.
                      </p>
                    </div>
                  </div>
                  <button @click="closeAddModal"
                    class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>

                <!-- Mode Switch Tabs -->
                <div class="flex bg-slate-100 rounded-xl p-1 text-xs font-semibold my-5">
                  <button type="button" @click="addMode = 'single'" class="flex-1 py-1.5 rounded-lg transition-all"
                    :class="addMode === 'single' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                    Single Number
                  </button>
                  <button type="button" @click="addMode = 'bulk'" class="flex-1 py-1.5 rounded-lg transition-all"
                    :class="addMode === 'bulk' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                    Bulk Import
                  </button>
                </div>

                <!-- Modal Error Banner -->
                <div v-if="modalError"
                  class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2.5 text-xs text-rose-800">
                  <ExclamationTriangleIcon class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
                  <span class="flex-1 leading-relaxed">{{ modalError }}</span>
                </div>

                <form @submit.prevent="submitAdd">
                  <!-- Single Number Input -->
                  <div v-if="addMode === 'single'" class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                      Phone Number
                    </label>
                    <div class="relative">
                      <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <PhoneIcon class="w-5 h-5" />
                      </div>
                      <input type="text" v-model="singlePhone" ref="phoneInputRef"
                        placeholder="e.g. 0781234567 or 256781234567"
                        class="block w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                        required />
                    </div>

                    <!-- Live Normalization Preview -->
                    <div v-if="singlePhone.trim()"
                      class="p-3 rounded-xl border text-xs flex items-center justify-between"
                      :class="isSingleValid ? 'bg-emerald-50/60 border-emerald-200 text-emerald-800' : 'bg-amber-50/60 border-amber-200 text-amber-800'">
                      <div class="flex items-center gap-2">
                        <CheckCircleIcon v-if="isSingleValid" class="w-4 h-4 text-emerald-600" />
                        <ExclamationTriangleIcon v-else class="w-4 h-4 text-amber-600" />
                        <span>
                          Normalized: <strong class="font-mono font-bold">{{ normalizedSinglePhone }}</strong>
                          <span v-if="singleCarrier" class="ml-1 opacity-75">({{ singleCarrier }})</span>
                        </span>
                      </div>
                      <span v-if="isSingleValid" class="font-semibold text-emerald-700 text-[11px]">Valid format</span>
                      <span v-else class="text-[11px] text-amber-700">Needs 10 or 12 digits</span>
                    </div>

                    <p class="text-[11px] text-slate-400">
                      Supports formats: <code
                        class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">07XXXXXXXX</code>, <code
                        class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">256XXXXXXXXX</code>, or with
                      country code <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">+256</code>.
                    </p>
                  </div>

                  <!-- Bulk Import Input -->
                  <div v-else class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                      Paste Phone Numbers
                    </label>
                    <textarea v-model="bulkPhones" rows="5"
                      placeholder="Enter numbers separated by commas or line breaks:&#10;0781234567&#10;0752345678&#10;256701234567"
                      class="block w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm placeholder-slate-400 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-mono"
                      required></textarea>
                    <p class="text-[11px] text-slate-400">
                      Separate numbers with commas, semicolons, or newlines. Invalid formats or duplicates will be
                      automatically reported.
                    </p>
                  </div>

                  <!-- Modal Footer Buttons -->
                  <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="closeAddModal"
                      class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors">
                      Cancel
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                      class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#005cb4] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/10 hover:shadow-blue-500/20 transition-all disabled:opacity-50">
                      <ArrowPathIcon v-if="isSubmitting" class="w-4 h-4 animate-spin text-white" />
                      <span>{{ isSubmitting ? 'Adding...' : 'Add to Blacklist' }}</span>
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- ======================================================== -->
    <!-- Remove Confirmation Modal                                -->
    <!-- ======================================================== -->
    <TransitionRoot appear :show="isRemoveModalOpen" as="template">
      <Dialog as="div" @close="closeRemoveModal" class="relative z-50">
        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
          leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95">
              <DialogPanel
                class="w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-6 sm:p-7 text-left align-middle shadow-2xl transition-all border border-slate-100">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4">
                  <ExclamationTriangleIcon class="w-6 h-6" />
                </div>

                <DialogTitle as="h3" class="text-lg font-extrabold text-slate-800">
                  Remove from Blacklist?
                </DialogTitle>

                <div class="mt-2 text-sm text-slate-600 space-y-3">
                  <p>
                    Are you sure you want to remove
                    <strong class="font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded">
                      {{ formatPhone(selectedItem?.msisdn) }}
                    </strong>
                    from the blacklist?
                  </p>
                  <p
                    class="text-xs text-rose-700 bg-rose-50/80 border border-rose-200/60 p-3 rounded-xl leading-relaxed">
                    <strong>Warning:</strong> Removing this number will immediately restore its eligibility to redeem
                    promotion codes, participate in prize draws, and receive airtime.
                  </p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                  <button type="button" @click="closeRemoveModal"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors">
                    Cancel / Keep Blacklisted
                  </button>
                  <button type="button" @click="confirmRemove" :disabled="isRemoving"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-500/10 hover:shadow-rose-500/20 transition-all disabled:opacity-50">
                    <ArrowPathIcon v-if="isRemoving" class="w-4 h-4 animate-spin text-white" />
                    <span>{{ isRemoving ? 'Removing...' : 'Yes, Remove Number' }}</span>
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
import { ref, computed, onMounted, nextTick } from 'vue';
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
  ShieldExclamationIcon,
  ShieldCheckIcon,
  NoSymbolIcon,
  PlusIcon,
  TrashIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  XCircleIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
  ClipboardDocumentIcon,
  PhoneIcon,
} from '@heroicons/vue/24/outline';

const store = useAuthStore();

// State
const entries = ref({ data: [], links: [], total: 0, from: 0, to: 0 });
const currentPage = ref(1);
const lastPage = ref(1);
const isLoading = ref(false);
const searchQuery = ref('');
const activeSearch = ref('');
const copiedPhone = ref(null);

// Modal states
const isAddModalOpen = ref(false);
const addMode = ref('single'); // 'single' or 'bulk'
const singlePhone = ref('');
const bulkPhones = ref('');
const isSubmitting = ref(false);
const modalError = ref('');
const phoneInputRef = ref(null);

// Remove modal states
const isRemoveModalOpen = ref(false);
const selectedItem = ref(null);
const isRemoving = ref(false);

// Toast notification
const toast = ref({
  show: false,
  title: '',
  message: '',
  type: 'success', // 'success' or 'error'
  timer: null,
});

const showToast = (title, message, type = 'success') => {
  if (toast.value.timer) clearTimeout(toast.value.timer);
  toast.value = {
    show: true,
    title,
    message,
    type,
    timer: setTimeout(() => {
      toast.value.show = false;
    }, 4500),
  };
};

// Phone normalizer
const normalizePhone = (raw) => {
  if (!raw) return '';
  let cleaned = raw.replace(/\D/g, '');
  if (cleaned.startsWith('0') && cleaned.length === 10) {
    cleaned = '256' + cleaned.substring(1);
  } else if (cleaned.length === 9 && cleaned.startsWith('7')) {
    cleaned = '256' + cleaned;
  }
  return cleaned;
};

// Carrier detector
const getCarrier = (msisdn) => {
  if (!msisdn) return 'National';
  const clean = String(msisdn).replace(/\D/g, '');
  if (
    clean.startsWith('25677') || clean.startsWith('25678') || clean.startsWith('25676') ||
    clean.startsWith('077') || clean.startsWith('078') || clean.startsWith('076')
  ) {
    return 'MTN';
  }
  if (
    clean.startsWith('25670') || clean.startsWith('25675') || clean.startsWith('25674') ||
    clean.startsWith('070') || clean.startsWith('075') || clean.startsWith('074')
  ) {
    return 'Airtel';
  }
  if (clean.startsWith('25672') || clean.startsWith('072')) {
    return 'Lycamobile';
  }
  if (clean.startsWith('25671') || clean.startsWith('071')) {
    return 'UTL';
  }
  return 'National';
};

const getCarrierBadgeClass = (msisdn) => {
  const carrier = getCarrier(msisdn);
  switch (carrier) {
    case 'MTN':
      return 'bg-amber-100 text-amber-800 border border-amber-200/60';
    case 'Airtel':
      return 'bg-red-50 text-red-700 border border-red-200/60';
    case 'Lycamobile':
      return 'bg-blue-50 text-blue-700 border border-blue-200/60';
    case 'UTL':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200/60';
    default:
      return 'bg-slate-100 text-slate-700 border border-slate-200';
  }
};

const formatPhone = (msisdn) => {
  if (!msisdn) return '';
  const clean = String(msisdn).replace(/\D/g, '');
  if (clean.length === 12 && clean.startsWith('256')) {
    return `+256 ${clean.slice(3, 6)} ${clean.slice(6, 9)} ${clean.slice(9)}`;
  }
  return msisdn;
};

const normalizedSinglePhone = computed(() => {
  return normalizePhone(singlePhone.value);
});

const isSingleValid = computed(() => {
  const norm = normalizedSinglePhone.value;
  return norm.length === 12 && norm.startsWith('256');
});

const singleCarrier = computed(() => {
  if (!isSingleValid.value) return '';
  return getCarrier(normalizedSinglePhone.value);
});

const getUserInitial = (name) => {
  if (!name) return 'A';
  return name.charAt(0).toUpperCase();
};

const formatDate = (date) => {
  if (!date) return '-';
  return moment(date).format('YYYY-MM-DD HH:mm:ss');
};

const formatRelativeDate = (date) => {
  if (!date) return '';
  return moment(date).fromNow();
};

// Clipboard copy
const copyPhone = async (phone) => {
  try {
    await navigator.clipboard.writeText(phone);
    copiedPhone.value = phone;
    setTimeout(() => {
      if (copiedPhone.value === phone) copiedPhone.value = null;
    }, 2000);
  } catch (err) {
    console.error('Copy failed:', err);
  }
};

// Fetch list
const getEntries = async (page = 1) => {
  isLoading.value = true;
  try {
    const token = store.token || localStorage.getItem('token');
    const params = { page };
    if (activeSearch.value) {
      params.search = activeSearch.value;
    }

    const response = await axios.get('/api/past-winners/blacklisted', {
      params,
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    entries.value = response.data;
    currentPage.value = response.data.current_page || 1;
    lastPage.value = response.data.last_page || 1;
  } catch (err) {
    console.error('Failed to fetch blacklisted numbers:', err);
    showToast('Network Error', 'Failed to retrieve blacklisted numbers. Please try again.', 'error');
  } finally {
    isLoading.value = false;
  }
};

// Search handling
const handleSearch = () => {
  activeSearch.value = searchQuery.value.trim();
  currentPage.value = 1;
  getEntries(1);
};

const clearSearch = () => {
  searchQuery.value = '';
  activeSearch.value = '';
  currentPage.value = 1;
  getEntries(1);
};

const handlePageChange = (page) => {
  getEntries(page);
};

// Add Modal
const openAddModal = () => {
  singlePhone.value = '';
  bulkPhones.value = '';
  modalError.value = '';
  addMode.value = 'single';
  isAddModalOpen.value = true;
  nextTick(() => {
    if (phoneInputRef.value) phoneInputRef.value.focus();
  });
};

const closeAddModal = () => {
  isAddModalOpen.value = false;
  modalError.value = '';
};

const submitAdd = async () => {
  modalError.value = '';

  const msisdnPayload = addMode.value === 'single' ? singlePhone.value.trim() : bulkPhones.value.trim();
  if (!msisdnPayload) {
    modalError.value = 'Please provide at least one phone number.';
    return;
  }

  isSubmitting.value = true;
  try {
    const token = store.token || localStorage.getItem('token');
    const response = await axios.post(
      '/api/past-winners/add',
      {
        msisdn: msisdnPayload,
        category: 'blacklisted',
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    closeAddModal();
    showToast('Success', response.data.message || 'Phone number added to blacklist successfully.');
    // Re-fetch current page or first page
    getEntries(1);
  } catch (err) {
    console.error('Failed to add number to blacklist:', err);
    if (err.response && err.response.data && err.response.data.message) {
      modalError.value = err.response.data.message;
    } else {
      modalError.value = 'An error occurred while adding to the blacklist. Please try again.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Remove Modal
const promptRemove = (item) => {
  selectedItem.value = item;
  isRemoveModalOpen.value = true;
};

const closeRemoveModal = () => {
  isRemoveModalOpen.value = false;
  selectedItem.value = null;
};

const confirmRemove = async () => {
  if (!selectedItem.value) return;

  isRemoving.value = true;
  try {
    const token = store.token || localStorage.getItem('token');
    const response = await axios.delete(
      `/api/past-winners/delete/${selectedItem.value.id}`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    const removedNumber = selectedItem.value.msisdn;
    closeRemoveModal();
    showToast('Removed Successfully', response.data.message || `Number ${formatPhone(removedNumber)} removed from blacklist.`);

    // Remove locally from state for immediate smooth response
    if (entries.value && entries.value.data) {
      entries.value.data = entries.value.data.filter((i) => i.id !== selectedItem.value?.id);
      if (entries.value.total) {
        entries.value.total = Math.max(0, entries.value.total - 1);
      }
    }

    // If page is now empty and not on first page, navigate back
    if (entries.value.data.length === 0 && currentPage.value > 1) {
      getEntries(currentPage.value - 1);
    } else {
      getEntries(currentPage.value);
    }
  } catch (err) {
    console.error('Failed to remove number:', err);
    showToast('Failed to Remove', 'Could not remove number from blacklist. Please try again.', 'error');
  } finally {
    isRemoving.value = false;
  }
};

// Lifecycle
onMounted(() => {
  getEntries(1);
});
</script>
