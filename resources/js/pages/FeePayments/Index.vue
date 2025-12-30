<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import feePayments from '@/routes/fee-payments';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, computed, watch } from 'vue';
import { Filter, X } from 'lucide-vue-next';

const props = defineProps<{
    payments?: any[];
    students?: any[];
    fees?: any[];
    credits?: Record<string, any[]>;
    grades?: any[];
    terms?: string[];
    filters?: {
        grade?: string;
        term?: string;
        status?: string;
        student?: string;
        date_from?: string;
        date_to?: string;
        has_balance?: string;
        sort_balance?: string;
    };
}>();

const paymentList = props.payments || [];
const studentsList = props.students || [];
const feesList = props.fees || [];
const creditsData = props.credits || {};
const gradesList = props.grades || [];
const termsList = props.terms || [];

const openAddPayment = ref(false);
const openEditPayment = ref(false);
const selectedEditPaymentId = ref<number | null>(null);
const showFilters = ref(false);

// Filter form
const filterForm = useForm({
    grade: props.filters?.grade || 'all',
    term: props.filters?.term || 'all',
    status: props.filters?.status || 'all',
    student: props.filters?.student || 'all',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    has_balance: props.filters?.has_balance || 'all',
    sort_balance: props.filters?.sort_balance || 'all',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Fee Payments',
        href: feePayments.index.url,
    },
];

const paymentForm = useForm({
    student_id: '',
    fee_id: '',
    amount_paid: '',
    payment_date: new Date().toISOString().split('T')[0],
    status: 'pending',
});

const editForm = useForm({
    student_id: '',
    fee_id: '',
    amount_paid: '',
    payment_date: '',
    status: '',
});

const resetForm = () => {
    paymentForm.reset();
    paymentForm.payment_date = new Date().toISOString().split('T')[0];
};

const resetEditForm = () => {
    editForm.reset();
};

const handleCreate = () => {
    resetForm();
    openAddPayment.value = true;
};

const handleSubmitPayment = () => {
    paymentForm.post('/fee-payments', {
        onSuccess: () => {
            openAddPayment.value = false;
            resetForm();
        },
    });
};

const handleEdit = (paymentId: number) => {
    const payment = paymentList.find(p => p.id === paymentId);
    if (payment) {
        selectedEditPaymentId.value = payment.id;
        editForm.student_id = payment.student_id.toString();
        editForm.fee_id = payment.fee_id.toString();
        editForm.amount_paid = payment.amount_paid.toString();
        editForm.payment_date = payment.payment_date;
        editForm.status = payment.status;

        openEditPayment.value = true;
    }
};

const handleUpdatePayment = () => {
    if (selectedEditPaymentId.value) {
        editForm.put(`/fee-payments/${selectedEditPaymentId.value}`, {
            onSuccess: () => {
                openEditPayment.value = false;
                resetEditForm();
            },
        });
    }
};

const handleDelete = (paymentId: number) => {
    if (confirm('Are you sure you want to delete this payment?')) {
        router.delete(`/fee-payments/${paymentId}`);
    }
};

// Filter handlers
const applyFilters = () => {
    // Build query params, excluding 'all' values
    const params: Record<string, string> = {};

    if (filterForm.grade && filterForm.grade !== 'all') {
        params.grade = filterForm.grade;
    }
    if (filterForm.term && filterForm.term !== 'all') {
        params.term = filterForm.term;
    }
    if (filterForm.status && filterForm.status !== 'all') {
        params.status = filterForm.status;
    }
    if (filterForm.student && filterForm.student !== 'all') {
        params.student = filterForm.student;
    }
    if (filterForm.date_from) {
        params.date_from = filterForm.date_from;
    }
    if (filterForm.date_to) {
        params.date_to = filterForm.date_to;
    }
    if (filterForm.has_balance && filterForm.has_balance !== 'all') {
        params.has_balance = filterForm.has_balance;
    }
    if (filterForm.sort_balance && filterForm.sort_balance !== 'all') {
        params.sort_balance = filterForm.sort_balance;
    }

    // Use router.get instead of filterForm.get for better reactivity
    router.get('/fee-payments', params, {
        preserveState: false,
        preserveScroll: false,
        only: ['payments', 'filters'],
    });
};

