<template>
    <div class="container mx-auto py-4">
        <div class="mb-4 flex justify-between items-center">
            <div class="flex-1 pr-4">
                <div class="relative md:w-1/3">
                    <h2 class="text-2xl font-semibold leading-normal mt-0 text-gray-800" id="basic">Used Codes</h2>
                    <p class="text-gray-500">All Codes</p>
                </div>
            </div>
            <div>
                
            </div>
        </div>

        <CodesTable :entries="entries" :maskCode="false" />
        <div class="flex justify-center mt-4">
            <ThePaginator
                :links="links"
                :currentPage="currentPage"
                :lastPage="lastPage"
                @pagechanged="showMore"
               />
        </div>
    </div>
</template>

<script>
import axios from "axios";
import CodesTable from "@/components/tables/CodesTable.vue";
import ThePaginator from "@/components/UI/ThePaginator.vue";

export default {
    name: "DataTable",
    data() {
        return {
            entries: [],
            links: [],
            currentPage: 1,
            lastPage: 1,
        };
    },
    components: {
        CodesTable,
        ThePaginator,
    },
    created() {
        this.getEntries();
    },
    computed: {
    },
    methods: {
        async getEntries(page = 1) {
            const response = await axios.get("/api/used-codes?page=" + page, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("token")}`,
                },
            });

            this.entries = response.data;
            this.links = response.data.links;
            this.currentPage = response.data.current_page;
            this.lastPage = response.data.last_page;
        },
        showMore(page) {
            this.getEntries(page);
        },
    },
};
</script>

<style>
    
</style>
