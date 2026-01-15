<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, watch, computed, h } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { reactive } from "vue";
import Swal from "sweetalert2";
import { message } from "ant-design-vue";

const page = usePage();

// reactive references
const users = ref(page.props.users);
const showModal = ref(false);
const showViewModal = ref(false);
const selectedUser = ref(null);
const memberDataSource = ref([]);
const editingUser = ref(null);
const perPage = ref(Number(page.props.perPage) || 10);
const search = ref(page.props.search || "");
const currentPage = ref(users.value.current_page || 1);

const form = ref({
    password: "",
    fname: "",
    lname: "",
    status: "",
    email: "",
    phone1: "",
});

// Watch for perPage changes to reload data
watch(perPage, () => {
    fetchUsers(1);
});

// Watch for search input (with debounce)
let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchUsers(1); // always reset to first page on search
    }, 400); // 400ms debounce
});

function fetchUsers(pageNumber = currentPage.value) {
    currentPage.value = pageNumber; // make sure this updates
    router.get(
        "/user",
        { perPage: perPage.value, page: pageNumber, search: search.value },
        { preserveState: false, preserveScroll: true, replace: true }
    );
}

function openAddModal() {
    editingUser.value = null;
    resetForm();
    showModal.value = true;
}

function openEditModal(user) {
    editingUser.value = user;
    form.value.phone1 = user.phone1;
    form.value.fname = user.fname;
    form.value.lname = user.lname;
    form.value.email = user.email;
    form.value.password = "";
    form.value.status = user.status;
    showModal.value = true;
}

function saveUser() {
    if (editingUser.value) {
        // Update
        router.put(`/user/${editingUser.value.hd_id}`, form.value, {
            onSuccess: (page) => {
                users.value = page.props.users;
                resetForm();
                message.success("User updated successfully");
            },
        });
    } else {
        // Add
        router.post("/user", form.value, {
            onSuccess: (page) => {
                users.value = page.props.users;
                resetForm();
                message.success("User added successfully");
            },
        });
    }
}

function resetForm() {
    form.value = { email: "", password: "", fname: "", lname: "" };
    editingUser.value = null;
    showModal.value = false;
}

function closeModal() {
    resetForm();
}

const dataSource = computed(() =>
    users.value.data.map((user) => ({
        ...user,
        key: user.hd_id,
    }))
);

