<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
} from 'chart.js';
import { Bar, Doughnut } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const props = defineProps({
    totalBalance: Number,
    goals: Array,
    upcomingBills: Array,
    recentTransactions: Array,
    weeklyStats: Object,
    monthlyStats: Object
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
    }).format(amount);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
};

// --- Charts Data ---

// 1. Goals Chart (Target vs Achieved)
const goalsChartData = computed(() => {
    return {
        labels: props.goals.map(g => g.name),
        datasets: [
            {
                label: 'Saved',
                backgroundColor: '#10B981', // Green
                data: props.goals.map(g => Number(g.current_amount))
            },
            {
                label: 'Target',
                backgroundColor: '#E5E7EB', // Gray
                data: props.goals.map(g => Number(g.target_amount))
            }
        ]
    };
});

const goalsChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: { stacked: false },
        y: { beginAtZero: true }
    }
};

// 2. Weekly Statistics (This Week vs Last Week)
const weeklyStatsData = computed(() => {
    return {
        labels: ['Income', 'Expenses'],
        datasets: [
            {
                label: 'This Week',
                backgroundColor: '#6366F1', // Indigo
                data: [Number(props.weeklyStats.thisWeek.income), Number(props.weeklyStats.thisWeek.expense)]
            },
            {
                label: 'Last Week',
                backgroundColor: '#9CA3AF', // Gray
                data: [Number(props.weeklyStats.lastWeek.income), Number(props.weeklyStats.lastWeek.expense)]
            }
        ]
    };
});

const weeklyStatsOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { beginAtZero: true }
    }
};

// 3. Expenses Breakdown (Doughnut)
const expensesChartData = computed(() => {
    return {
        labels: props.monthlyStats.breakdown.map(item => item.category || 'Uncategorized'),
        datasets: [
            {
                backgroundColor: ['#F87171', '#FB923C', '#FBBF24', '#34D399', '#60A5FA', '#A78BFA', '#F472B6'],
                data: props.monthlyStats.breakdown.map(item => Number(item.total))
            }
        ]
    };
});

const expensesChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right' }
    }
};

// Calculate monthly comparison percentage
const monthlyChange = computed(() => {
    const current = props.monthlyStats.thisMonthTotal;
    const last = props.monthlyStats.lastMonthTotal;
    if (last === 0) return current > 0 ? 100 : 0;
    return Math.round(((current - last) / last) * 100);
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Overview
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Top Row: Balance & Bank Card -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Card 1: Total Balance -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">Total Balance</h3>
                            <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">
                                {{ formatCurrency(totalBalance) }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">Across all accounts</p>
                        </div>
                        <div class="mt-6">
                            <Link :href="route('balances')" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm font-medium">
                                See all accounts &rarr;
                            </Link>
                        </div>
                    </div>

                    <!-- Card 2: Visual Bank Card -->
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 text-white flex flex-col justify-between relative overflow-hidden h-48 lg:col-span-1">
                        <!-- Decorative circles -->
                        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 rounded-full bg-white opacity-10"></div>
                        <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 rounded-full bg-white opacity-10"></div>
                        
                        <div class="flex justify-between items-start z-10">
                            <span class="text-lg font-bold tracking-wider">Financial App</span>
                            <svg class="h-8 w-8 text-white opacity-80" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2 10h20v10a2 2 0 01-2 2H4a2 2 0 01-2-2V10zm0-2V6a2 2 0 012-2h16a2 2 0 012 2v2H2zm10 8h6v2h-6v-2z"/>
                            </svg>
                        </div>
                        <div class="z-10">
                            <p class="text-2xl font-mono tracking-widest mb-1">•••• •••• •••• 4242</p>
                            <div class="flex justify-between items-end mt-4">
                                <div>
                                    <p class="text-xs opacity-75 uppercase">Card Holder</p>
                                    <p class="font-medium tracking-wide">User Name</p>
                                </div>
                                <div>
                                    <p class="text-xs opacity-75 uppercase">Expires</p>
                                    <p class="font-medium tracking-wide">12/28</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Upcoming Bill (Moved up for better layout on lg screens) -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Upcoming Bill</h3>
                            <Link :href="route('bills')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All</Link>
                        </div>
                        
                        <div v-if="upcomingBills.length > 0">
                            <div class="border-l-4 border-indigo-500 pl-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-r-md">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Due {{ formatDate(upcomingBills[0].due_date) }}</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ upcomingBills[0].name }}</p>
                                <p class="text-lg font-semibold text-indigo-600 dark:text-indigo-400 mt-1">{{ formatCurrency(upcomingBills[0].amount) }}</p>
                            </div>
                            <div v-if="upcomingBills.length > 1" class="mt-4 space-y-2">
                                <p class="text-xs text-gray-500 uppercase font-medium">Next up:</p>
                                <div v-for="bill in upcomingBills.slice(1, 3)" :key="bill.id" class="flex justify-between text-sm">
                                    <span class="text-gray-700 dark:text-gray-300">{{ bill.name }}</span>
                                    <span class="text-gray-900 dark:text-white font-medium">{{ formatDate(bill.due_date) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No upcoming bills found.
                        </div>
                    </div>
                </div>

                <!-- Middle Row: Statistics & Goals -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Card 3: Goals Chart -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Goals Progress</h3>
                            <Link :href="route('goals')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Manage</Link>
                        </div>
                        <div class="h-64">
                            <Bar :data="goalsChartData" :options="goalsChartOptions" />
                        </div>
                    </div>

                    <!-- Card 6: Weekly Statistics -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Weekly Comparison</h3>
                        <div class="h-64">
                            <Bar :data="weeklyStatsData" :options="weeklyStatsOptions" />
                        </div>
                    </div>

                    <!-- Card 7: Expenses Breakdown -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Monthly Expenses</h3>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(monthlyStats.thisMonthTotal) }}</p>
                                <p class="text-sm" :class="monthlyChange > 0 ? 'text-red-500' : 'text-green-500'">
                                    {{ monthlyChange > 0 ? '+' : '' }}{{ monthlyChange }}% vs Last Month
                                </p>
                            </div>
                        </div>
                        <div class="h-56 relative">
                            <Doughnut :data="expensesChartData" :options="expensesChartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: Recent Activity -->
                <!-- Card 5: Recent Activity -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Activity</h3>
                        <Link :href="route('transactions')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All Activity</Link>
                    </div>

                    <div class="overflow-y-auto max-h-96 pr-2">
                        <ul class="space-y-4">
                            <li v-for="transaction in recentTransactions" :key="transaction.id" class="flex justify-between items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                                <div class="flex items-center space-x-4">
                                    <div class="p-2 rounded-full" :class="transaction.type === 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                                        <svg v-if="transaction.type === 'income'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ transaction.name }}</p>
                                        <p class="text-sm text-gray-500">{{ transaction.category }} • {{ formatDate(transaction.date) }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                        {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(Math.abs(transaction.amount)) }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ transaction.status }}</p>
                                </div>
                            </li>
                            <li v-if="recentTransactions.length === 0" class="text-center py-4 text-gray-500">
                                No recent activity.
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
