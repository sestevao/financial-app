<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import MonthSelector from '@/Components/MonthSelector.vue';

const props = defineProps({
    goals: Array,
    filters: Object
});

const page = usePage();
const highlightedId = ref(null);

const checkHighlight = () => {
    if (page.props.flash?.updated_id) {
        highlightedId.value = page.props.flash.updated_id;
        setTimeout(() => {
            highlightedId.value = null;
        }, 3000);
    }
};

watch(() => page.props.flash?.updated_id, () => {
    checkHighlight();
});

onMounted(() => {
    checkHighlight();
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    target_amount: '',
    current_amount: '',
    deadline: ''
});

const handleFilterChange = ({ year, month }) => {
    router.get(route('goals'), { year, month }, { preserveState: true, preserveScroll: true });
};

const openModal = (goal = null) => {
    isModalOpen.value = true;
    if (goal) {
        isEditing.value = true;
        editingId.value = goal.id;
        form.name = goal.name;
        form.target_amount = goal.target_amount;
        form.current_amount = goal.current_amount;
        form.deadline = goal.deadline;
    } else {
        isEditing.value = false;
        editingId.value = null;
        form.reset();
        form.current_amount = 0;
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('goals.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('goals.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const isDepositModalOpen = ref(false);
const depositGoalId = ref(null);
const depositForm = useForm({
    amount: ''
});

const openDepositModal = (goal) => {
    depositGoalId.value = goal.id;
    isDepositModalOpen.value = true;
    depositForm.amount = '';
};

const closeDepositModal = () => {
    isDepositModalOpen.value = false;
    depositForm.reset();
};

const submitDeposit = () => {
    depositForm.post(route('goals.deposit', depositGoalId.value), {
        onSuccess: () => closeDepositModal(),
    });
};

const deleteGoal = (id) => {
    if (confirm('Are you sure you want to delete this goal?')) {
        router.delete(route('goals.destroy', id));
    }
};

const calculateProgress = (current, target) => {
    if (!target || target == 0) return 0;
    return Math.min(Math.round((current / target) * 100), 100);
};
</script>

<template>
    <Head title="Goals" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Savings Goals
                </h2>
                <PrimaryButton @click="openModal()">
                    Add Goal
                </PrimaryButton>
            </div>
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

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div v-if="goals.length === 0" class="col-span-full text-center py-10 bg-white dark:bg-gray-800 rounded-lg shadow">
                        <p class="text-gray-500">No goals set yet. Start saving today!</p>
                    </div>

                    <div 
                        v-for="goal in goals" 
                        :key="goal.id" 
                        class="overflow-hidden shadow-sm sm:rounded-lg p-6 relative transition-colors duration-1000"
                        :class="[
                            goal.id === highlightedId ? 'bg-yellow-100 dark:bg-yellow-900/50' : 'bg-white dark:bg-gray-800'
                        ]"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ goal.name }}</h3>
                                <p class="text-sm text-gray-500" v-if="goal.deadline">Deadline: {{ goal.deadline }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="openDepositModal(goal)" class="bg-green-600 text-white hover:bg-green-700 p-2 rounded-md shadow-sm transition-colors" title="Deposit Funds">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button @click="openModal(goal)" class="bg-indigo-600 text-white hover:bg-indigo-700 p-2 rounded-md shadow-sm transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                <button @click="deleteGoal(goal.id)" class="bg-red-600 text-white hover:bg-red-700 p-2 rounded-md shadow-sm transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mb-2 flex justify-between text-sm font-medium">
                            <span class="text-gray-700 dark:text-gray-300">£{{ Number(goal.current_amount).toFixed(2) }}</span>
                            <span class="text-gray-500">of £{{ Number(goal.target_amount).toFixed(2) }}</span>
                        </div>

                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-indigo-600 h-2.5 rounded-full" :style="{ width: calculateProgress(goal.current_amount, goal.target_amount) + '%' }"></div>
                        </div>
                        <p class="text-right text-xs text-gray-500 mt-1">{{ calculateProgress(goal.current_amount, goal.target_amount) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ isEditing ? 'Edit Goal' : 'Add New Goal' }}
                </h2>

                <div class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="name" value="Goal Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g. New Car"
                            autofocus
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="target_amount" value="Target Amount" />
                            <TextInput
                                id="target_amount"
                                v-model="form.target_amount"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full"
                                placeholder="1000.00"
                            />
                            <InputError :message="form.errors.target_amount" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="current_amount" value="Saved So Far" />
                            <TextInput
                                id="current_amount"
                                v-model="form.current_amount"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full"
                                placeholder="0.00"
                            />
                            <InputError :message="form.errors.current_amount" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="deadline" value="Target Date (Optional)" />
                        <TextInput
                            id="deadline"
                            v-model="form.deadline"
                            type="date"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.deadline" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{ isEditing ? 'Update Goal' : 'Save Goal' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="isDepositModalOpen" @close="closeDepositModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Add Funds to Goal
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    This will create a transaction and update your goal progress.
                </p>

                <div class="mt-6">
                    <InputLabel for="deposit_amount" value="Amount to Deposit" />
                    <TextInput
                        id="deposit_amount"
                        v-model="depositForm.amount"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        placeholder="0.00"
                        autofocus
                    />
                    <InputError :message="depositForm.errors.amount" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeDepositModal"> Cancel </SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': depositForm.processing }"
                        :disabled="depositForm.processing"
                        @click="submitDeposit"
                    >
                        Deposit
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
