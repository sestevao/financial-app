<script setup>
import { computed } from 'vue';

const props = defineProps({
    selectedYear: {
        type: Number,
        required: true
    },
    selectedMonth: {
        type: [Number, String],
        required: true
    }
});

const emit = defineEmits(['update:year', 'update:month', 'change']);

const months = [
    { value: 'all', label: 'All' },
    { value: '1', label: 'Jan' },
    { value: '2', label: 'Feb' },
    { value: '3', label: 'Mar' },
    { value: '4', label: 'Apr' },
    { value: '5', label: 'May' },
    { value: '6', label: 'Jun' },
    { value: '7', label: 'Jul' },
    { value: '8', label: 'Aug' },
    { value: '9', label: 'Sep' },
    { value: '10', label: 'Oct' },
    { value: '11', label: 'Nov' },
    { value: '12', label: 'Dec' },
];

const changeYear = (delta) => {
    const newYear = Number(props.selectedYear) + delta;
    emit('update:year', newYear);
    emit('change', { year: newYear, month: props.selectedMonth });
};

const changeMonth = (monthValue) => {
    emit('update:month', monthValue);
    emit('change', { year: props.selectedYear, month: monthValue });
};
</script>

<template>
    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
        <!-- Year Selector -->
        <div class="flex items-center space-x-4">
            <button @click="changeYear(-1)" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <span class="text-lg font-bold text-gray-700 dark:text-gray-200">{{ selectedYear }}</span>
            <button @click="changeYear(1)" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>

        <!-- Month Tabs -->
        <div class="flex overflow-x-auto pb-2 sm:pb-0 hide-scrollbar space-x-1 w-full sm:w-auto justify-start sm:justify-end">
            <button
                v-for="month in months"
                :key="month.value"
                @click="changeMonth(month.value)"
                class="px-3 py-1.5 text-sm font-medium rounded-md whitespace-nowrap transition-colors"
                :class="selectedMonth == month.value 
                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200' 
                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700'"
            >
                {{ month.label }}
            </button>
        </div>
    </div>
</template>
