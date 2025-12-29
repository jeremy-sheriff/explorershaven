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
import { ref, computed } from 'vue';

const props = defineProps<{
    payments?: any[];
    students?: any[];
    fees?: any[];
}>();

const paymentList = props.payments || [];
const studentsList = props.students || [];
const feesList = props.fees || [];

const openAddPayment = ref(false);
const openEditPayment = ref(false);
const selectedEditPaymentId = ref<number | null>(null);

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

// Get fee details helper
const getFeeDetails = (feeId: string) => {
    const fee = feesList.find(f => f.id.toString() === feeId);
    return fee ? `${fee.grade?.name} - ${fee.term} (${formatCurrency(fee.amount)})` : '';
};
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
                            @click="handleCreate"
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
                                    {{ student.first_name }} {{ student.last_name }} - {{ student.adm_no }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="paymentForm.errors.student_id" class="text-sm text-red-600">{{ paymentForm.errors.student_id }}</span>
                    </div>

                    <!-- Fee -->
                    <div class="grid gap-2">
                        <Label for="fee_id">Fee</Label>
                        <Select v-model="paymentForm.fee_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a fee" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="fee in feesList" :key="fee.id" :value="fee.id.toString()">
                                    {{ fee.grade?.name }} - {{ fee.term }} ({{ formatCurrency(fee.amount) }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
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
                                    {{ student.first_name }} {{ student.last_name }} - {{ student.adm_no }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="editForm.errors.student_id" class="text-sm text-red-600">{{ editForm.errors.student_id }}</span>
                    </div>

                    <!-- Fee -->
                    <div class="grid gap-2">
                        <Label for="edit_fee_id">Fee</Label>
                        <Select v-model="editForm.fee_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a fee" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="fee in feesList" :key="fee.id" :value="fee.id.toString()">
                                    {{ fee.grade?.name }} - {{ fee.term }} ({{ formatCurrency(fee.amount) }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
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
