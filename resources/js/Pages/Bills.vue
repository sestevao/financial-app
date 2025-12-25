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
    bills: Array,
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
    amount: '',
    due_date: '',
    status: 'unpaid'
});

const handleFilterChange = ({ year, month }) => {
    router.get(route('bills'), { year, month }, { preserveState: true, preserveScroll: true });
};

const openModal = (bill = null) => {
    isModalOpen.value = true;
    if (bill) {
        isEditing.value = true;
        editingId.value = bill.id;
        form.name = bill.name;
        form.amount = bill.amount;
        form.due_date = bill.due_date;
        form.status = bill.status;
    } else {
        isEditing.value = false;
        editingId.value = null;
        form.reset();
        form.due_date = new Date().toISOString().split('T')[0];
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('bills.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('bills.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const payBill = (id) => {
    if (confirm('Pay this bill? This will create a transaction and mark bill as paid.')) {
        router.post(route('bills.pay', id));
    }
};

const deleteBill = (id) => {
    if (confirm('Are you sure you want to delete this bill?')) {
        router.delete(route('bills.destroy', id));
    }
};
</script>

<template>
    <Head title="Bills" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Bills & Recurring Payments
                </h2>
                <PrimaryButton @click="openModal()">
                    Add Bill
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

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div v-if="bills.length === 0" class="text-center py-10">
                            <p class="text-gray-500">No bills added yet.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bill Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="bill in bills" :key="bill.id" :class="{'bg-yellow-100 dark:bg-yellow-900/50 transition-colors duration-1000': bill.id === highlightedId}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ bill.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ bill.due_date }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[bill.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800', 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium']">
                                                {{ bill.status.toUpperCase() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            £{{ Number(bill.amount).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button v-if="bill.status === 'unpaid'" @click="payBill(bill.id)" class="bg-green-600 text-white hover:bg-green-700 px-3 py-1 rounded-md text-sm font-medium transition-colors mr-2 shadow-sm">Pay</button>
                                            <button @click="openModal(bill)" class="bg-indigo-600 text-white hover:bg-indigo-700 px-3 py-1 rounded-md text-sm font-medium transition-colors mr-2 shadow-sm">Edit</button>
                                            <button @click="deleteBill(bill.id)" class="bg-red-600 text-white hover:bg-red-700 px-3 py-1 rounded-md text-sm font-medium transition-colors shadow-sm">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ isEditing ? 'Edit Bill' : 'Add New Bill' }}
                </h2>

                <div class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="name" value="Bill Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g. Electricity"
                            autofocus
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="amount" value="Amount" />
                            <TextInput
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full"
                                placeholder="0.00"
                            />
                            <InputError :message="form.errors.amount" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="due_date" value="Due Date" />
                            <TextInput
                                id="due_date"
                                v-model="form.due_date"
                                type="date"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.due_date" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="status" value="Status" />
                        <select
                            id="status"
                            v-model="form.status"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option value="unpaid">Unpaid</option>
                            <option value="paid">Paid</option>
                        </select>
                        <InputError :message="form.errors.status" class="mt-2" />
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
                        {{ isEditing ? 'Update Bill' : 'Save Bill' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
