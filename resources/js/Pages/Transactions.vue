<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import DangerButton from '@/Components/DangerButton.vue';
import MonthSelector from '@/Components/MonthSelector.vue';

const props = defineProps({
    transactions: Object,
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

const handleFilterChange = ({ year, month }) => {
    router.get(route('transactions'), { year, month }, { preserveState: true, preserveScroll: true });
};

const groupedTransactions = computed(() => {
    const groups = {};
    if (!props.transactions.data) return groups;
    
    props.transactions.data.forEach(transaction => {
        const date = new Date(transaction.date);
        const monthYear = date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
        
        if (!groups[monthYear]) {
            groups[monthYear] = [];
        }
        groups[monthYear].push(transaction);
    });
    return groups;
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    amount: '',
    date: '',
    type: 'expense',
    category: '',
    status: 'Completed'
});

const isImportModalOpen = ref(false);

const importForm = useForm({
    file: null,
});

const openImportModal = () => {
    isImportModalOpen.value = true;
};

const closeImportModal = () => {
    isImportModalOpen.value = false;
    importForm.reset();
    importForm.clearErrors();
};

const submitImport = () => {
    importForm.post(route('transactions.import'), {
        onSuccess: () => closeImportModal(),
    });
};

const openModal = (transaction = null) => {
    isModalOpen.value = true;
    if (transaction) {
        isEditing.value = true;
        editingId.value = transaction.id;
        form.name = transaction.name;
        form.amount = transaction.amount;
        form.date = transaction.date;
        form.type = transaction.type;
        form.category = transaction.category;
        form.status = transaction.status;
    } else {
        isEditing.value = false;
        editingId.value = null;
        form.reset();
        form.date = new Date().toISOString().split('T')[0];
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('transactions.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('transactions.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTransaction = (id) => {
    if (confirm('Are you sure you want to delete this transaction?')) {
        router.delete(route('transactions.destroy', id));
    }
};

</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Transactions
                </h2>
                <div class="flex space-x-2">
                    <SecondaryButton @click="openImportModal()">
                        Import PDF
                    </SecondaryButton>
                    <PrimaryButton @click="openModal()">
                        Add Transaction
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <!-- Filters Section -->
                    <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                        <MonthSelector 
                            :selected-year="Number(filters.year || new Date().getFullYear())" 
                            :selected-month="filters.month || 'all'" 
                            @change="handleFilterChange" 
                        />
                    </div>

                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <!-- Empty State -->
                        <div v-if="!transactions.data || transactions.data.length === 0" class="text-center py-10">
                            <p class="text-gray-500">No transactions found. Start by adding one!</p>
                        </div>

                        <!-- Transactions Table Grouped by Month -->
                        <div v-else class="space-y-8">
                            <div v-for="(group, month) in groupedTransactions" :key="month">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 px-1">{{ month }}</h3>
                                <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                                <th scope="col" class="relative px-6 py-3">
                                                    <span class="sr-only">Actions</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                            <tr 
                                                v-for="transaction in group" 
                                                :key="transaction.id"
                                                :class="{'bg-yellow-100 dark:bg-yellow-900/50 transition-colors duration-1000': transaction.id === highlightedId}"
                                            >
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ transaction.name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ transaction.date }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                        {{ transaction.category || 'Uncategorized' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span :class="[transaction.status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800', 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium']">
                                                        {{ transaction.status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <span :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                                        {{ transaction.type === 'income' ? '+' : '-' }}£{{ Math.abs(transaction.amount).toFixed(2) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button @click="openModal(transaction)" class="bg-indigo-600 text-white hover:bg-indigo-700 px-3 py-1 rounded-md text-sm font-medium transition-colors mr-2 shadow-sm">Edit</button>
                                                    <button @click="deleteTransaction(transaction.id)" class="bg-red-600 text-white hover:bg-red-700 px-3 py-1 rounded-md text-sm font-medium transition-colors shadow-sm">Delete</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div class="flex justify-between items-center mt-6">
                                <div class="text-sm text-gray-700 dark:text-gray-400">
                                    Showing {{ transactions.from }} to {{ transactions.to }} of {{ transactions.total }} results
                                </div>
                                <div class="flex space-x-1">
                                    <template v-for="(link, k) in transactions.links" :key="k">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            v-html="link.label"
                                            class="px-4 py-2 border rounded-md text-sm font-medium bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                                            :class="{ 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700': link.active }"
                                            :preserve-scroll="true"
                                        />
                                        <span
                                            v-else
                                            v-html="link.label"
                                            class="px-4 py-2 border rounded-md text-sm font-medium opacity-50 cursor-not-allowed bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600"
                                        ></span>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Transaction Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ isEditing ? 'Edit Transaction' : 'Add New Transaction' }}
                </h2>

                <div class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="name" value="Name / Description" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g. Grocery Store"
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
                            <InputLabel for="date" value="Date" />
                            <TextInput
                                id="date"
                                v-model="form.date"
                                type="date"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.date" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="type" value="Type" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            >
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            >
                                <option value="Completed">Completed</option>
                                <option value="Pending">Pending</option>
                            </select>
                            <InputError :message="form.errors.status" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="category" value="Category" />
                        <select
                            id="category"
                            v-model="form.category"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option value="">Select Category</option>
                            <option value="Food">Food</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Transport">Transport</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Shopping">Shopping</option>
                            <option value="Housing">Housing</option>
                            <option value="Income">Income</option>
                            <option value="Investment">Investment</option>
                            <option value="Savings">Savings</option>
                            <option value="Others">Others</option>
                        </select>
                        <InputError :message="form.errors.category" class="mt-2" />
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
                        {{ isEditing ? 'Update Transaction' : 'Save Transaction' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Import PDF Modal -->
        <Modal :show="isImportModalOpen" @close="closeImportModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Import Transactions from PDF
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Upload your bank statement PDF. The system will try to extract transactions automatically.
                </p>

                <div class="mt-6">
                    <InputLabel for="file" value="PDF Statement" />
                    <input
                        id="file"
                        type="file"
                        @input="importForm.file = $event.target.files[0]"
                        accept=".pdf"
                        class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                    />
                    <InputError :message="importForm.errors.file" class="mt-2" />
                    <progress v-if="importForm.progress" :value="importForm.progress.percentage" max="100">
                        {{ importForm.progress.percentage }}%
                    </progress>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeImportModal"> Cancel </SecondaryButton>

                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': importForm.processing }"
                        :disabled="importForm.processing"
                        @click="submitImport"
                    >
                        Import
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
