<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { computed, ref } from 'vue';
import { Filter, X } from 'lucide-vue-next';

const props = defineProps<{
    fees?: any[];
    grades?: any[];
    terms?: string[];
    filters?: {
        grade?: string;
        term?: string;
    };
}>();

const feeList = props.fees || [];
const gradesList = props.grades || [];
const termsList = props.terms || [];

const showFilters = ref(false);

// Filter form
const filterForm = useForm({
    grade: props.filters?.grade || 'all',
    term: props.filters?.term || 'all',
});

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

// Filter handlers
const applyFilters = () => {
    const params: Record<string, string> = {};

    if (filterForm.grade && filterForm.grade !== 'all') {
        params.grade = filterForm.grade;
    }
    if (filterForm.term && filterForm.term !== 'all') {
        params.term = filterForm.term;
    }

    router.get('/fees', params, {
        preserveState: false,
        preserveScroll: false,
        only: ['fees', 'filters'],
    });
};

const clearFilters = () => {
    filterForm.grade = 'all';
    filterForm.term = 'all';

    router.get('/fees', {}, {
        preserveState: false,
        preserveScroll: false,
    });
};

const hasActiveFilters = computed(() => {
    return !!(
        (filterForm.grade && filterForm.grade !== 'all') ||
        (filterForm.term && filterForm.term !== 'all')
    );
});

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
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                @click="showFilters = !showFilters"
                                class="gap-2"
                            >
                                <Filter class="h-4 w-4" />
                                Filters
                                <span v-if="hasActiveFilters" class="ml-1 rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground">
                                    Active
                                </span>
                            </Button>
                            <button
                                @click="handleCreate"
                                class="rounded-lg bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-200"
                            >
                                + Add Fee
                            </button>
                        </div>
                    </div>

                    <!-- Filter Panel -->
                    <div v-if="showFilters" class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-sidebar-accent">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold">Filter Fees</h3>
                            <Button
                                v-if="hasActiveFilters"
                                variant="ghost"
                                size="sm"
                                @click="clearFilters"
                                class="gap-2 text-red-600 hover:text-red-700"
                            >
                                <X class="h-4 w-4" />
                                Clear All
                            </Button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <!-- Grade Filter -->
                            <div class="grid gap-2">
                                <Label>Grade</Label>
                                <Select v-model="filterForm.grade">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Grades" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Grades</SelectItem>
                                        <SelectItem v-for="grade in gradesList" :key="grade.id" :value="grade.id.toString()">
                                            {{ grade.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Term Filter -->
                            <div class="grid gap-2">
                                <Label>Term</Label>
                                <Select v-model="filterForm.term">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Terms" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Terms</SelectItem>
                                        <SelectItem v-for="term in termsList" :key="term" :value="term">
                                            {{ term }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <Button variant="outline" @click="showFilters = false">
                                Close
                            </Button>
                            <Button @click="applyFilters" :disabled="filterForm.processing">
                                Apply Filters
                            </Button>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    <div v-if="hasActiveFilters && !showFilters" class="mb-4 flex flex-wrap gap-2">
                        <span class="text-sm text-muted-foreground">Active filters:</span>
                        <span v-if="filterForm.grade && filterForm.grade !== 'all'" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
                            Grade: {{ gradesList.find(g => g.id.toString() === filterForm.grade)?.name }}
                            <button @click="filterForm.grade = 'all'; applyFilters()" class="hover:text-red-600">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="filterForm.term && filterForm.term !== 'all'" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
                            Term: {{ filterForm.term }}
                            <button @click="filterForm.term = 'all'; applyFilters()" class="hover:text-red-600">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
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

                        <!-- Empty State -->
                        <div v-if="feeList.length === 0" class="py-12 text-center">
                            <p class="text-muted-foreground">No fees found matching your filters.</p>
                            <Button v-if="hasActiveFilters" variant="outline" class="mt-4" @click="clearFilters">
                                Clear Filters
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
