<template>
    <div class="container mx-auto py-4">
        <div class="mb-4 flex justify-between items-center">
            <div class="flex-1 pr-4">
                <div class="relative md:w-1/3">
                    <h3 class="text-xl font-semibold leading-normal mt-0 text-gray-800" id="basic">Search: {{search}}</h3>
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
        <div v-if="error" class="flex my-3">
            <div class=" text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">No Data Found! </strong>
                <span class="block sm:inline">{{error}}</span>
            </div>
        </div>
        <div >
            <TableData :colums="columns" >
            
                <tr
                    v-for="td in entries.data"
                    :key="td.id"
                    class="border-b text-sm border-gray-200 hover:bg-gray-100"
                >
                    <td class="py-2 px-6 text-left">{{ td.msisdn }}</td>
                    <td class="py-2 px-6 text-left">
                        <span :class="fieldState(td.status)">{{ td.status }}</span>
                    </td>
                    <td class="py-2 px-6 uppercase text-left">{{ td.inText }}</td>
                    <td class="py-2 px-6 text-left">{{ changeDateFormat(td.created_at) }}</td>
                    <td class="py-2 px-6 text-left">{{ td.response }}</td>
                    
                </tr>

    </TableData>
    <div class="flex justify-center mt-4" v-show="loading">
        <ThePaginator
            :links="links"
            :currentPage="currentPage"
            :lastPage="lastPage"
            @pagechanged="showMore"
           />
    </div>
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
import { onMounted } from "vue";

export default {
    name: "DataTable",
    data() {
        return {
            columns: [
                { name: "Phone Number", key: "phone", sortable: true },
                { name: "Status", key: "status", sortable: true },
                { name: "Message", key: "Message", sortable: true },
                { name: "Date", key: "Date", sortable: true },
                { name: "Response", key: "response", sortable: true },
            ],
            entries: [],
            links: [],
            currentPage: 1,
            lastPage: 1,
            loading: 1,
            error:"",
           
        };
    },
    components: {
        TableData,
        ThePaginator,
       
    },
    created() {
        this.search = this.$route.path.split('/').pop();
        this.searchParamEntries();
    },
    beforeRouteUpdate(to, from, next) {
        this.error = "";
        this.search = to.path.split('/').pop();
        this.searchParamEntries();
        next();
    },
    computed: {
        
        

    },
    methods: {
        async searchParamEntries(page = 1) {
            const response = await axios.get("/api/search/in-messages/param/"+this.search+"?page=" + page, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("token")}`,
                },
            });
            console.log(response.data);
            this.entries = response.data;
            this.links = response.data.links;
            this.currentPage = response.data.current_page;
            this.lastPage = response.data.last_page;
            //If there is no data, set error message
            console.log(this.entries.data);
            if (this.entries.data.length == 0) {
                this.error = "No data found for this search parameter "+this.search;
            }
            

            
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
