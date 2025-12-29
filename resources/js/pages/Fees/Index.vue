<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    fees?: any[];
}>();

const feeList = props.fees || [];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Fees',
        href: '#',
    },
];

const handleEdit = (feeId: number) => {
    alert('Edit fee: ' + feeId);
};

const handleView = (feeId: number) => {
    alert('View fee: ' + feeId);
};

const handleDelete = (feeId: number) => {
    if (confirm('Are you sure you want to delete this fee?')) {
        alert('Delete fee: ' + feeId);
    }
};

const handleCreate = () => {
    alert('Create new fee');
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const totalFees = feeList.reduce((sum, fee) => sum + parseFloat(fee.amount || 0), 0);
const uniqueTerms = [...new Set(feeList.map(f => f.term))].length;
const uniqueGrades = [...new Set(feeList.map(f => f.grade_id))].length;
</script>

<template>
    <Head title="Fees" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Total Fee Records</div>
                    <div class="text-2xl font-bold">{{ feeList.length }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Total Amount</div>
                    <div class="text-2xl font-bold">{{ formatCurrency(totalFees) }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Active Terms</div>
                    <div class="text-2xl font-bold">{{ uniqueTerms }}</div>
                </div>
            </div>

            <!-- Fees Table -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white md:min-h-min dark:border-sidebar-border dark:bg-sidebar">
                <div class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Fee Structure</h2>
                        <button
                            @click="handleCreate"
                            class="rounded-lg bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-200"
                        >
                            + Add Fee
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 dark:bg-sidebar-accent">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Due Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-sidebar">
                            <tr v-for="fee in feeList" :key="fee.id" class="hover:bg-gray-50 dark:hover:bg-sidebar-accent">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    {{ fee.grade?.name || 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ fee.term }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold">
                                    {{ formatCurrency(fee.amount) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ formatDate(fee.due_date) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="handleView(fee.id)"
                                            class="rounded px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950"
                                        >
                                            View
                                        </button>
                                        <button
                                            @click="handleEdit(fee.id)"
                                            class="rounded px-3 py-1 text-sm text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-950"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="handleDelete(fee.id)"
                                            class="rounded px-3 py-1 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950"
                                        >
                                            Delete
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
