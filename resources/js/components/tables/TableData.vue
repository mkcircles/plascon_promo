<template>
    <div
        class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative"
    >
        <table
            class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative"
        >
            <thead>
                <th
                    v-for="th in tableHeader"
                    :key="th"
                    class="bg-gray-100 text-left sticky top-0 border-b border-gray-200 px-6 py-2 text-gray-600 font-bold tracking-wider uppercase text-xs"
                >
                    {{ th.name }}
                </th>
            </thead>
            <tbody>
                <slot />
            </tbody>
        </table>
    </div>

   
</template>

<script>
import { capitalize } from "vue";

export default {
    name: "TableData",
    props: {
        colums: {
            type: Array,
            required: true,
        },
        
    },
    computed: {
        tableHeader() {
            return this.colums || [];
        },
        tableData() {
            return this.data || [];
        },
    },
    methods: {
        fieldState(status) {
            if (status === "pending") {
                return "bg-yellow-200 text-yellow-600 py-1 px-3 rounded-full text-xs";
            } else if (status === "used") {
                return "bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs";
            } else {
                return "bg-blue-200 text-blue-600 py-1 px-3 rounded-full text-xs";
            }
        },
    },
};
</script>

<style>
    .page-link {
        color: #393939;
        display: flex;
        padding: 0.5rem 0.75rem;
        border-style: solid;
        border-color: #e5e7eb;

    }
    .page-link:hover {
        background-color: #e5e7eb;
    }
    .page-link:focus {
        
    }
    .page-link .active {
        background-color: #cfd1d3;
        border-radius: 5px;
    }
</style>
