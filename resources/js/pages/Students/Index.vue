<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import students from '@/routes/students';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { useMediaQuery } from '@vueuse/core'
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger,
} from '@/components/ui/drawer'

import {Checkbox} from "@/components/ui/checkbox";
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { computed, ref } from "vue";
import axios from 'axios';

const isDesktop = useMediaQuery('(min-width: 640px)')

const Modal = computed(() => ({
    Root: isDesktop.value ? Dialog : Drawer,
    Trigger: isDesktop.value ? DialogTrigger : DrawerTrigger,
    Content: isDesktop.value ? DialogContent : DrawerContent,
    Header: isDesktop.value ? DialogHeader : DrawerHeader,
    Title: isDesktop.value ? DialogTitle : DrawerTitle,
    Description: isDesktop.value ? DialogDescription : DrawerDescription,
    Footer: isDesktop.value ? DialogFooter : DrawerFooter,
    Close: isDesktop.value ? DialogClose : DrawerClose,
}))

const openAddStudent = ref(false)
const openEditStudent = ref(false)
const openViewStudent = ref(false)
const selectedEditStudentId = ref<number | null>(null)
const selectedViewStudentId = ref<number | null>(null)
const studentDetails = ref<any>(null)
const loadingDetails = ref(false)

const props = defineProps<{
    students?: any[];
    grades?: any[];
}>();

const studentsList = props.students || [];
const gradesList = props.grades || [];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Students',
        href: students.index().url,
    },
];

const studentForm = useForm({
    adm_no: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    gender: '',
    grade_id: '',
    guardian_first_name: '',
    guardian_last_name: '',
    guardian_phone: '',
    guardian_gender: '',  // ADD THIS
})

const editForm = useForm({
    adm_no: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    gender: '',
    grade_id: '',
    guardian_first_name: '',
    guardian_last_name: '',
    guardian_phone: '',
    guardian_gender: '',  // ADD THIS
})

const resetForm = () => {
    studentForm.reset()
}

const resetEditForm = () => {
    editForm.reset()
}

const handleEdit = (studentId: number) => {
    const student = studentsList.find(s => s.id === studentId)
    if (student) {
        selectedEditStudentId.value = student.id
        editForm.adm_no = student.adm_no
        editForm.first_name = student.first_name
        editForm.middle_name = student.middle_name || ''
        editForm.last_name = student.last_name
        editForm.grade_id = student.grade?.id?.toString() || ''
        editForm.guardian_first_name = student.guardian?.first_name || ''
        editForm.guardian_last_name = student.guardian?.last_name || ''
        editForm.guardian_phone = student.guardian?.phone_number || ''

        openEditStudent.value = true
    }
};

const handleView = async (studentId: number) => {
    selectedViewStudentId.value = studentId
    loadingDetails.value = true
    openViewStudent.value = true

    try {
        const response = await axios.get(`/students/${studentId}`)
        studentDetails.value = response.data
    } catch (error) {
        console.error('Error fetching student details:', error)
        alert('Failed to load student details')
        openViewStudent.value = false
    } finally {
        loadingDetails.value = false
    }
};

const handleDelete = (studentId: number) => {
    if (confirm('Are you sure you want to delete this student?')) {
        router.delete(`/students/${studentId}`)
    }
};

const handleCreate = () => {
    resetForm()
    openAddStudent.value = true
};

const handleSubmitStudent = () => {
    studentForm.post('/students', {
        onSuccess: () => {
            openAddStudent.value = false
            resetForm()
        }
    })
}