// Update the clearFilters function:
const clearFilters = () => {
    filterForm.grade = 'all';
    filterForm.term = 'all';
    filterForm.status = 'all';
    filterForm.student = 'all';
    filterForm.date_from = '';
    filterForm.date_to = '';
    filterForm.has_balance = 'all';
    filterForm.sort_balance = 'all';

    // Use router.get to clear filters
    router.get('/fee-payments', {}, {
        preserveState: false,
        preserveScroll: false,
    });
};


// Update the hasActiveFilters computed:
const hasActiveFilters = computed(() => {
    return !!(
        (filterForm.grade && filterForm.grade !== 'all') ||
        (filterForm.term && filterForm.term !== 'all') ||
        (filterForm.status && filterForm.status !== 'all') ||
        (filterForm.student && filterForm.student !== 'all') ||
        filterForm.date_from ||
        filterForm.date_to ||
        (filterForm.has_balance && filterForm.has_balance !== 'all') ||
        (filterForm.sort_balance && filterForm.sort_balance !== 'all')
    );
});


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

// Get student name helper
const getStudentName = (studentId: string) => {
    const student = studentsList.find(s => s.id.toString() === studentId);
    return student ? `${student.first_name} ${student.last_name} - ${student.adm_no}` : '';
};

// Filter fees based on selected student's grade
const filteredFeesForAdd = computed(() => {
    if (!paymentForm.student_id) return feesList;

    const student = studentsList.find(s => s.id.toString() === paymentForm.student_id);
    if (!student || !student.grade_id) return feesList;

    return feesList.filter(f => f.grade_id === student.grade_id);
});

const filteredFeesForEdit = computed(() => {
    if (!editForm.student_id) return feesList;

    const student = studentsList.find(s => s.id.toString() === editForm.student_id);
    if (!student || !student.grade_id) return feesList;

    return feesList.filter(f => f.grade_id === student.grade_id);
});

// Watch for student changes and reset fee selection
watch(() => paymentForm.student_id, () => {
    paymentForm.fee_id = '';
});

watch(() => editForm.student_id, () => {
    editForm.fee_id = '';
});

// Get fee details helper
const getFeeDetails = (feeId: string) => {
    const fee = feesList.find(f => f.id.toString() === feeId);
    return fee ? `${fee.grade?.name} - ${fee.term} (${formatCurrency(fee.amount)})` : '';
};

// Get available credit for a student
const getStudentCredit = (studentId: string) => {
    const studentCredits = creditsData[studentId] || [];
    const totalCredit = studentCredits.reduce((sum, credit) => sum + parseFloat(credit.amount || 0), 0);
    return totalCredit;
};

// Calculate total credits
const totalCredits = Object.values(creditsData).flat().reduce((sum, credit) => sum + parseFloat(credit.amount || 0), 0);
</script>

