<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

import { Users, GraduationCap, DollarSign, TrendingUp } from 'lucide-vue-next';

interface Stats {
    totalStudents: number;
    totalGrades: number;
    totalFees: number;
    totalCollected: number;
}

const props = defineProps<{
    stats?: Stats;
    studentsByGrade?: Record<string, number>;
    paymentStatus?: Record<string, number>;
    recentPayments?: any[];
    monthlyCollection?: Record<string, number>;
}>();

// Provide defaults
const stats = props.stats || { totalStudents: 0, totalGrades: 0, totalFees: 0, totalCollected: 0 };
const studentsByGrade = props.studentsByGrade || {};
const paymentStatus = props.paymentStatus || {};
const recentPayments = props.recentPayments || [];
const monthlyCollection = props.monthlyCollection || {};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-KE', {
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors = {
        paid: 'bg-green-500',
        partial: 'bg-yellow-500',
        pending: 'bg-red-500',
    };
    return colors[status as keyof typeof colors] || colors.pending;
};

// Calculate collection rate
const collectionRate = stats.totalFees > 0
    ? Math.round((stats.totalCollected / stats.totalFees) * 100)
    : 0;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Students</p>
                            <h3 class="text-3xl font-bold">{{ stats.totalStudents }}</h3>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
                            <Users class="h-6 w-6 text-blue-600 dark:text-blue-300" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Active Grades</p>
                            <h3 class="text-3xl font-bold">{{ stats.totalGrades }}</h3>
                        </div>
                        <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900">
                            <GraduationCap class="h-6 w-6 text-purple-600 dark:text-purple-300" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Fees</p>
                            <h3 class="text-2xl font-bold">{{ formatCurrency(stats.totalFees) }}</h3>
                        </div>
                        <div class="rounded-full bg-amber-100 p-3 dark:bg-amber-900">
                            <DollarSign class="h-6 w-6 text-amber-600 dark:text-amber-300" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Collected</p>
                            <h3 class="text-2xl font-bold">{{ formatCurrency(stats.totalCollected) }}</h3>
                            <p class="text-xs text-green-600 dark:text-green-400">{{ collectionRate }}% collected</p>
                        </div>
                        <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
                            <TrendingUp class="h-6 w-6 text-green-600 dark:text-green-300" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Students by Grade Chart -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
                    <h3 class="mb-4 text-lg font-semibold">Students by Grade</h3>
                    <div class="space-y-3">
                        <div v-for="(count, grade) in studentsByGrade" :key="grade">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium">{{ grade }}</span>
                                <span class="text-sm text-muted-foreground">{{ count }} students</span>
                            </div>
                            <div class="h-2 w-full bg-gray-200 rounded-full dark:bg-gray-700">
                                <div
                                    class="h-2 bg-blue-600 rounded-full dark:bg-blue-500"
                                    :style="{ width: `${(count / stats.totalStudents) * 100}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Chart -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
                    <h3 class="mb-4 text-lg font-semibold">Payment Status</h3>
                    <div class="space-y-3">
                        <div v-for="(count, status) in paymentStatus" :key="status">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <div :class="getStatusColor(status)" class="h-3 w-3 rounded-full"></div>
                                    <span class="text-sm font-medium capitalize">{{ status }}</span>
                                </div>
                                <span class="text-sm text-muted-foreground">{{ count }} payments</span>
                            </div>
                            <div class="h-2 w-full bg-gray-200 rounded-full dark:bg-gray-700">
                                <div
                                    :class="getStatusColor(status)"
                                    class="h-2 rounded-full"
                                    :style="{ width: `${(count / Object.values(paymentStatus).reduce((a, b) => a + b, 0)) * 100}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Collection Trend -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
                <h3 class="mb-4 text-lg font-semibold">Monthly Collection Trend</h3>
                <div class="flex items-end gap-2 h-48">
                    <div
                        v-for="(amount, month) in monthlyCollection"
                        :key="month"
                        class="flex-1 flex flex-col items-center gap-2"
                    >
                        <div class="w-full bg-blue-600 rounded-t dark:bg-blue-500 hover:bg-blue-700 transition-colors"
                             :style="{ height: `${(amount / Math.max(...Object.values(monthlyCollection))) * 100}%` }"
                             :title="formatCurrency(amount)"
                        ></div>
                        <span class="text-xs text-muted-foreground">{{ month.split('-')[1] }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-sidebar">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold">Recent Payments</h3>
                    <div class="space-y-3">
                        <div
                            v-for="payment in recentPayments"
                            :key="payment.id"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-sidebar-accent"
                        >
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center dark:bg-blue-900">
                                    <span class="text-sm font-semibold text-blue-600 dark:text-blue-300">
                                        {{ payment.student?.first_name?.[0] }}{{ payment.student?.last_name?.[0] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium">{{ payment.student?.first_name }} {{ payment.student?.last_name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ payment.fee?.term }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">{{ formatCurrency(payment.amount_paid) }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(payment.payment_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