const columns = [
    { title: "ID#", dataIndex: "hd_id", key: "hd_id" },
    { title: "Full Name", dataIndex: "full_name", key: "full_name" },
    { title: "Contact#", dataIndex: "phone1", key: "phone1" },
    { title: "Address", dataIndex: "address", key: "address" },
    {
        title: "Status",
        dataIndex: "status",
        key: "status",
        customRender: ({ text }) => {
            let label = "";
            let colorClass = "";

            if (text === 0) {
                label = "Active";
                colorClass = "bg-green-100 text-green-700";
            } else if (text === 1) {
                label = "Inactive";
                colorClass = "bg-red-100 text-red-700";
            } else if (text === 2) {
                label = "Deceased";
                colorClass = "bg-blue-100 text-blue-700";
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

                h(
                    "button",
                    {
                        class: "text-blue-500 hover:text-blue-700 rounded",
                        onClick: () => viewUser(record),
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
                h(
                    "button",
                    {
                        class: "text-blue-500 hover:text-blue-700 rounded",
                        onClick: () => sendUser(record),
                        title: "Send",
                    },
                    [
                        h(
                            "svg",
                            {
                                xmlns: "http://www.w3.org/2000/svg",
                                class: "h-5 w-5",
                                fill: "currentColor",
                                viewBox: "0 0 24 24",
                            },
                            [
                                h("path", {
                                    d: "M2.01 21L23 12 2.01 3 2 10l15 2-15 2z",
                                }),
                            ]
                        ),
                    ]
                ),
            ]);
        },
    },
];

const pagination = computed(() => ({
    current: users.value.current_page || 1,
    pageSize: perPage.value,
    total: users.value.total || 0,
    showSizeChanger: true,
    pageSizeOptions: [10, 25, 50, 100],
    onChange: (page, pageSizeValue) => {
        perPage.value = Number(pageSizeValue);
        fetchUsers(page);
    },
}));

function viewUser(user) {
    selectedUser.value = user;
    showViewModal.value = true;

    // Load members from API
    router.get(
        `/user/${user.hd_id}`,
        {},
        {
            onSuccess: (page) => {
                console.log("Members", page.props.user.members);
                memberDataSource.value = page.props.user.members;
            },
        }
    );
}

// const cachedUsers = reactive({});

// function viewUser(record) {
//     const id = record.hd_id;
//     if (cachedUsers[id]) {
//         selectedUser.value = cachedUsers[id];
//         showViewModal.value = true;
//         return;
//     }

//     router.get(
//         `/user/${id}`,
//         {},
//         {
//             onSuccess: (page) => {
//                 cachedUsers[id] = page.props.user;
//                 selectedUser.value = page.props.user;
//                 showViewModal.value = true;
//             },
//         }
//     );
// }

const memberColumns = [
    { title: "ID", dataIndex: "hd_id", key: "hd_id" },
    { title: "Full Name", dataIndex: "fname", key: "fname" },
    { title: "Email", dataIndex: "email", key: "email" },
    {
        title: "Status",
        dataIndex: "status",
        key: "status",
        customRender: ({ text }) => {
            if (text === 0) return "Active";
            if (text === 1) return "Inactive";
            if (text === 2) return "Deceased";
            return "";
        },
    },
];
</script>

<template>
    <Head title="User List" />

    <AuthenticatedLayout>
        <!-- <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">User List</h2>
        </template> -->

        <div class="py-5">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center justify-between space-x-4">
                    <button
                        @click="openAddModal"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow"
                    >
                        Add Member
                    </button>

                    <!-- <div>
                        <a-button type="primary" @click="showModals"
                            >Open Modal of 1000px width</a-button
                        >
                        <a-modal
                            v-model:open="open"
                            width="1000px"
                            title="Basic Modal"
                            @ok="handleOk"
                        >
                            <p>Some contents...</p>
                            <p>Some contents...</p>
                            <p>Some contents...</p>
                        </a-modal>
                    </div> -->

                    <!-- Right: Search -->
                    <div class="relative flex-grow">
                        <!-- Search Icon -->
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3a7.5 7.5 0 006.15 13.65z"
                                />
                            </svg>
                        </div>

                        <!-- Search Input -->
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search..."
                            class="w-full pl-10 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                    <a-table
                        :dataSource="dataSource"
                        :columns="columns"
                        :pagination="pagination"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
        <div class="bg-white rounded-md shadow-lg p-6 w-full max-w-md">
            <h2 class="text-lg font-medium mb-4">
                {{ editingUser ? "Edit User" : "Add User" }}
            </h2>

            <form @submit.prevent="saveUser" class="max-w-md mb-0 pb-0">
                <div class="relative z-0 w-full mb-5 group">
                    <input
                        type="email"
                        v-model="form.email"
                        id="floating_email"
                        autocomplete="new-email"
                        class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer"
                        placeholder=" "
                        required
                    />
                    <label
                        for="floating_email"
                        class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                        >Email address</label
                    >
                </div>

                <div class="relative z-0 w-full mb-5 group">
                    <input
                        type="password"
                        v-model="form.password"
                        id="floating_password"
                        autocomplete="new-password"
                        class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer"
                        placeholder=" "
                        :required="!editingUser"
                    />
                    <label
                        for="floating_password"
                        class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                        >Password</label
                    >
                </div>

                <div class="grid md:grid-cols-2 md:gap-6">
                    <div class="relative z-0 w-full mb-5 group">
                        <input
                            type="text"
                            v-model="form.fname"
                            id="floating_fname"
                            class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer"
                            placeholder=" "
                            required
                        />
                        <label
                            for="floating_fname"
                            class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                            >First Name</label
                        >
                    </div>

                    <div class="relative z-0 w-full mb-5 group">
                        <input
                            type="text"
                            v-model="form.lname"
                            id="floating_lname"
                            class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer"
                            placeholder=" "
                            required
                        />
                        <label
                            for="floating_lname"
                            class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                            >Last Name</label
                        >
                    </div>

                    <div v-if="editingUser" class="relative z-0 w-full mb-5 group">
                        <label
                            for="status"
                            class="block mb-2 text-sm font-medium text-gray-700"
                        >
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="block w-full px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="0">Active</option>
                            <option value="1">Inactive</option>
                            <option value="2">Deceased</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors"
                    >
                        {{ editingUser ? "Update" : "Add" }}
                    </button>

                    <button
                        @click="closeModal"
                        type="button"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </form>

            <!-- <a-form
                ref="formRef"
                :model="form"
                :label-col="{ span: 6 }"
                :wrapper-col="{ span: 18 }"
                @submit.prevent="saveUser"
            >
                <a-form-item
                    label="Email"
                    name="email"
                    :rules="[
                        {
                            required: true,
                            type: 'email',
                            message: 'Please input a valid email!',
                        },
                    ]"
                >
                    <a-input v-model:value="form.email" placeholder="Email address" />
                </a-form-item>

                <a-form-item
                    label="Password"
                    name="password"
                    :rules="[
                        { required: !editingUser, message: 'Please input password!' },
                    ]"
                >
                    <a-input-password
                        v-model:value="form.password"
                        placeholder="Password"
                    />
                </a-form-item>

                <a-form-item
                    label="First Name"
                    name="fname"
                    :rules="[{ required: true, message: 'Please input first name!' }]"
                >
                    <a-input v-model:value="form.fname" placeholder="First Name" />
                </a-form-item>

                <a-form-item
                    label="Last Name"
                    name="lname"
                    :rules="[{ required: true, message: 'Please input last name!' }]"
                >
                    <a-input v-model:value="form.lname" placeholder="Last Name" />
                </a-form-item>

                <a-form-item v-if="editingUser" label="Status" name="status">
                    <a-select v-model:value="form.status" placeholder="Select status">
                        <a-select-option value="0">Active</a-select-option>
                        <a-select-option value="1">Inactive</a-select-option>
                        <a-select-option value="2">Deceased</a-select-option>
                    </a-select>
                </a-form-item>

                <a-form-item :wrapper-col="{ span: 18, offset: 6 }">
                    <a-button type="primary" html-type="submit">{{
                        editingUser ? "Update" : "Add"
                    }}</a-button>
                    <a-button type="danger" style="margin-left: 10px" @click="closeModal"
                        >Cancel</a-button
                    >
                </a-form-item>
            </a-form> -->
        </div>
    </div>
    <a-modal v-model:open="showViewModal" title="Member Details" width="1000px">
        <p>
            Family Representative –
            <strong>{{ selectedUser?.full_name }}</strong>
        </p>
        <a-divider orientation="left">Family Members</a-divider>

        <div class="flex justify-start mb-3">
            <a-button type="primary" @click="openAddMember"> Add Member </a-button>
        </div>

        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
            <a-table
                :dataSource="memberDataSource"
                :columns="memberColumns"
                :pagination="false"
                rowKey="hd_id"
            />
        </div>
    </a-modal>
</template>
