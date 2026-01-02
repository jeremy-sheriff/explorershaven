<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
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
const openAddFee = ref(false);
const openEditFee = ref(false);
const selectedEditFeeId = ref<number | null>(null);

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

// Add fee form
const feeForm = useForm({
    grade_id: '',
    amount: '',
    term: '',
    due_date: '',
});

// Edit fee form
const editForm = useForm({
    grade_id: '',
    amount: '',
    term: '',
    due_date: '',
});

const resetForm = () => {
    feeForm.reset();
};

const resetEditForm = () => {
    editForm.reset();
};

const handleCreate = () => {
    resetForm();
    openAddFee.value = true;
};

const handleSubmitFee = () => {
    feeForm.post('/fees', {
        onSuccess: () => {
            openAddFee.value = false;
            resetForm();
        },
    });
};

const handleEdit = (feeId: number) => {
    const fee = feeList.find(f => f.id === feeId);
    if (fee) {
        selectedEditFeeId.value = fee.id;
        editForm.grade_id = fee.grade_id.toString();
        editForm.amount = fee.amount.toString();
        editForm.term = fee.term;
        editForm.due_date = fee.due_date;

        openEditFee.value = true;
    }
};

const handleUpdateFee = () => {
    if (selectedEditFeeId.value) {
        editForm.put(`/fees/${selectedEditFeeId.value}`, {
            onSuccess: () => {
                openEditFee.value = false;
                resetEditForm();
            },
        });
    }
};

const handleDelete = (feeId: number) => {
    if (confirm('Are you sure you want to delete this fee?')) {
        router.delete(`/fees/${feeId}`);
    }
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
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Due Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-sidebar">
                            <tr v-for="(fee, index) in feeList" :key="fee.id" class="hover:bg-gray-50 dark:hover:bg-sidebar-accent">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    {{ index + 1 }}
                                </td>
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
                                        <Button variant="ghost" size="sm" @click="handleEdit(fee.id)">
                                            Edit
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="handleDelete(fee.id)">
                                            Delete
                                        </Button>
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


        <!-- Add Fee Modal -->
        <Dialog v-model:open="openAddFee">
            <DialogContent
                class="sm:max-w-2xl"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <DialogHeader>
                    <DialogTitle>Add New Fee</DialogTitle>
                    <DialogDescription>
                        Enter the fee details below.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <!-- Grade -->
                    <div class="grid gap-2">
                        <Label for="grade_id">Grade</Label>
                        <Select v-model="feeForm.grade_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a grade" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="grade in gradesList" :key="grade.id" :value="grade.id.toString()">
                                    {{ grade.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="feeForm.errors.grade_id" class="text-sm text-red-600">{{ feeForm.errors.grade_id }}</span>
                    </div>

                    <!-- Term -->
                    <div class="grid gap-2">
                        <Label for="term">Term</Label>
                        <Input
                            id="term"
                            v-model="feeForm.term"
                            type="text"
                            placeholder="e.g., Term 1 2025"
                        />
                        <span v-if="feeForm.errors.term" class="text-sm text-red-600">{{ feeForm.errors.term }}</span>
                    </div>

                    <!-- Amount and Due Date -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="amount">Amount (KES)</Label>
                            <Input
                                id="amount"
                                v-model="feeForm.amount"
                                type="number"
                                step="0.01"
                                placeholder="15000.00"
                            />
                            <span v-if="feeForm.errors.amount" class="text-sm text-red-600">{{ feeForm.errors.amount }}</span>
                        </div>
                        <div class="grid gap-2">
                            <Label for="due_date">Due Date</Label>
                            <Input
                                id="due_date"
                                v-model="feeForm.due_date"
                                type="date"
                            />
                            <span v-if="feeForm.errors.due_date" class="text-sm text-red-600">{{ feeForm.errors.due_date }}</span>
                        </div>
                    </div>
                </div>

                <DialogFooter class="pt-4">
                    <DialogClose as-child>
                        <Button variant="outline" @click="resetForm">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        @click="handleSubmitFee"
                        :disabled="feeForm.processing"
                    >
                        {{ feeForm.processing ? 'Creating...' : 'Create Fee' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Fee Modal -->
        <Dialog v-model:open="openEditFee">
            <DialogContent
                class="sm:max-w-2xl"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <DialogHeader>
                    <DialogTitle>Edit Fee</DialogTitle>
                    <DialogDescription>
                        Update the fee details below.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <!-- Grade -->
                    <div class="grid gap-2">
                        <Label for="edit_grade_id">Grade</Label>
                        <Select v-model="editForm.grade_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a grade" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="grade in gradesList" :key="grade.id" :value="grade.id.toString()">
                                    {{ grade.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="editForm.errors.grade_id" class="text-sm text-red-600">{{ editForm.errors.grade_id }}</span>
                    </div>

                    <!-- Term -->
                    <div class="grid gap-2">
                        <Label for="edit_term">Term</Label>
                        <Input
                            id="edit_term"
                            v-model="editForm.term"
                            type="text"
                            placeholder="e.g., Term 1 2025"
                        />
                        <span v-if="editForm.errors.term" class="text-sm text-red-600">{{ editForm.errors.term }}</span>
                    </div>

                    <!-- Amount and Due Date -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="edit_amount">Amount (KES)</Label>
                            <Input
                                id="edit_amount"
                                v-model="editForm.amount"
                                type="number"
                                step="0.01"
                                placeholder="15000.00"
                            />
                            <span v-if="editForm.errors.amount" class="text-sm text-red-600">{{ editForm.errors.amount }}</span>
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit_due_date">Due Date</Label>
                            <Input
                                id="edit_due_date"
                                v-model="editForm.due_date"
                                type="date"
                            />
                            <span v-if="editForm.errors.due_date" class="text-sm text-red-600">{{ editForm.errors.due_date }}</span>
                        </div>
                    </div>
                </div>

                <DialogFooter class="pt-4">
                    <DialogClose as-child>
                        <Button variant="outline" @click="resetEditForm">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        @click="handleUpdateFee"
                        :disabled="editForm.processing"
                    >
                        {{ editForm.processing ? 'Updating...' : 'Update Fee' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
