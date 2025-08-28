<template>
    <div class="container mx-auto py-4">
        <div class="mb-4 flex justify-between items-center">
            <div class="flex-1 pr-4">
                <div class="relative md:w-1/3">
                    <h2 class="text-2xl font-semibold leading-normal mt-0 text-gray-800" id="basic">Generated Codes</h2>
                    <p class="text-gray-500">All Codes</p>
                </div>
            </div>
            <div>
                <!-- <button
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    @click="showModal = true"
                >
                    Generate New Code
                </button> -->
            </div>
        </div>

        <TableData :colums="columns" >
            
                <tr
                    v-for="td in entries.data"
                    :key="td.id"
                    class="border-b text-sm border-gray-200 hover:bg-gray-100"
                >
                    <td class="py-2 px-6 text-left">{{ genCode(td.code) }}</td>
                    <td class="py-2 px-6 text-left">{{ td.area }}</td>
                    <td class="py-2 px-6 text-left">
                        <span :class="fieldState(td.status)">{{ td.status }}</span>
                    </td>
                    <td class="py-2 px-6 text-left">{{ td.prizeWon }}</td>
                    <td class="py-2 px-6 text-left">
                        <span v-if="td.inMessageId">{{ td.inMessageId }}</span>
                    </td>
                    <td class="py-2 px-6 text-left">{{ changeDateFormat(td.created_at) }}</td>
                    <td class="py-2 px-6 text-left">
                        <span v-if="td.message">{{ changeDateFormat(td.message.created_at) }}</span>
                        
                    </td>
                </tr>

        </TableData>
        <div class="flex justify-center mt-4">
            <ThePaginator
                :links="links"
                :currentPage="currentPage"
                :lastPage="lastPage"
                @pagechanged="showMore"
               />
            
            <!-- <Pagination :data="entries" class="page-link relative block py-1.5 px-3 rounded border-0 bg-transparent outline-none transition-all duration-300 rounded text-gray-800 hover:text-gray-800 focus:shadow-none" @pagination-change-page="getEntries" /> -->
        </div>
    </div>
</template>

<script>
import axios from "axios";
import {useRoute} from 'vue-router'
//import LaravelVuePagination from 'laravel-vue-pagination';
import TableData from "@/components/tables/TableData.vue";
import ThePaginator from "@/components/UI/ThePaginator.vue";
import moment from "moment";

export default {
    name: "DataTable",
    data() {
        return {
            columns: [
                { name: "Code", key: "code", sortable: true },
                { name: "Area", key: "area", sortable: true },
                { name: "Status", key: "status", sortable: true },
                { name: "Prize", key: "prizeWon", sortable: true },
                { name: "Phone Number", key: "inMessageId", sortable: true },
                { name: "Created At", key: "created_at", sortable: true },
                { name: "Updated At", key: "updated_at", sortable: true },
            ],
            entries: [],
            links: [],
            currentPage: 1,
            lastPage: 1,
           
        };
    },
    components: {
        TableData,
        ThePaginator,
       
    },
    created() {
        this.getEntries();
    },
    computed: {
        

    },
    methods: {
        async getEntries(page = 1) {
            const response = await axios.get("/api/promo-codes?page=" + page, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("token")}`,
                },
            });
            this.entries = response.data;
            this.links = response.data.links;
            this.currentPage = response.data.current_page;
            this.lastPage = response.data.last_page;
            
        },
        fieldState(status) {
            if (status === "pending") {
                return "bg-orange-400 text-white py-1 px-3 rounded-full text-xs capitalize";
            } else if (status === "used") {
                return "bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs capitalize";
            } else {
                return "bg-blue-200 text-blue-600 py-1 px-3 rounded-full text-xs capitalize";
            }
        },
        genCode(code) {
            return code.slice(0,4)+"XXXX";
        },
        changeDateFormat(date) {
            var momentDate = moment(date)
            return momentDate.format("YYYY-MM-DD hh:mm:ss");
        },
        showMore(page) {
            this.getEntries(page);
        },
        
    },
};
</script>

<style>
    
</style>
