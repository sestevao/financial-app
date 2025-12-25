<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Navigation -->
        <nav class="container mx-auto px-6 py-6 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <img src="/logo-1.svg" alt="Logo" class="h-10 w-auto" />
                <span class="text-xl font-bold text-gray-800 dark:text-white">FinanceApp</span>
            </div>
            
            <div v-if="canLogin" class="flex items-center space-x-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500"
                >
                    Dashboard
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500"
                    >
                        Log in
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500"
                    >
                        Get Started
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="container mx-auto px-6 py-16 md:py-32 text-center md:text-left flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-12 md:mb-0">
                <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-6">
                    Master Your Money, <br class="hidden md:inline">
                    <span class="text-indigo-600 dark:text-indigo-400">Secure Your Future</span>
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed max-w-lg mx-auto md:mx-0">
                    The all-in-one platform to track transactions, manage bills, and set financial goals. Gain clarity on your spending today.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                    <Link
                        v-if="canRegister && !$page.props.auth.user"
                        :href="route('register')"
                        class="px-8 py-3 bg-indigo-600 text-white rounded-lg text-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl"
                    >
                        Create Free Account
                    </Link>
                    <Link
                         v-if="$page.props.auth.user"
                         :href="route('dashboard')"
                         class="px-8 py-3 bg-indigo-600 text-white rounded-lg text-lg font-semibold hover:bg-indigo-700 transition shadow-lg hover:shadow-xl"
                    >
                        Go to Dashboard
                    </Link>
                    <a href="#features" class="px-8 py-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-white rounded-lg text-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition">
                        Learn More
                    </a>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center md:justify-end relative">
                <!-- Abstract Hero Visual -->
                <div class="relative w-full max-w-md aspect-square">
                    <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500 to-purple-500 rounded-full opacity-20 blur-3xl animate-pulse"></div>
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 border border-gray-100 dark:border-gray-700 transform rotate-3 hover:rotate-0 transition duration-500">
                        <div class="flex items-center justify-between mb-6">
                            <div class="space-y-1">
                                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-3 w-16 bg-gray-100 dark:bg-gray-600 rounded"></div>
                            </div>
                            <div class="h-10 w-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs">S</div>
                                    <div class="h-3 w-20 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                </div>
                                <div class="h-3 w-12 bg-green-200 dark:bg-green-900 rounded"></div>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs">N</div>
                                    <div class="h-3 w-24 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                </div>
                                <div class="h-3 w-12 bg-red-200 dark:bg-red-900 rounded"></div>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">A</div>
                                    <div class="h-3 w-16 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                </div>
                                <div class="h-3 w-12 bg-blue-200 dark:bg-blue-900 rounded"></div>
                            </div>
                        </div>
                         <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                             <div class="flex justify-between items-end">
                                 <div class="h-3 w-24 bg-gray-100 dark:bg-gray-600 rounded"></div>
                                 <div class="h-6 w-32 bg-indigo-500 rounded"></div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Features Section -->
        <section id="features" class="bg-white dark:bg-gray-800 py-20">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold mb-4">Everything you need to manage wealth</h2>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Simple yet powerful tools to help you keep track of your financial life without the complexity.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-10">
                    <!-- Feature 1 -->
                    <div class="p-8 rounded-2xl bg-gray-50 dark:bg-gray-900 hover:shadow-lg transition">
                        <div class="w-14 h-14 bg-indigo-100 dark:bg-indigo-900 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Transaction Tracking</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Log income and expenses with ease. Categorize transactions to see exactly where your money goes.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 rounded-2xl bg-gray-50 dark:bg-gray-900 hover:shadow-lg transition">
                        <div class="w-14 h-14 bg-green-100 dark:bg-green-900 rounded-xl flex items-center justify-center text-green-600 dark:text-green-400 mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Goal Visualization</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Set financial goals and track your progress. Watch your savings grow with visual charts.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 rounded-2xl bg-gray-50 dark:bg-gray-900 hover:shadow-lg transition">
                        <div class="w-14 h-14 bg-pink-100 dark:bg-pink-900 rounded-xl flex items-center justify-center text-pink-600 dark:text-pink-400 mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Bill Management</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Keep track of recurring bills and due dates. Never miss a payment again.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-50 dark:bg-gray-900 py-12 border-t border-gray-200 dark:border-gray-800">
            <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <img src="/logo-1.svg" alt="Logo" class="h-8 w-auto grayscale opacity-50" />
                    <span class="text-gray-500 font-semibold">FinanceApp</span>
                </div>
                <div class="text-sm text-gray-500">
                    &copy; {{ new Date().getFullYear() }} FinanceApp. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
