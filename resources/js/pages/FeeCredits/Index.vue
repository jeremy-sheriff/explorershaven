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
    credits?: any[];
    students?: any[];
    fees?: any[];
    grades?: any[];
    totalAvailable?: number;
    totalApplied?: number;
    filters?: {
        student?: string;
        status?: string;
        grade?: string;
    };
}>();

const creditList = props.credits || [];
const studentsList = props.students || [];
const feesList = props.fees || [];
const gradesList = props.grades || [];

const showFilters = ref(false);

// Filter form
const filterForm = useForm({
    student: props.filters?.student || 'all',
    status: props.filters?.status || 'all',
    grade: props.filters?.grade || 'all',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Fee Credits',
        href: '#',
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
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors = {
        available: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        applied: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return colors[status as keyof typeof colors] || colors.available;
};

// Filter handlers
const applyFilters = () => {
    const params: Record<string, string> = {};

    if (filterForm.student && filterForm.student !== 'all') {
        params.student = filterForm.student;
    }
    if (filterForm.status && filterForm.status !== 'all') {
        params.status = filterForm.status;
    }
    if (filterForm.grade && filterForm.grade !== 'all') {
        params.grade = filterForm.grade;
    }

    router.get('/fee-credits', params, {
        preserveState: false,
        preserveScroll: false,
        only: ['credits', 'filters'],
    });
};

const clearFilters = () => {
    filterForm.student = 'all';
    filterForm.status = 'all';
    filterForm.grade = 'all';

    router.get('/fee-credits', {}, {
        preserveState: false,
        preserveScroll: false,
    });
};

const hasActiveFilters = computed(() => {
    return !!(
        (filterForm.student && filterForm.student !== 'all') ||
        (filterForm.status && filterForm.status !== 'all') ||
        (filterForm.grade && filterForm.grade !== 'all')
    );
});

const totalCredits = creditList.reduce((sum, credit) => sum + parseFloat(credit.amount || 0), 0);
</script>

<template>
    <Head title="Fee Credits (Overpayments)" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Total Credits</div>
                    <div class="text-2xl font-bold">{{ creditList.length }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Available Credits</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(totalAvailable || 0) }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Applied Credits</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(totalApplied || 0) }}</div>
                </div>
            </div>

            <!-- Credits Table -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white md:min-h-min dark:border-sidebar-border dark:bg-sidebar">
                <div class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Fee Credits</h2>
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
                    </div>

                    <!-- Filter Panel -->
                    <div v-if="showFilters" class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-sidebar-accent">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold">Filter Credits</h3>
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

                        <div class="grid gap-4 md:grid-cols-3">
                            <!-- Student Filter -->
                            <div class="grid gap-2">
                                <Label>Student</Label>
                                <Select v-model="filterForm.student">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Students" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Students</SelectItem>
                                        <SelectItem v-for="student in studentsList" :key="student.id" :value="student.id.toString()">
                                            {{ student.first_name }} {{ student.last_name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Status Filter -->
                            <div class="grid gap-2">
                                <Label>Status</Label>
                                <Select v-model="filterForm.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        <SelectItem value="available">Available</SelectItem>
                                        <SelectItem value="applied">Applied</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

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
                        <span v-if="filterForm.student && filterForm.student !== 'all'" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
                            Student: {{ studentsList.find(s => s.id.toString() === filterForm.student)?.first_name }}
                            <button @click="filterForm.student = 'all'; applyFilters()" class="hover:text-red-600">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="filterForm.status && filterForm.status !== 'all'" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
                            Status: {{ filterForm.status }}
                            <button @click="filterForm.status = 'all'; applyFilters()" class="hover:text-red-600">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="filterForm.grade && filterForm.grade !== 'all'" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
                            Grade: {{ gradesList.find(g => g.id.toString() === filterForm.grade)?.name }}
                            <button @click="filterForm.grade = 'all'; applyFilters()" class="hover:text-red-600">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 dark:bg-sidebar-accent">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">From Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Applied To</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Notes</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-sidebar">
                            <tr v-for="(credit, index) in creditList" :key="credit.id"
                                :class="['hover:bg-gray-50 dark:hover:bg-sidebar-accent', { 'bg-muted/50': index % 2 === 0 }]">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    {{ credit.student?.first_name }} {{ credit.student?.last_name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ credit.student?.grade?.name || 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(credit.amount) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span :class="getStatusColor(credit.status)" class="rounded-full px-2 py-1 text-xs font-medium uppercase">
                                        {{ credit.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    Payment #{{ credit.from_payment_id }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span v-if="credit.applied_to_fee">
                                        {{ credit.applied_to_fee?.grade?.name }} - {{ credit.applied_to_fee?.term }}
                                    </span>
                                    <span v-else class="text-muted-foreground">Not applied</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ formatDate(credit.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="line-clamp-2">{{ credit.notes || '-' }}</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="creditList.length === 0" class="py-12 text-center">
                            <p class="text-muted-foreground">No credits found matching your filters.</p>
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
