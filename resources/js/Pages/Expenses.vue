<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';
import { computed } from 'vue';
import MonthSelector from '@/Components/MonthSelector.vue';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    expensesByCategory: Array,
    totalExpense: Number,
    filters: Object
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
    }).format(amount);
};

const handleFilterChange = ({ year, month }) => {
    router.get(route('expenses'), { year, month }, { preserveState: true, preserveScroll: true });
};

const chartData = computed(() => {
    return {
        labels: props.expensesByCategory.map(item => item.category || 'Uncategorized'),
        datasets: [
            {
                backgroundColor: ['#41B883', '#E46651', '#00D8FF', '#DD1B16', '#FB923C', '#A78BFA', '#F472B6', '#6B7280', '#10B981', '#F59E0B'],
                data: props.expensesByCategory.map(item => item.total)
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false
};
</script>

<template>
    <Head title="Expenses" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Expenses Breakdown
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Breakdown List -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium mb-4">Expenses by Category</h3>
                            <ul class="space-y-3">
                                <li v-for="expense in expensesByCategory" :key="expense.category" class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-2 last:border-0">
                                    <span class="text-gray-600 dark:text-gray-300">{{ expense.category || 'Uncategorized' }}</span>
                                    <span class="font-bold text-gray-800 dark:text-white">{{ formatCurrency(expense.total) }}</span>
                                </li>
                            </ul>
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600 flex justify-between items-center">
                                <span class="text-lg font-bold">Total</span>
                                <span class="text-lg font-bold text-red-600">{{ formatCurrency(totalExpense) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6 text-gray-900 dark:text-gray-100 h-80">
                            <Doughnut :data="chartData" :options="chartOptions" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