<template>
    <Head title="Fee Payments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
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
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Available Credits</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(totalCredits) }}</div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white md:min-h-min dark:border-sidebar-border dark:bg-sidebar">
                <div class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Fee Payments</h2>
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
                                + Record Payment
                            </button>
                        </div>
                    </div>

                    <!-- Filter Panel -->
                    <div v-if="showFilters" class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-sidebar-accent">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold">Filter Payments</h3>
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

                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
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

                            <!-- Status Filter -->
                            <div class="grid gap-2">
                                <Label>Status</Label>
                                <Select v-model="filterForm.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
                                        <SelectItem value="partial">Partial</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

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

                            <!-- Date From -->
                            <div class="grid gap-2">
                                <Label for="date_from">Date From</Label>
                                <Input
                                    id="date_from"
                                    v-model="filterForm.date_from"
                                    type="date"
                                    class="w-full"
                                />
                            </div>

                            <!-- Date To -->
                            <div class="grid gap-2">
                                <Label for="date_to">Date To</Label>
                                <Input
                                    id="date_to"
                                    v-model="filterForm.date_to"
                                    type="date"
                                    class="w-full"
                                />
                            </div>

                            <!-- Has Balance Filter -->
                            <div class="grid gap-2">
                                <Label>Balance Status</Label>
                                <Select v-model="filterForm.has_balance">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All</SelectItem>
                                        <SelectItem value="yes">Has Balance</SelectItem>
                                        <SelectItem value="no">Fully Paid</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Sort by Balance -->
                            <div class="grid gap-2">
                                <Label>Sort by Balance</Label>
                                <Select v-model="filterForm.sort_balance">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Default" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Default (Latest First)</SelectItem>
                                        <SelectItem value="asc">Balance: Low to High</SelectItem>
                                        <SelectItem value="desc">Balance: High to Low</SelectItem>
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
                        <span v-if="filterForm.status && filterForm.status !== 'all'" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
        Status: {{ filterForm.status }}
        <button @click="filterForm.status = 'all'; applyFilters()" class="hover:text-red-600">
            <X class="h-3 w-3" />
        </button>
    </span>
                        <span v-if="filterForm.student && filterForm.student !== 'all'" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
        Student: {{ studentsList.find(s => s.id.toString() === filterForm.student)?.first_name }}
        <button @click="filterForm.student = 'all'; applyFilters()" class="hover:text-red-600">
            <X class="h-3 w-3" />
        </button>
    </span>
                        <span v-if="filterForm.date_from" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
        From: {{ filterForm.date_from }}
        <button @click="filterForm.date_from = ''; applyFilters()" class="hover:text-red-600">
            <X class="h-3 w-3" />
        </button>
    </span>
                        <span v-if="filterForm.date_to" class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs">
        To: {{ filterForm.date_to }}
        <button @click="filterForm.date_to = ''; applyFilters()" class="hover:text-red-600">
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
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Amount Paid</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Payment Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-sidebar">
                            <tr v-for="(payment, index) in paymentList" :key="payment.id"
                                :class="['hover:bg-gray-50 dark:hover:bg-sidebar-accent', { 'bg-muted/50': index % 2 === 0 }]">
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
                                    <span v-if="payment.balance > 0" class="font-semibold text-orange-600 dark:text-orange-400">
                                        {{ formatCurrency(payment.balance) }}
                                    </span>
                                    <span v-else class="font-semibold text-green-600 dark:text-green-400">
                                        Paid
                                    </span>
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
                                        <Button variant="ghost" size="sm" @click="handleEdit(payment.id)">
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="handleDelete(payment.id)">
                                            Delete
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="paymentList.length === 0" class="py-12 text-center">
                            <p class="text-muted-foreground">No payments found matching your filters.</p>
                            <Button v-if="hasActiveFilters" variant="outline" class="mt-4" @click="clearFilters">
                                Clear Filters
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Payment Modal -->
        <Dialog v-model:open="openAddPayment">
            <DialogContent
                class="sm:max-w-2xl"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <DialogHeader>
                    <DialogTitle>Record New Payment</DialogTitle>
                    <DialogDescription>
                        Enter the payment details below to record a new fee payment.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <!-- Student -->
                    <div class="grid gap-2">
                        <Label for="student_id">Student</Label>
                        <Select v-model="paymentForm.student_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a student" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="student in studentsList" :key="student.id" :value="student.id.toString()">
                                    {{ student.first_name }} {{ student.last_name }} - {{ student.adm_no }} ({{ student.grade?.name }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="paymentForm.errors.student_id" class="text-sm text-red-600">{{ paymentForm.errors.student_id }}</span>

                        <!-- Show available credit if exists -->
                        <div v-if="paymentForm.student_id && getStudentCredit(paymentForm.student_id) > 0"
                             class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                💰 Available Credit: {{ formatCurrency(getStudentCredit(paymentForm.student_id)) }}
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-400">
                                This credit can be applied to future fees
                            </p>
                        </div>
                    </div>

                    <!-- Fee -->
                    <div class="grid gap-2">
                        <Label for="fee_id">Fee</Label>
                        <Select v-model="paymentForm.fee_id" :disabled="!paymentForm.student_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a fee" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="fee in filteredFeesForAdd" :key="fee.id" :value="fee.id.toString()">
                                    {{ fee.term }} ({{ formatCurrency(fee.amount) }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="!paymentForm.student_id" class="text-xs text-muted-foreground">Select a student first</span>
                        <span v-if="paymentForm.errors.fee_id" class="text-sm text-red-600">{{ paymentForm.errors.fee_id }}</span>
                    </div>

                    <!-- Amount and Date -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="amount_paid">Amount Paid</Label>
                            <Input
                                id="amount_paid"
                                v-model="paymentForm.amount_paid"
                                type="number"
                                step="0.01"
                                placeholder="15000.00"
                            />
                            <span v-if="paymentForm.errors.amount_paid" class="text-sm text-red-600">{{ paymentForm.errors.amount_paid }}</span>
                        </div>
                        <div class="grid gap-2">
                            <Label for="payment_date">Payment Date</Label>
                            <Input
                                id="payment_date"
                                v-model="paymentForm.payment_date"
                                type="date"
                            />
                            <span v-if="paymentForm.errors.payment_date" class="text-sm text-red-600">{{ paymentForm.errors.payment_date }}</span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <Select v-model="paymentForm.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="partial">Partial</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="paymentForm.errors.status" class="text-sm text-red-600">{{ paymentForm.errors.status }}</span>
                    </div>
                </div>

                <DialogFooter class="pt-4">
                    <DialogClose as-child>
                        <Button variant="outline" @click="resetForm">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button @click="handleSubmitPayment" :disabled="paymentForm.processing">
                        {{ paymentForm.processing ? 'Recording...' : 'Record Payment' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Payment Modal -->
        <Dialog v-model:open="openEditPayment">
            <DialogContent
                class="sm:max-w-2xl"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <DialogHeader>
                    <DialogTitle>Edit Payment</DialogTitle>
                    <DialogDescription>
                        Update the payment details below.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <!-- Student -->
                    <div class="grid gap-2">
                        <Label for="edit_student_id">Student</Label>
                        <Select v-model="editForm.student_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a student" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="student in studentsList" :key="student.id" :value="student.id.toString()">
                                    {{ student.first_name }} {{ student.last_name }} - {{ student.adm_no }} ({{ student.grade?.name }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="editForm.errors.student_id" class="text-sm text-red-600">{{ editForm.errors.student_id }}</span>

                        <!-- Show available credit if exists -->
                        <div v-if="editForm.student_id && getStudentCredit(editForm.student_id) > 0"
                             class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                💰 Available Credit: {{ formatCurrency(getStudentCredit(editForm.student_id)) }}
                            </p>
                        </div>
                    </div>

                    <!-- Fee -->
                    <div class="grid gap-2">
                        <Label for="edit_fee_id">Fee</Label>
                        <Select v-model="editForm.fee_id" :disabled="!editForm.student_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a fee" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="fee in filteredFeesForEdit" :key="fee.id" :value="fee.id.toString()">
                                    {{ fee.term }} ({{ formatCurrency(fee.amount) }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="!editForm.student_id" class="text-xs text-muted-foreground">Select a student first</span>
                        <span v-if="editForm.errors.fee_id" class="text-sm text-red-600">{{ editForm.errors.fee_id }}</span>
                    </div>

                    <!-- Amount and Date -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="edit_amount_paid">Amount Paid</Label>
                            <Input
                                id="edit_amount_paid"
                                v-model="editForm.amount_paid"
                                type="number"
                                step="0.01"
                                placeholder="15000.00"
                            />
                            <span v-if="editForm.errors.amount_paid" class="text-sm text-red-600">{{ editForm.errors.amount_paid }}</span>
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit_payment_date">Payment Date</Label>
                            <Input
                                id="edit_payment_date"
                                v-model="editForm.payment_date"
                                type="date"
                            />
                            <span v-if="editForm.errors.payment_date" class="text-sm text-red-600">{{ editForm.errors.payment_date }}</span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="grid gap-2">
                        <Label for="edit_status">Status</Label>
                        <Select v-model="editForm.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="partial">Partial</SelectItem>
                                <SelectItem value="paid">Paid</SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="editForm.errors.status" class="text-sm text-red-600">{{ editForm.errors.status }}</span>
                    </div>
                </div>

                <DialogFooter class="pt-4">
                    <DialogClose as-child>
                        <Button variant="outline" @click="resetEditForm">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button @click="handleUpdatePayment" :disabled="editForm.processing">
                        {{ editForm.processing ? 'Updating...' : 'Update Payment' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
