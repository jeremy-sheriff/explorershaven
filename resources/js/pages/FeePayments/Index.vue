<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';


const props = defineProps<{
    payments?: any[];
}>();

const paymentList = props.payments || [];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Fee Payments',
        href: '#',
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return 'Not paid';
    return new Date(date).toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors = {
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        partial: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        pending: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return colors[status as keyof typeof colors] || colors.pending;
};

const totalPaid = paymentList.reduce((sum, p) => sum + parseFloat(p.amount_paid || 0), 0);
const paidCount = paymentList.filter(p => p.status === 'paid').length;
const pendingCount = paymentList.filter(p => p.status === 'pending').length;
</script>

<template>
    <Head title="Fee Payments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Total Payments</div>
                    <div class="text-2xl font-bold">{{ paymentList.length }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Total Collected</div>
                    <div class="text-2xl font-bold">{{ formatCurrency(totalPaid) }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Paid / Pending</div>
                    <div class="text-2xl font-bold">{{ paidCount }} / {{ pendingCount }}</div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white md:min-h-min dark:border-sidebar-border dark:bg-sidebar">
                <div class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Fee Payments</h2>
                        <button
                            class="rounded-lg bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-200"
                        >
                            + Record Payment
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 dark:bg-sidebar-accent">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Amount Paid</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Payment Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-sidebar">
                            <tr v-for="payment in paymentList" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-sidebar-accent">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    {{ payment.student?.first_name }} {{ payment.student?.last_name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ payment.fee?.grade?.name || 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ payment.fee?.term || 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold">
                                    {{ formatCurrency(payment.amount_paid) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ formatDate(payment.payment_date) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span :class="getStatusColor(payment.status)" class="rounded-full px-2 py-1 text-xs font-medium uppercase">
                                        {{ payment.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            class="rounded px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950"
                                        >
                                            View
                                        </button>
                                        <button
                                            class="rounded px-3 py-1 text-sm text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-950"
                                        >
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
