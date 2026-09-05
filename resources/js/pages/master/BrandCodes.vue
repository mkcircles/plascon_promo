<template>
    <div class="container mx-auto py-4">
        <div class="mb-4 flex justify-between items-center">
            <div class="flex-1 pr-4">
                <div class="relative md:w-1/3">
                    <h2 class="text-2xl font-semibold leading-normal mt-0 text-gray-800" id="basic">{{brandName}} Codes</h2>
                    <p class="text-gray-500">All Codes</p>
                </div>
            </div>
            <div>
            </div>
        </div>

        <CodesTable :entries="entries" />
        <div class="flex justify-center mt-4" v-show="loading">
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
import {useRoute} from 'vue-router'
import CodesTable from "@/components/tables/CodesTable.vue";
import ThePaginator from "@/components/UI/ThePaginator.vue";

export default {
    name: "BrandCodes",
    data() {
        return {
            entries: [],
            links: [],
            currentPage: 1,
            lastPage: 1,
            brand: "",
            loading: 1,
        };
    },
    components: {
        CodesTable,
        ThePaginator,
    },
    created() {
        this.brand = decodeURIComponent(this.$route.params.region || this.$route.params.brand || this.$route.path.split('/').pop());
        this.getEntries();
    },
    beforeRouteUpdate(to, from, next) {
        this.brand = decodeURIComponent(to.params.region || to.params.brand || to.path.split('/').pop());
        this.getEntries();
        next();
    },
    computed: {
        brandName() {
            return this.brand.replace(/[-_]/g, " ").replace(/\w\S*/g, function (txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
        },
    },
    methods: {
        async getEntries(page = 1) {
            const response = await axios.get("/api/brand-codes/"+this.brand+"?page=" + page, {
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
