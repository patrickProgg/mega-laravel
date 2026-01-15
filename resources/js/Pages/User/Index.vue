<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { InfoCircleOutlined } from "@ant-design/icons-vue";
import { UserOutlined } from "@ant-design/icons-vue";
import { ref, watch, computed, h } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";
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

const memberPerPage = ref(5);
const memberCurrentPage = ref(1);

const form = ref({
    email: "",
    password: "",
    fname: "",
    lname: "",
    status: "",
    phone1: "",
});

watch(perPage, () => {
    fetchUsers(1);
});

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchUsers(1);
    }, 400);
});

function fetchUsers(pageNumber = currentPage.value) {
    currentPage.value = pageNumber;

    router.get(
        "/user",
        {
            page: pageNumber,
            ...(perPage.value !== 10 && { perPage: perPage.value }),
            ...(search.value && { search: search.value }),
        },
        { preserveScroll: true }
    );
}

function openAddModal() {
    editingUser.value = null;
    resetForm();
    showModal.value = true;
}

function openEditModal(user) {
    editingUser.value = user;
    form.value.email = user.email;
    form.value.password = "";
    form.value.fname = user.fname;
    form.value.lname = user.lname;
    form.value.status = user.status;
    form.value.phone1 = user.phone1;
    showModal.value = true;
}

function saveUser() {
    if (editingUser.value) {
        router.put(`/user/${editingUser.value.hd_id}`, form.value, {
            onSuccess: (page) => {
                users.value = page.props.users;
                resetForm();
                message.success("User updated successfully");
            },
        });
    } else {
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
    form.value = {
        email: "",
        password: "",
        fname: "",
        lname: "",
        status: "",
        phone1: "",
    };
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
    { title: "ID#", dataIndex: "hd_id", key: "hd_id", width: 20 },
    { title: "Full Name", dataIndex: "full_name", key: "full_name" },
    { title: "Contact#", dataIndex: "phone1", key: "phone1" },
    { title: "Address", dataIndex: "address", key: "address" },
    {
        title: "Status",
        dataIndex: "status",
        key: "status",
        width: 100,
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
    pageSizeOptions: [10, 20, 50, 100],
    style: { marginRight: "1.25rem", marginTop: "1rem", textAlign: "right" },
    onChange: (page, pageSizeValue) => {
        perPage.value = Number(pageSizeValue);
        fetchUsers(page);
    },
}));

const memberColumns = [
    { title: "ID#", dataIndex: "ln_id", key: "ln_id", width: 20 },
    { title: "Full Name", dataIndex: "full_name", key: "full_name" },
    { title: "Address", dataIndex: "address", key: "address" },
    { title: "Phone", dataIndex: "phone1", key: "phone1" },
    {
        title: "Status",
        dataIndex: "status",
        key: "status",
        width: 100,
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
];

function viewUser(user) {
    router.get(
        "/user",
        { view: user.hd_id },
        {
            preserveState: true,
            replace: true,
        }
    );
}

watch(
    () => page.props.selectedUser,
    (user) => {
        if (user) {
            selectedUser.value = user;
            memberDataSource.value = user.members;
            showViewModal.value = true;
        } else {
            showViewModal.value = false;
        }
    },
    { immediate: true }
);

const memberPagination = computed(() => ({
    current: memberCurrentPage.value,
    pageSize: memberPerPage.value,
    total: memberDataSource.value.length,
    showSizeChanger: true,
    pageSizeOptions: [5, 10, 25, 50],
    style: { marginRight: "1.25rem", marginTop: "1rem", textAlign: "right" },
    onChange: (page, pageSize) => {
        memberCurrentPage.value = page;
        memberPerPage.value = pageSize;
    },
}));

watch(showViewModal, (open) => {
    if (!open) {
        router.get("/user", {}, { preserveState: true, replace: true });
    }
});

function openAddMember() {
    showAddMemberModal.value = true;
}
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
                    <a-button type="primary" @click="openAddModal">Add Member</a-button>
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
                        rowKey="hd_id"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- <div
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
    <!-- </div>
    </div>  -->

    <a-modal
        v-model:open="showModal"
        :title="editingUser ? 'Update Member' : 'Add Member'"
        width="1000px"
        :destroyOnClose="false"
        :style="{ top: '20px' }"
    >
        <a-form
            ref="formRef"
            :model="form.value"
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
                :rules="[{ required: !editingUser, message: 'Please input password!' }]"
            >
                <a-input-password v-model:value="form.password" placeholder="Password" />
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
                    <a-select-option :value="0">Active</a-select-option>
                    <a-select-option :value="1">Inactive</a-select-option>
                    <a-select-option :value="2">Deceased</a-select-option>
                </a-select>
            </a-form-item>
        </a-form>

        <!-- Footer slot -->
        <template #footer>
            <a-button danger @click="closeModal">Close</a-button>
            <a-button
                type="primary"
                @click="saveUser"
            >
                {{ editingUser ? "Update" : "Add" }}
            </a-button>
        </template>
    </a-modal>

    <a-modal
        v-model:open="showViewModal"
        title="Member Details"
        width="1000px"
        :destroyOnClose="false"
        :style="{ top: '20px' }"
    >
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
                :pagination="memberPagination"
                rowKey="hd_id"
            />
        </div>

        <template #footer>
            <a-button danger @click="showViewModal = false"> Close </a-button>
        </template>
    </a-modal>
</template>
