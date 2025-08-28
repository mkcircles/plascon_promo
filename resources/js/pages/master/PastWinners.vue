<template>
    <div class="container mx-auto py-4">
        <div class="mb-4 flex justify-between items-center">
            <div class="flex-1 pr-4">
                <div class="relative md:w-1/3">
                    <h2 class="text-2xl font-semibold leading-normal mt-0 text-gray-800" id="basic">Past Winners</h2>
                    <p class="text-gray-500">All numbers</p>
                </div>
            </div>
            <div>
                <form>   
    <label for="default-search" class="mb-2  text-sm font-medium text-gray-900 sr-only dark:text-gray-300">Search</label>
    <div class="relative">
        <!-- <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div> -->
        <input type="text" v-model="phoneNumber" id="default-search" class="block p-4 pl-10 w-80 text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Add Phone number" required>
        <button type="submit" @click.prevent="addToBlacklist" class="text-white absolute right-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Add Number</button>
    </div>
</form>
            </div>
            
        </div>

        <TableData :colums="columns" >
            
                <tr
                    v-for="td in entries.data"
                    :key="td.id"
                    class="border-b text-sm border-gray-200 hover:bg-gray-100"
                >
                    <td class="py-2 px-6 text-left">{{ td.msisdn }}</td>
                    <td class="py-2 px-6 text-left">{{ td.user.name }}</td>
                    <td class="py-2 px-6 text-left">{{ changeDateFormat(td.created_at) }}</td>
                    <td class="py-2 px-6 text-left">
                        <button class="" @click.prevent="deleteFromList(td.id)">Delete</button>
                    </td>
                    
                    
                </tr>

        </TableData>
        <div class="flex justify-center">
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
import ThePaginator from "@/components/UI/ThePaginator.vue";
import TableData from "@/components/tables/TableData.vue";
import moment from "moment";

export default {
    name: "DataTable",
    data() {
        return {
            columns: [
                { name: "Phone Number", key: "phone", sortable: true },
                { name: "Added By", key: "status", sortable: true },
                { name: "Date", key: "Date", sortable: true },
                { name: "Action", key: "action", sortable: true },
            ],
            entries: [],
            category: "winner",
            phoneNumber: "",
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
            const response = await axios.get("/api/past-winners/"+this.category+"?page=" + page, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("token")}`,
                },
            });
            this.entries = response.data;
            this.links = response.data.links;
            this.currentPage = response.data.current_page;
            this.lastPage = response.data.last_page;

        },
        deleteFromList(id) {
            axios.delete("/api/past-winners/delete/"+ id, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("token")}`,
                },
            });
            window.location.reload();
        },
        addToBlacklist() {
            if(this.phoneNumber.length === 12) {
                axios.post("/api/past-winners/add", {
                    msisdn: this.phoneNumber,
                    category: this.category,
                }, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem("token")}`,
                    },
                });
                this.phoneNumber = "";
                window.location.reload();
            } else {
                alert("Please enter a valid phone number");
            }
           
        },
        showMore(page) {
            this.getEntries(page);
        },

        changeDateFormat(date) {
            var momentDate = moment(date)
            return momentDate.format("YYYY-MM-DD hh:mm:ss");
        },
       
    },
};
</script>

<style>
    
</style>
