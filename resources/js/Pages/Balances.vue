<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import MonthSelector from '@/Components/MonthSelector.vue';

const props = defineProps({
    totalIncome: Number,
    totalExpense: Number,
    balance: Number,
    filters: Object
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
    }).format(amount);
};

const handleFilterChange = ({ year, month }) => {
    router.get(route('balances'), { year, month }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Balances" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Balances
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Month Selector -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-6">
                    <MonthSelector 
                        :selected-year="Number(filters.year)" 
                        :selected-month="filters.month || 'all'" 
                        @change="handleFilterChange" 
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Current Balance -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Current Balance</h3>
                            <p class="mt-2 text-3xl font-bold" :class="balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(balance) }}
                            </p>
                        </div>
                    </div>

                    <!-- Total Income -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Income</h3>
                            <p class="mt-2 text-3xl font-bold text-green-600">
                                +{{ formatCurrency(totalIncome) }}
                            </p>
                        </div>
                    </div>

                    <!-- Total Expenses -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Expenses</h3>
                            <p class="mt-2 text-3xl font-bold text-red-600">
                                -{{ formatCurrency(totalExpense) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
