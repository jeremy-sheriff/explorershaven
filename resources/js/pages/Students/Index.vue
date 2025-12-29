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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {computed, ref} from "vue";

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
const selectedEditStudentId = ref<number | null>(null)

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
    grade_id: '',
    guardian_first_name: '',
    guardian_last_name: '',
    guardian_phone: '',
})

const editForm = useForm({
    adm_no: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    grade_id: '',
    guardian_first_name: '',
    guardian_last_name: '',
    guardian_phone: '',
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

const handleView = (studentId: number) => {
    alert('View student: ' + studentId);
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
                                <TableHead>Adm No</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Grade</TableHead>
                                <TableHead>Guardian</TableHead>
                                <TableHead>Phone</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="student in studentsList" :key="student.id">
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
                                        <button
                                            @click="handleView(student.id)"
                                            class="rounded px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950"
                                        >
                                            View
                                        </button>

                                        <Button @click="handleEdit(student.id)">
                                            Edit
                                        </Button>

                                        <Button variant="destructive" @click="handleDelete(student.id)">
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

                    <!-- Grade -->
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
    </AppLayout>
</template>