const handleUpdateStudent = () => {
    if (selectedEditStudentId.value) {
        editForm.put(`/students/${selectedEditStudentId.value}`, {
            onSuccess: () => {
                openEditStudent.value = false
                resetEditForm()
            }
        })
    }
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>


<template>
    <Head title="Students" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Total Students</div>
                    <div class="text-2xl font-bold">{{ studentsList.length }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Active Grades</div>
                    <div class="text-2xl font-bold">{{ gradesList.length }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="text-sm font-medium text-muted-foreground">Guardians</div>
                    <div class="text-2xl font-bold">{{ new Set(studentsList.map(s => s.guardian?.id)).size }}</div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white md:min-h-min dark:border-sidebar-border dark:bg-sidebar">
                <div class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-semibold">Students</h2>
                        <button
                            @click="handleCreate"
                            class="rounded-lg bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-200"
                        >
                            + Add Student
                        </button>
                    </div>

                    <Table>
                        <TableCaption>A list of all students in the system.</TableCaption>
                        <TableHeader>
                            <TableRow>
                                <TableHead>#</TableHead>
                                <TableHead>Adm No</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Grade</TableHead>
                                <TableHead>Guardian</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(student, index) in studentsList" :key="student.id" :class="['hover:bg-gray-50 dark:hover:bg-sidebar-accent', { 'bg-muted/50': index % 2 === 0 }]">
                                <TableCell class="font-medium">
                                    {{ index + 1 }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ student.adm_no }}
                                </TableCell>
                                <TableCell>
                                    {{ student.first_name }} {{ student.middle_name }} {{ student.last_name }}
                                </TableCell>
                                <TableCell>
                                    {{ student.grade?.name || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    {{ student.guardian?.first_name || '' }} {{ student.guardian?.last_name || '' }}
                                </TableCell>
                                <TableCell>
                                    {{ student.guardian?.phone_number || 'N/A' }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button
                                            variant="default"
                                            size="sm"
                                            @click="handleView(student.id)"
                                        >
                                            View
                                        </Button>

                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="handleEdit(student.id)"
                                        >
                                            Edit
                                        </Button>

                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="handleDelete(student.id)"
                                        >
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <!-- Add Student Modal -->
        <component :is="Modal.Root" v-model:open="openAddStudent" :modal="true">
            <component
                :is="Modal.Content"
                class="sm:max-w-2xl"
                :class="[{ 'px-2 pb-8 *:px-4': !isDesktop }]"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <component :is="Modal.Header">
                    <component :is="Modal.Title">
                        Add New Student
                    </component>
                    <component :is="Modal.Description">
                        Fill in the student information below to add a new student to the system.
                    </component>
                </component>

                <div class="grid gap-4 py-4">
                    <!-- Admission Number -->
                    <div class="grid gap-2">
                        <Label for="adm_no">
                            Admission Number
                        </Label>
                        <Input
                            id="adm_no"
                            v-model="studentForm.adm_no"
                            placeholder="e.g., STU009"
                        />
                        <span v-if="studentForm.errors.adm_no" class="text-sm text-red-600">{{ studentForm.errors.adm_no }}</span>
                    </div>

                    <!-- Student Names -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="first_name">
                                First Name
                            </Label>
                            <Input
                                id="first_name"
                                v-model="studentForm.first_name"
                                placeholder="John"
                            />
                            <span v-if="studentForm.errors.first_name" class="text-sm text-red-600">{{ studentForm.errors.first_name }}</span>
                        </div>
                        <div class="grid gap-2">
                            <Label for="middle_name">
                                Middle Name
                            </Label>
                            <Input
                                id="middle_name"
                                v-model="studentForm.middle_name"
                                placeholder="Optional"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="last_name">
                                Last Name
                            </Label>
                            <Input
                                id="last_name"
                                v-model="studentForm.last_name"
                                placeholder="Doe"
                            />
                            <span v-if="studentForm.errors.last_name" class="text-sm text-red-600">{{ studentForm.errors.last_name }}</span>
                        </div>
                    </div>

                    <!-- Gender and Grade -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="gender">
                                Gender
                            </Label>
                            <Select v-model="studentForm.gender">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select gender" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="male">Male</SelectItem>
                                    <SelectItem value="female">Female</SelectItem>
                                </SelectContent>
                            </Select>
                            <span v-if="studentForm.errors.gender" class="text-sm text-red-600">{{ studentForm.errors.gender }}</span>
                        </div>

                        <div class="grid gap-2">
                            <Label for="grade_id">
                                Grade
                            </Label>
                            <Select v-model="studentForm.grade_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a grade" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="grade in gradesList" :key="grade.id" :value="grade.id.toString()">
                                        {{ grade.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <span v-if="studentForm.errors.grade_id" class="text-sm text-red-600">{{ studentForm.errors.grade_id }}</span>
                        </div>
                    </div>

                    <!-- Guardian Information -->
                    <div class="border-t pt-4">
                        <h3 class="mb-3 text-sm font-semibold">Guardian Information</h3>
                        <div class="grid gap-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="guardian_first_name">
                                        Guardian First Name
                                    </Label>
                                    <Input
                                        id="guardian_first_name"
                                        v-model="studentForm.guardian_first_name"
                                        placeholder="Jane"
                                    />
                                    <span v-if="studentForm.errors.guardian_first_name" class="text-sm text-red-600">{{ studentForm.errors.guardian_first_name }}</span>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="guardian_last_name">
                                        Guardian Last Name
                                    </Label>
                                    <Input
                                        id="guardian_last_name"
                                        v-model="studentForm.guardian_last_name"
                                        placeholder="Doe"
                                    />
                                    <span v-if="studentForm.errors.guardian_last_name" class="text-sm text-red-600">{{ studentForm.errors.guardian_last_name }}</span>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="guardian_gender">
                                        Guardian Gender
                                    </Label>
                                    <Select v-model="studentForm.guardian_gender">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select gender" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="male">Male</SelectItem>
                                            <SelectItem value="female">Female</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <span v-if="studentForm.errors.guardian_gender" class="text-sm text-red-600">{{ studentForm.errors.guardian_gender }}</span>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="guardian_phone">
                                        Guardian Phone Number
                                    </Label>
                                    <Input
                                        id="guardian_phone"
                                        v-model="studentForm.guardian_phone"
                                        placeholder="0712345678"
                                        type="tel"
                                    />
                                    <span v-if="studentForm.errors.guardian_phone" class="text-sm text-red-600">{{ studentForm.errors.guardian_phone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <component :is="Modal.Footer" class="pt-4">
                    <component :is="Modal.Close" as-child>
                        <Button variant="outline" @click="resetForm">
                            Cancel
                        </Button>
                    </component>
                    <Button @click="handleSubmitStudent" :disabled="studentForm.processing">
                        {{ studentForm.processing ? 'Adding...' : 'Add Student' }}
                    </Button>
                </component>
            </component>
        </component>

        <!-- Edit Student Modal -->
        <component :is="Modal.Root" v-model:open="openEditStudent" :modal="true">
            <component
                :is="Modal.Content"
                class="sm:max-w-2xl"
                :class="[{ 'px-2 pb-8 *:px-4': !isDesktop }]"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <component :is="Modal.Header">
                    <component :is="Modal.Title">
                        Edit Student
                    </component>
                    <component :is="Modal.Description">
                        Update the student information below.
                    </component>
                </component>

                <div class="grid gap-4 py-4">
                    <!-- Admission Number -->
                    <div class="grid gap-2">
                        <Label for="edit_adm_no">
                            Admission Number
                        </Label>
                        <Input
                            id="edit_adm_no"
                            v-model="editForm.adm_no"
                            placeholder="e.g., STU009"
                        />
                        <span v-if="editForm.errors.adm_no" class="text-sm text-red-600">{{ editForm.errors.adm_no }}</span>
                    </div>

                    <!-- Student Names -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="edit_first_name">
                                First Name
                            </Label>
                            <Input
                                id="edit_first_name"
                                v-model="editForm.first_name"
                                placeholder="John"
                            />
                            <span v-if="editForm.errors.first_name" class="text-sm text-red-600">{{ editForm.errors.first_name }}</span>
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit_middle_name">
                                Middle Name
                            </Label>
                            <Input
                                id="edit_middle_name"
                                v-model="editForm.middle_name"
                                placeholder="Optional"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit_last_name">
                                Last Name
                            </Label>
                            <Input
                                id="edit_last_name"
                                v-model="editForm.last_name"
                                placeholder="Doe"
                            />
                            <span v-if="editForm.errors.last_name" class="text-sm text-red-600">{{ editForm.errors.last_name }}</span>
                        </div>
                    </div>

                    <!-- Grade -->
                    <div class="grid gap-2">
                        <Label for="edit_grade_id">
                            Grade
                        </Label>
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

                    <!-- Guardian Information -->
                    <div class="border-t pt-4">
                        <h3 class="mb-3 text-sm font-semibold">Guardian Information</h3>
                        <div class="grid gap-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="edit_guardian_first_name">
                                        Guardian First Name
                                    </Label>
                                    <Input
                                        id="edit_guardian_first_name"
                                        v-model="editForm.guardian_first_name"
                                        placeholder="Jane"
                                    />
                                    <span v-if="editForm.errors.guardian_first_name" class="text-sm text-red-600">{{ editForm.errors.guardian_first_name }}</span>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="edit_guardian_last_name">
                                        Guardian Last Name
                                    </Label>
                                    <Input
                                        id="edit_guardian_last_name"
                                        v-model="editForm.guardian_last_name"
                                        placeholder="Doe"
                                    />
                                    <span v-if="editForm.errors.guardian_last_name" class="text-sm text-red-600">{{ editForm.errors.guardian_last_name }}</span>
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="edit_guardian_phone">
                                    Guardian Phone Number
                                </Label>
                                <Input
                                    id="edit_guardian_phone"
                                    v-model="editForm.guardian_phone"
                                    placeholder="0712345678"
                                    type="tel"
                                />
                                <span v-if="editForm.errors.guardian_phone" class="text-sm text-red-600">{{ editForm.errors.guardian_phone }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <component :is="Modal.Footer" class="pt-4">
                    <component :is="Modal.Close" as-child>
                        <Button variant="outline" @click="resetEditForm">
                            Cancel
                        </Button>
                    </component>
                    <Button @click="handleUpdateStudent" :disabled="editForm.processing">
                        {{ editForm.processing ? 'Updating...' : 'Update Student' }}
                    </Button>
                </component>
            </component>
        </component>

        <!-- View Student Details Modal -->
        <component :is="Modal.Root" v-model:open="openViewStudent" :modal="true">
            <component
                :is="Modal.Content"
                class="sm:max-w-4xl max-h-[90vh] overflow-y-auto"
                :class="[{ 'px-2 pb-8 *:px-4': !isDesktop }]"
            >
                <component :is="Modal.Header">
                    <component :is="Modal.Title">
                        Student Details
                    </component>
                    <component :is="Modal.Description">
                        Complete financial and personal information
                    </component>
                </component>

                <div v-if="loadingDetails" class="py-12 text-center">
                    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
                    <p class="mt-4 text-sm text-muted-foreground">Loading student details...</p>
                </div>

                <div v-else-if="studentDetails" class="space-y-6 py-4">
                    <!-- Student Information -->
                    <div class="rounded-lg border p-4">
                        <h3 class="mb-3 font-semibold">Student Information</h3>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-sm text-muted-foreground">Admission Number</p>
                                <p class="font-medium">{{ studentDetails.student.adm_no }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Full Name</p>
                                <p class="font-medium">
                                    {{ studentDetails.student.first_name }}
                                    {{ studentDetails.student.middle_name }}
                                    {{ studentDetails.student.last_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Grade</p>
                                <p class="font-medium">{{ studentDetails.student.grade?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Guardian</p>
                                <p class="font-medium">
                                    {{ studentDetails.student.guardian?.first_name }}
                                    {{ studentDetails.student.guardian?.last_name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ studentDetails.student.guardian?.phone_number }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-950">
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Paid</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(studentDetails.summary.total_paid) }}</p>
                        </div>
                        <div class="rounded-lg border bg-red-50 p-4 dark:bg-red-950">
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">Total Balance</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(studentDetails.summary.total_balance) }}</p>
                        </div>
                        <div class="rounded-lg border bg-green-50 p-4 dark:bg-green-950">
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Available Credits</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(studentDetails.summary.available_credits) }}</p>
                        </div>
                        <div class="rounded-lg border bg-purple-50 p-4 dark:bg-purple-950">
                            <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Current Term Balance</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(studentDetails.summary.current_term_balance) }}</p>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="rounded-lg border">
                        <div class="border-b p-4">
                            <h3 class="font-semibold">Payment History</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Fee Term</TableHead>
                                        <TableHead>Amount Paid</TableHead>
                                        <TableHead>Balance</TableHead>
                                        <TableHead>Payment Method</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="studentDetails.payments.length === 0">
                                        <TableCell colspan="5" class="text-center text-muted-foreground">
                                            No payments recorded
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-for="payment in studentDetails.payments" :key="payment.id">
                                        <TableCell>{{ formatDate(payment.created_at) }}</TableCell>
                                        <TableCell>{{ payment.fee?.term || 'N/A' }}</TableCell>
                                        <TableCell class="font-medium text-green-600">
                                            {{ formatCurrency(payment.amount_paid) }}
                                        </TableCell>
                                        <TableCell class="font-medium text-red-600">
                                            {{ formatCurrency(payment.balance) }}
                                        </TableCell>
                                        <TableCell>{{ payment.payment_method || 'N/A' }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>

                    <!-- Fee Credits -->
                    <div class="rounded-lg border">
                        <div class="border-b p-4">
                            <h3 class="font-semibold">Fee Credits</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Source</TableHead>
                                        <TableHead>Applied To</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-if="studentDetails.credits.length === 0">
                                        <TableCell colspan="5" class="text-center text-muted-foreground">
                                            No credits available
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-for="credit in studentDetails.credits" :key="credit.id">
                                        <TableCell>{{ formatDate(credit.created_at) }}</TableCell>
                                        <TableCell class="font-medium">
                                            {{ formatCurrency(credit.amount) }}
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': credit.status === 'available',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200': credit.status === 'applied'
                                                }"
                                            >
                                                {{ credit.status }}
                                            </span>
                                        </TableCell>
                                        <TableCell>
                                            {{ credit.from_payment ? `Payment #${credit.from_payment.id}` : 'N/A' }}
                                        </TableCell>
                                        <TableCell>
                                            {{ credit.applied_to_fee ? credit.applied_to_fee.term : 'Not Applied' }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>

                <component :is="Modal.Footer" class="pt-4">
                    <component :is="Modal.Close" as-child>
                        <Button variant="outline">
                            Close
                        </Button>
                    </component>
                </component>
            </component>
        </component>
    </AppLayout>
</template>
