<template>
    <div class="container mx-auto py-8 px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Analytics Dashboard</h2>
            <p class="text-slate-500 mt-1">Visualize real-time trends for inbound SMS traffic requests.</p>
        </div>

        <div class="w-full">
            <!-- SMS Message Traffic -->
            <MessageTrafficChart />
        </div>
    </div>
</template>

<script setup>
import MessageTrafficChart from "@/components/Chart/MessageTrafficChart.vue";
</script>

<style></style>
