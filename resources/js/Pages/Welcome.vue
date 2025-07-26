<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="CalibrationTrack - Professional Calibration Management" />
    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 min-h-screen">
        <!-- Header -->
        <header class="relative">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6 md:justify-start md:space-x-10">
                    <!-- Logo -->
                    <div class="flex justify-start lg:w-0 lg:flex-1">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 flex items-center">
                                <div class="h-10 w-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="ml-3 text-2xl font-bold text-gray-900 dark:text-white">CalibrationTrack</span>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <nav v-if="canLogin" class="hidden md:flex space-x-10">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="text-base font-medium text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="whitespace-nowrap text-base font-medium text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors"
                            >
                                Sign in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="whitespace-nowrap bg-blue-600 text-white px-4 py-2 rounded-md text-base font-medium hover:bg-blue-700 transition-colors shadow-sm"
                            >
                                Get Started
                            </Link>
                        </template>
                    </nav>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center py-16">
                <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                    <span class="block">Professional</span>
                    <span class="block text-blue-600">Calibration Management</span>
                </h1>
                <p class="mt-3 max-w-md mx-auto text-base text-gray-500 dark:text-gray-300 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                    Streamline your calibration processes with our comprehensive tracking system. 
                    Manage gages, track calibration schedules, and ensure compliance with ease.
                </p>
                <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                    <div class="rounded-md shadow">
                        <Link
                            v-if="!$page.props.auth.user && canRegister"
                            :href="route('register')"
                            class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10 transition-colors"
                        >
                            Start Free Trial
                        </Link>
                        <Link
                            v-else-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10 transition-colors"
                        >
                            Go to Dashboard
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10 transition-colors"
                        >
                            Sign In
                        </Link>
                    </div>
                    <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                        <a href="#features" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10 transition-colors">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div id="features" class="py-16">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center">
                        <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                            Everything you need for calibration management
                        </h2>
                        <p class="mt-4 max-w-2xl text-xl text-gray-500 dark:text-gray-300 lg:mx-auto">
                            Comprehensive tools to track, manage, and maintain your calibration processes
                        </p>
                    </div>

                    <div class="mt-16">
                        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                            <!-- Feature 1: Gage Management -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 9.172V5L8 4z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Gage Management</h3>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-base text-gray-500 dark:text-gray-300">
                                        Organize and track all your measurement gages with detailed information, 
                                        serial numbers, and location tracking.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 2: Calibration Scheduling -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Smart Scheduling</h3>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-base text-gray-500 dark:text-gray-300">
                                        Automated reminders and scheduling system to ensure calibrations 
                                        are never missed and compliance is maintained.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 3: Compliance Tracking -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Compliance Tracking</h3>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-base text-gray-500 dark:text-gray-300">
                                        Maintain detailed records and certificates to meet industry 
                                        standards and regulatory requirements.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 4: Department Management -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Department Organization</h3>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-base text-gray-500 dark:text-gray-300">
                                        Organize gages by departments and manage access controls 
                                        with role-based permissions.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 5: Reporting & Analytics -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-red-500 text-white">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Reports & Analytics</h3>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-base text-gray-500 dark:text-gray-300">
                                        Generate comprehensive reports and export data in multiple 
                                        formats for analysis and auditing.
                                    </p>
                                </div>
                            </div>

                            <!-- Feature 6: Checkout System -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Checkout System</h3>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-base text-gray-500 dark:text-gray-300">
                                        Track gage usage with a complete checkout and check-in system 
                                        including history and accountability.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="bg-blue-700 dark:bg-blue-800 rounded-lg shadow-xl">
                <div class="px-6 py-12 sm:px-12 sm:py-16 lg:px-16">
                    <div class="mx-auto max-w-4xl text-center">
                        <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                            Ready to streamline your calibration process?
                        </h2>
                        <p class="mt-6 text-xl leading-8 text-blue-200">
                            Join companies that trust CalibrationTrack to manage their measurement systems and maintain compliance.
                        </p>
                        <div class="mt-8 flex justify-center">
                            <div class="inline-flex rounded-md shadow">
                                <Link
                                    v-if="!$page.props.auth.user && canRegister"
                                    :href="route('register')"
                                    class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 transition-colors"
                                >
                                    Start Your Free Trial
                                </Link>
                                <Link
                                    v-else-if="$page.props.auth.user"
                                    :href="route('dashboard')"
                                    class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 transition-colors"
                                >
                                    Go to Dashboard
                                </Link>
                                <Link
                                    v-else
                                    :href="route('login')"
                                    class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 transition-colors"
                                >
                                    Sign In Now
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-24 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="ml-2 text-lg font-semibold text-gray-900 dark:text-white">CalibrationTrack</span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        &copy; {{ new Date().getFullYear() }} CalibrationTrack. Professional calibration management.
                    </div>
                </div>
               
            </div>
        </footer>
    </div>
</template>
