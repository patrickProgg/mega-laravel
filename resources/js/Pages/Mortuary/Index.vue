<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, watch } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";

const page = usePage();

// reactive references
const deceased = ref(page.props.deceased);
const perPage = ref(page.props.perPage || 10);
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

function goToPage(link) {
    if (!link) return;

    // If the link is an object with a `url` property (from deceased.links)
    const urlString = typeof link === "object" ? link.url : link;
    if (!urlString) return;

    // Extract page number from URL
    const url = new URL(urlString, window.location.origin);
    const pageNumber = Number(url.searchParams.get("page") || 1);

    currentPage.value = pageNumber;
    fetchUsers(pageNumber);
}
</script>

<template>
    <Head title="Mortuary" />

    <AuthenticatedLayout>
        <!-- <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">User List</h2>
        </template> -->

        <div class="py-5">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center justify-between">
                    <!-- Left: Show per page -->
                    <div class="flex items-center space-x-2">
                        <label for="perPage" class="text-sm font-medium text-gray-700">
                            Show:
                        </label>
                        <select
                            v-model="perPage"
                            id="perPage"
                            @change="fetchUsers(1)"
                            class="block w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <!-- Right: Search -->
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search..."
                        class="w-64 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                </div>

                <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                    <table
                        class="w-full text-sm text-left text-gray-500 dark:text-gray-400"
                    >
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                        >
                            <tr>
                                <th scope="col" class="py-3 px-6">ID#</th>
                                <th scope="col" class="py-3 px-6">Full Name</th>
                                <th scope="col" class="py-3 px-6">Address</th>
                                <th scope="col" class="py-3 px-6">Passing Date</th>
                                <th scope="col" class="py-3 px-6">Phone</th>
                                <th scope="col" class="py-3 px-6">Amount</th>
                                <th scope="col" class="py-3 px-6">Ledger</th>
                                <th scope="col" class="py-3 px-6">Status</th>
                                <th scope="col" class="py-3 px-6 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="dec in deceased.data"
                                :key="dec.dd_id"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                <td
                                    class="py-2 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ dec.hd_id }}
                                </td>
                                <td
                                    class="py-2 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ dec.full_name }}
                                </td>
                                <td
                                    class="py-2 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ dec.address }}
                                </td>
                                <td
                                    class="py-2 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ dec.dd_date_died }}
                                </td>
                                <td
                                    class="py-2 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ dec.phone1 }}
                                </td>
                                <td
                                    class="py-2 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ dec.dd_total_amt }}
                                </td>
                                <td
                                    class="py-2 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ dec.dd_amt_rcv }}
                                </td>
                                <td
                                    class="py-2 px-6 font-medium whitespace-nowrap dark:text-white"
                                >
                                    <span
                                        v-if="dec.dd_status == 0"
                                        class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold bg-red-100 text-red-700"
                                    >
                                        Pending
                                    </span>

                                    <span
                                        v-else-if="dec.dd_status == 1"
                                        class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-700"
                                    >
                                        Partial
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold bg-green-100 text-green-700"
                                    >
                                        Settled
                                    </span>
                                </td>

                                <td
                                    class="py-2 px-1 flex items-center justify-center space-x-2"
                                >
                                    <button class="text-blue-500 hover:text-blue-700">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                c4.478 0 8.268 2.943 9.542 7
                                                -1.274 4.057-5.064 7-9.542 7
                                                -4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                    </button>

                                    <button class="text-green-500 hover:text-green-700">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 2h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 2v5h5"
                                            />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="deceased.data.length === 0">
                                <td
                                    colspan="3"
                                    class="py-4 px-6 text-center text-gray-500"
                                >
                                    No data found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Flowbite pagination -->
                    <div
                        class="flex items-center justify-between py-3 px-4 bg-white border-t border-gray-200 sm:px-6"
                    >
                        <span class="text-sm text-gray-700 dark:text-gray-400">
                            Showing
                            <span class="font-semibold text-gray-900 dark:text-white">{{
                                deceased.from
                            }}</span>
                            to
                            <span class="font-semibold text-gray-900 dark:text-white">{{
                                deceased.to
                            }}</span>
                            of
                            <span class="font-semibold text-gray-900 dark:text-white">{{
                                deceased.total
                            }}</span>
                            entries
                        </span>
                        <nav
                            class="inline-flex -space-x-px rounded-md shadow-sm"
                            aria-label="Pagination"
                        >
                            <button
                                v-for="link in deceased.data.links"
                                :key="link.label"
                                v-html="link.label"
                                :disabled="!link.url"
                                @click.prevent="goToPage(link.url)"
                                class="border border-gray-300 bg-white px-3 py-1 text-sm font-medium text-gray-500 hover:bg-gray-100 disabled:opacity-50"
                                :class="{ 'bg-blue-600 text-white': link.active }"
                            ></button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
