<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import BreadCroumbs from '@/OwnComponents/BreadCroumbs.vue';

const props = defineProps({
    usersCount: Number,
    categoriesCount: Number,
    articlesCount: Number,
    latestUser: String,
    latestCategory: String,
    latestArticle: String,
});

const breadcrumbs = [
    {
        label: "Dashboard"
    }
];
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <BreadCroumbs :items="breadcrumbs"></BreadCroumbs>
        </template>

        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">

                        <div
                            class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="red" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25c0-3.727 3.023-6.75 6.75-6.75h1.5c3.727 0 6.75 3.023 6.75 6.75v.75H4.5v-.75z" />
                              </svg>
                              
                            <h5 class="mb-2 mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                Users</h5>

                            <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">{{ usersCount }} registered users</p>

                            <p class="inline-flex items-center text-black-600">
                                Last update: {{ latestUser }}
                            </p>
                        </div>
                        <div
                            class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                class="w-7 h-7 stroke-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>

                            <h5 class="mb-2 mt-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                Articles</h5>

                            <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">{{ articlesCount }} active articles</p>

                            <p class="inline-flex items-center text-black-600">
                                Last update: {{ latestArticle }}
                            </p>
                        </div>
                        <div
                            class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="red" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                              </svg>
                              

                            <h5 class="mb-3 mt-3 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                Categories</h5>

                            <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">{{ categoriesCount }} active categories</p>

                            <p class="inline-flex items-center text-black-600">
                                Last update: {{ latestCategory }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
