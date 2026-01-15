<style>
/* Make all table cells smaller padding */
.ant-table-cell {
    padding: 4px 8px !important;
    font-size: 14px; /* optional, smaller font */
    line-height: 2 !important;
}

.ant-table-row {
    height: 50px !important;
    max-height: 50px !important;
}

/* Rounded inputs */
.ant-input,
.ant-input-password,
.ant-select-selector {
    border-radius: 6px !important;
}

/* Reduce input height and form item spacing */
.ant-input,
.ant-input-number {
    height: 32px !important;
    font-size: 14px !important;
}

.ant-form-item {
    margin-bottom: 2px !important;
}

/* Placeholder styling */
.ant-input::placeholder,
.ant-input-password input::placeholder,
.ant-input-number input::placeholder {
    font-size: 13px !important;
    color: #bfbfbf !important;
}
</style>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { InfoCircleOutlined } from "@ant-design/icons-vue";
import { UserOutlined } from "@ant-design/icons-vue";
import { ref, watch, computed, h } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { message } from "ant-design-vue";

const page = usePage();
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

// Address dropdown options
const provinces = ref([]);
const cities = ref([]);
const barangays = ref([]);
const puroks = ref([]);

const form = ref({
    email: "",
    password: "",
    fname: "",
    mname: "",
    lname: "",
    birthday: "",
    age: "",
    phone1: "",
    phone2: "",
    purok: "",
    barangay: "",
    city: "",
    province: "",
    zip_code: "",
    status: "",
});

// Load provinces on mount
watch(showModal, async (isOpen) => {
    if (isOpen && provinces.value.length === 0) {
        await loadProvinces();
    }
});

async function loadProvinces() {
    try {
        const response = await fetch("/api/provinces");
        provinces.value = await response.json();
    } catch (error) {
        console.error("Failed to load provinces:", error);
    }
}

async function loadCities(provinceId) {
    if (!provinceId) {
        cities.value = [];
        form.value.zip_code = "";
        return;
    }
    try {
        const response = await fetch(`/api/cities/${provinceId}`);
        cities.value = await response.json();
    } catch (error) {
        console.error("Failed to load cities:", error);
    }
}

// Auto-populate zip code when city is selected
function handleCityChange(cityId) {
    const selectedCity = cities.value.find((c) => c.id === cityId);
    console.log("Selected city:", selectedCity);
    if (selectedCity && selectedCity.zip_code) {
        form.value.zip_code = selectedCity.zip_code;
        console.log("Zip code set to:", form.value.zip_code);
    } else {
        form.value.zip_code = "";
        console.log("No zip code found");
    }
}

async function loadBarangays(cityId) {
    if (!cityId) {
        barangays.value = [];
        return;
    }
    try {
        const response = await fetch(`/api/barangays/${cityId}`);
        barangays.value = await response.json();
    } catch (error) {
        console.error("Failed to load barangays:", error);
    }
}

async function loadPuroks(barangayId) {
    if (!barangayId) {
        puroks.value = [];
        return;
    }
    try {
        const response = await fetch(`/api/puroks/${barangayId}`);
        puroks.value = await response.json();
    } catch (error) {
        console.error("Failed to load puroks:", error);
    }
}

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
    console.log("Editing user:", user);
    editingUser.value = user;
    form.value.email = user.email;
    form.value.password = "";
    form.value.fname = user.fname;
    form.value.mname = user.mname;
    form.value.lname = user.lname;
    form.value.birthday = user.birthday;
    form.value.age = user.age;
    form.value.phone1 = user.phone1;
    form.value.phone2 = user.phone2;
    form.value.purok = user.purok;
    form.value.barangay = user.barangay;
    form.value.city = user.city;
    form.value.province = user.province;
    form.value.zip_code = user.zip_code;
    form.value.status = user.status;

    // Load cascading dropdowns
    if (user.province) {
        loadCities(user.province);
    }
    if (user.city) {
        loadBarangays(user.city);
    }
    if (user.barangay) {
        loadPuroks(user.barangay);
    }

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
    {
        title: "ID#",
        dataIndex: "hd_id",
        key: "hd_id",
        width: 20,
        customCell: () => ({
            style: {
                padding: "0",
            },
        }),
    },
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

watch(
    () => form.value.birthday,
    (newBirthday) => {
        if (newBirthday) {
            form.value.age = calculateAge(newBirthday);
        } else {
            form.value.age = "";
        }
    }
);

// Helper function to calculate age from birthday string
function calculateAge(birthday) {
    const birthDate = new Date(birthday);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}
</script>

