<template>
    <div class="between:flex mb-3">
        <div class="justify-between">
            <span class="right:margin-1">Show</span>
            <select class="right:margin-1" v-model="currentEntries" @change="paginateData">
                <option v-for="e in showEntries" :key="e" :value="e">{{e}}</option>
                
                </select>
        </div>
        <div class="end:flex">
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="create">Create</button>
        </div>
        

    </div>
  <TableData :colums="columns" :data="filteredEntries" />
</template>

<script>
import axios from 'axios';
import { $array } from 'alga-js';
import TableData from './tables/TableData.vue';
export default {
    name: "DataTable",
    data() {
        return {
            columns: [
                { name: 'Code', key: 'code', sortable: true },
                { name: 'Area', key: 'area', sortable: true },
                { name: 'Status', key: 'status', sortable: true },
                { name: 'Prize', key: 'prizeWon', sortable: true },
                { name: 'Message ID', key: 'inMessageId', sortable: true },
                { name: 'Created At', key: 'created_at', sortable: true },
                { name: 'Updated At', key: 'updated_at', sortable: true },
            ],
            entries: [],
            showEntries: [10,25,50,100],
            currentEntries: 10,
            filteredEntries: [],
        };
    },
    components: {
        TableData,
    },
    created() {
        this.getEntries().then(res => {
            this.entries = res.data.data
            this.filteredEntries = $array.paginate(this.entries)(1, this.currentEntries);
            console.log(this.filteredEntries);
        });
    },
    methods: {
        async getEntries() {
            const response = await axios.get('/api/promo-codes',{
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
            })
            return response;
        },
        async paginateData() {
            this.filteredEntries = $array.paginate(this.entries)(1, this.currentEntries);
        },
    },
    // {
    //         "id": 1,
    //         "code": "KPKAG7XY",
    //         "area": "Kampala",
    //         "status": "pending",
    //         "inMessageId": "",
    //         "prizeWon": "",
    //         "created_at": "2022-08-24T17:00:38.000000Z",
    //         "updated_at": null
    //     },

}
</script>

<style>

</style>