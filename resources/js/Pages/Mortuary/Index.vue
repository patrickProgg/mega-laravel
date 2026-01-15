<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { InfoCircleOutlined } from "@ant-design/icons-vue";
import { UserOutlined } from "@ant-design/icons-vue";
import { ref, watch, computed, h } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";

const page = usePage();

// reactive references
const deceased = ref(page.props.deceased);
const perPage = ref(Number(page.props.perPage) || 10);
const search = ref(page.props.search || "");
const currentPage = ref(deceased.value.current_page || 1);

// Watch for perPage changes to reload data
watch(perPage, () => {
    fetchUsers(1);
});

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchUsers(1); // always reset to first page on search
    }, 400); // 400ms debounce
});

function fetchUsers(pageNumber = currentPage.value) {
    currentPage.value = pageNumber;
    router.get(
        "/mortuary",
        { perPage: perPage.value, page: pageNumber, search: search.value },
        {
            preserveState: false, // keep previous state
            preserveScroll: true, // ✅ prevent scroll jump
            replace: true, // update URL without adding history
        }
    );
}

const dataSource = computed(() =>
    deceased.value.data.map((dec) => ({
        ...dec,
        key: dec.dd_id,
    }))
);

const columns = [
    { title: "ID#", dataIndex: "hd_id", key: "hd_id", width: 20 },
    { title: "Full Name", dataIndex: "full_name", key: "full_name" },
    { title: "Address", dataIndex: "address", key: "address" },
    { title: "Passing Date", dataIndex: "dd_date_died", key: "dd_date_died" },
    // { title: "Phone", dataIndex: "phone1", key: "phone1" },
    { title: "Amount", dataIndex: "dd_total_amt", key: "dd_total_amt" },
    { title: "Ledger", dataIndex: "dd_amt_rcv", key: "dd_amt_rcv" },
    {
        title: "Status",
        dataIndex: "dd_status",
        key: "status",
        width: 100,
        customRender: ({ text }) => {
            let label = "";
            let colorClass = "";

            if (text === 0) {
                label = "Pending";
                colorClass = "bg-red-100 text-red-700";
            } else if (text === 1) {
                label = "Partial";
                colorClass = "bg-blue-100 text-blue-700";
            } else if (text === 2) {
                label = "Settled";
                colorClass = "bg-green-100 text-green-700";
            }

            return h(
                "span",
                {
                    class: `inline-flex items-center px-2 py-1 rounded text-xs font-semibold ${colorClass}`,
                },
                label
            );
        },
    },
    {
        title: "Action",
        key: "action",
        width: 100,
        customRender: ({ record }) => {
            return h("div", { class: "flex space-x-2" }, [
                // Edit button with pencil icon
                h(
                    "button",
                    {
                        class: "text-yellow-500 hover:text-yellow-700 rounded",
                        onClick: () => openEditModal(record),
                        title: "Edit",
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                class: "h-5 w-5",
                                fill: "none",
                                viewBox: "0 0 24 24",
                                stroke: "currentColor",
                            },
                            [
                                h("path", {
                                    "stroke-linecap": "round",
                                    "stroke-linejoin": "round",
                                    "stroke-width": 2,
                                    d:
                                        "M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z",
                                }),
                            ]
                        ),
                    ]
                ),

                // Delete button with trash icon
                h(
                    "button",
                    {
                        class: "text-blue-500 hover:text-blue-700 rounded",
                        onClick: () => viewUser(record), // replace deleteUser with viewUser
                        title: "View",
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                class: "h-5 w-5",
                                fill: "none",
                                viewBox: "0 0 24 24",
                                stroke: "currentColor",
                            },
                            [
                                h("path", {
                                    "stroke-linecap": "round",
                                    "stroke-linejoin": "round",
                                    "stroke-width": 2,
                                    d:
                                        "M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z",
                                }),
                            ]
                        ),
                    ]
                ),
            ]);
        },
    },
];

// Antd Table pagination
const pagination = computed(() => ({
    current: deceased.value.current_page || 1,
    pageSize: perPage.value,
    total: deceased.value.total || 0,
    showSizeChanger: true,
    pageSizeOptions: [10, 25, 50, 100],
    onChange: (page, pageSizeValue) => {
        perPage.value = Number(pageSizeValue);
        fetchUsers(page);
    },
}));
</script>

<template>
    <Head title="Mortuary" />

    <AuthenticatedLayout>
        <!-- <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">User List</h2>
        </template> -->

        <div class="py-5">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center justify-between space-x-4">
                    <!-- Right: Search -->
                    <div class="relative flex-grow">
                        <div class="components-input-demo-presuffix">
                            <a-input v-model:value="search" placeholder="Search...">
                                <template #prefix>
                                    <user-outlined />
                                </template>
                                <template #suffix>
                                    <a-tooltip title="Extra information">
                                        <info-circle-outlined
                                            style="color: rgba(0, 0, 0, 0.45)"
                                        />
                                    </a-tooltip>
                                </template>
                            </a-input>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                    <a-table
                        :dataSource="dataSource"
                        :columns="columns"
                        :pagination="pagination"
                        rowKey="dd_id"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