<template>
    <Head title="User List" />

    <AuthenticatedLayout>
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

        <a-modal
            v-model:open="showModal"
            :title="editingUser ? 'Update Member' : 'Add Member'"
            width="800px"
            :destroyOnClose="false"
            :style="{ top: '20px' }"
        >
            <a-form
                ref="formRef"
                :model="form.value"
                layout="vertical"
                @submit.prevent="saveUser"
            >
                <a-divider orientation="left" style="color: #1890ff"
                    >Personal Information</a-divider
                >
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="12">
                        <a-form-item
                            label="First Name"
                            name="fname"
                            :rules="[{ message: 'Please input first name!' }]"
                        >
                            <a-input
                                :value="form.fname"
                                @input="
                                    form.fname =
                                        $event.target.value.charAt(0).toUpperCase() +
                                        $event.target.value.slice(1)
                                "
                                placeholder="First Name"
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item
                            label="Middle Name"
                            name="mname"
                            :rules="[{ message: 'Please input middle name!' }]"
                        >
                            <a-input
                                :value="form.mname"
                                @input="
                                    form.mname =
                                        $event.target.value.charAt(0).toUpperCase() +
                                        $event.target.value.slice(1)
                                "
                                placeholder="Middle Name"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="16">
                        <a-form-item
                            label="Last Name"
                            name="lname"
                            :rules="[{ message: 'Please input last name!' }]"
                        >
                            <a-input
                                :value="form.lname"
                                @input="
                                    form.lname =
                                        $event.target.value.charAt(0).toUpperCase() +
                                        $event.target.value.slice(1)
                                "
                                placeholder="Last Name"
                            />
                        </a-form-item>
                    </a-col>

                    <a-col :xs="24" :sm="5">
                        <a-form-item
                            label="Birthday"
                            name="birthday"
                            :rules="[{ message: 'Please input birthday!' }]"
                        >
                            <a-input
                                v-model:value="form.birthday"
                                placeholder="Birthday"
                                type="date"
                            />
                        </a-form-item>
                    </a-col>

                    <a-col :xs="24" :sm="3">
                        <a-form-item
                            label="Age"
                            name="age"
                            :rules="[{ message: 'Please input age!' }]"
                        >
                            <a-input
                                v-model:value="form.age"
                                placeholder="Age"
                                type="number"
                                readonly
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="12">
                        <a-form-item
                            label="Contact Number 1"
                            name="phone_number_1"
                            :rules="[{ message: 'Please input contact number 1!' }]"
                        >
                            <a-input
                                :value="form.phone1"
                                @input="form.phone1 = $event.target.value.slice(0, 11)"
                                placeholder="Contact Number 1"
                                type="tel"
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item
                            label="Contact Number 2"
                            name="phone_number_2"
                            :rules="[{ message: 'Please input contact number 2!' }]"
                        >
                            <a-input
                                :value="form.phone2"
                                @input="form.phone2 = $event.target.value.slice(0, 11)"
                                placeholder="Contact Number 2"
                                type="tel"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-divider orientation="left" style="color: #1890ff">Address</a-divider>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="12">
                        <a-form-item
                            label="Province"
                            name="province"
                            :rules="[{ message: 'Please select province!' }]"
                        >
                            <a-select
                                v-model:value="form.province"
                                placeholder="Select Province"
                                @change="
                                    (value) => {
                                        form.city = undefined;
                                        form.barangay = undefined;
                                        form.purok = undefined;
                                        loadCities(value);
                                    }
                                "
                            >
                                <a-select-option
                                    v-for="province in provinces"
                                    :key="province.id"
                                    :value="province.id"
                                >
                                    {{ province.name }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item
                            label="City"
                            name="city"
                            :rules="[{ message: 'Please select city!' }]"
                        >
                            <a-select
                                v-model:value="form.city"
                                placeholder="Select City"
                                :disabled="!form.province"
                                @change="
                                    (value) => {
                                        form.barangay = undefined;
                                        form.purok = undefined;
                                        form.zip_code = '';
                                        handleCityChange(value);
                                        loadBarangays(value);
                                    }
                                "
                            >
                                <a-select-option
                                    v-for="city in cities"
                                    :key="city.id"
                                    :value="city.id"
                                >
                                    {{ city.name }}
                                    {{ city.zip_code ? `(${city.zip_code})` : "" }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="16">
                        <a-form-item
                            label="Barangay"
                            name="barangay"
                            :rules="[{ message: 'Please select barangay!' }]"
                        >
                            <a-select
                                v-model:value="form.barangay"
                                placeholder="Select Barangay"
                                :disabled="!form.city"
                                @change="
                                    (value) => {
                                        form.purok = undefined;
                                        loadPuroks(value);
                                    }
                                "
                            >
                                <a-select-option
                                    v-for="barangay in barangays"
                                    :key="barangay.id"
                                    :value="barangay.id"
                                >
                                    {{ barangay.name }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="5">
                        <a-form-item
                            label="Purok"
                            name="purok"
                            :rules="[{ message: 'Please select purok!' }]"
                        >
                            <a-select
                                v-model:value="form.purok"
                                placeholder="Select Purok"
                                :disabled="!form.barangay"
                            >
                                <a-select-option
                                    v-for="purok in puroks"
                                    :key="purok.id"
                                    :value="purok.id"
                                >
                                    {{ purok.name }}
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="3">
                        <a-form-item
                            label="Zip Code"
                            name="zip_code"
                            :rules="[{ message: 'Please input zip code!' }]"
                        >
                            <a-input
                                v-model:value="form.zip_code"
                                placeholder="Zip Code"
                                disabled
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-divider v-if="editingUser" orientation="left" style="color: #1890ff"
                    >Status</a-divider
                >

                <a-form-item v-if="editingUser" label="Status" name="status">
                    <a-select v-model:value="form.status" placeholder="Select status">
                        <a-select-option :value="0">Active</a-select-option>
                        <a-select-option :value="1">Inactive</a-select-option>
                        <a-select-option :value="2">Deceased</a-select-option>
                    </a-select>
                </a-form-item>
            </a-form>

            <template #footer>
                <a-button danger @click="closeModal">Close</a-button>
                <a-button type="primary" @click="saveUser">
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
    </AuthenticatedLayout>
</template>
