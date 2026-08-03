<template>
    <TableData :colums="columns">
        <tr
            v-for="td in entriesData"
            :key="td.id"
            class="border-b text-sm border-gray-200 hover:bg-gray-100"
        >
            <td class="py-2 px-6 text-left">
                {{ maskCode ? genCode(td.code) : td.code }}
            </td>
            <td class="py-2 px-6 text-left">{{ td.brand }}</td>
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
</template>

<script>
import TableData from "@/components/tables/TableData.vue";
import moment from "moment";

export default {
    name: "CodesTable",
    components: {
        TableData,
    },
    props: {
        entries: {
            type: [Object, Array],
            required: true,
        },
        maskCode: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            columns: [
                { name: "Code", key: "code", sortable: true },
                { name: "Brand", key: "brand", sortable: true },
                { name: "Status", key: "status", sortable: true },
                { name: "Prize", key: "prizeWon", sortable: true },
                { name: "Phone Number", key: "inMessageId", sortable: true },
                { name: "Created At", key: "created_at", sortable: true },
                { name: "Updated At", key: "updated_at", sortable: true },
            ],
        };
    },
    computed: {
        entriesData() {
            if (Array.isArray(this.entries)) {
                return this.entries;
            }
            return this.entries?.data || [];
        },
    },
    methods: {
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
            if (!code) return "";
            return code.slice(0, 4) + "XXXX";
        },
        changeDateFormat(date) {
            if (!date) return "";
            return moment(date).format("YYYY-MM-DD hh:mm:ss");
        },
    },
};
</script>
