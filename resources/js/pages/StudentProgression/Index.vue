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
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ref, computed } from 'vue';
import { GraduationCap, Users, TrendingUp, AlertCircle, Settings } from 'lucide-vue-next';

const props = defineProps<{
    grades?: any[];
    currentYear?: string;
    stats?: any;
    maxGradeLevel?: number;
    autoGraduateEnabled?: boolean;
}>();

const gradesList = props.grades || [];
const currentYear = props.currentYear || '2025';
const stats = props.stats || {};

const openPromoteGrade = ref(false);
const openPromoteAll = ref(false);
const openStartNewYear = ref(false);
const openSettings = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Student Progression',
        href: '#',
    },
];

// Promote Grade Form
const promoteGradeForm = useForm({
    from_grade_id: '',
    to_grade_id: '',
    notes: '',
});

// Promote All Form
const promoteAllForm = useForm({
    notes: '',
});

// Start New Year Form
const newYearForm = useForm({
    new_year: '',
    promote_students: true,
});

// Settings Form
const settingsForm = useForm({
    max_grade_level: props.maxGradeLevel || 12,
    auto_graduate_enabled: props.autoGraduateEnabled || true,
});

const resetPromoteGradeForm = () => {
    promoteGradeForm.reset();
};

const resetPromoteAllForm = () => {
    promoteAllForm.reset();
};

const resetNewYearForm = () => {
    newYearForm.reset();
    newYearForm.new_year = '';
    newYearForm.promote_students = true;
};

const resetSettingsForm = () => {
    settingsForm.max_grade_level = props.maxGradeLevel || 12;
    settingsForm.auto_graduate_enabled = props.autoGraduateEnabled || true;
};

const handlePromoteGrade = () => {
    promoteGradeForm.post('/student-progression/promote-grade', {
        onSuccess: () => {
            openPromoteGrade.value = false;
            resetPromoteGradeForm();
        },
    });
};

const handlePromoteAll = () => {
    if (!confirm('This will promote ALL active students to the next grade. Are you sure?')) {
        return;
    }

    promoteAllForm.post('/student-progression/promote-all', {
        onSuccess: () => {
            openPromoteAll.value = false;
            resetPromoteAllForm();
        },
    });
};

const handleStartNewYear = () => {
    if (!confirm(`Start academic year ${newYearForm.new_year}? This will update all students.`)) {
        return;
    }

    newYearForm.post('/student-progression/start-new-year', {
        onSuccess: () => {
            openStartNewYear.value = false;
            resetNewYearForm();
        },
    });
};

const handleUpdateSettings = () => {
    settingsForm.put('/student-progression/settings', {
        onSuccess: () => {
            openSettings.value = false;
        },
    });
};

const filteredToGrades = computed(() => {
    if (!promoteGradeForm.from_grade_id) return gradesList;

    const fromGradeId = parseInt(promoteGradeForm.from_grade_id);
    return gradesList.filter(g => g.id > fromGradeId);
});

const getNextYear = () => {
    return (parseInt(currentYear) + 1).toString();
};

const totalActiveStudents = computed(() => {
    return gradesList.reduce((sum, grade) => sum + (grade.students_count || 0), 0);
});
</script>

<template>
    <Head title="Student Progression" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-blue-600" />
                        <div class="text-sm font-medium text-muted-foreground">Active Students</div>
                    </div>
                    <div class="text-2xl font-bold">{{ totalActiveStudents }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="flex items-center gap-2">
                        <TrendingUp class="h-5 w-5 text-green-600" />
                        <div class="text-sm font-medium text-muted-foreground">Total Promotions</div>
                    </div>
                    <div class="text-2xl font-bold">{{ stats.total_promotions || 0 }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-purple-600" />
                        <div class="text-sm font-medium text-muted-foreground">Graduated</div>
                    </div>
                    <div class="text-2xl font-bold">{{ stats.total_graduated || 0 }}</div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                    <div class="flex items-center gap-2">
                        <AlertCircle class="h-5 w-5 text-orange-600" />
                        <div class="text-sm font-medium text-muted-foreground">Repetitions</div>
                    </div>
                    <div class="text-2xl font-bold">{{ stats.total_repetitions || 0 }}</div>
                </div>
            </div>

            <!-- Current Year Info -->
            <Alert class="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950">
                <AlertCircle class="h-4 w-4" />
                <AlertTitle>Current Academic Year: {{ currentYear }}</AlertTitle>
                <AlertDescription>
                    Manage student progressions and start new academic years from this page.
                </AlertDescription>
            </Alert>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <Button @click="openPromoteGrade = true" class="gap-2">
                    <TrendingUp class="h-4 w-4" />
                    Promote by Grade
                </Button>
                <Button @click="openPromoteAll = true" variant="default" class="gap-2">
                    <Users class="h-4 w-4" />
                    Promote All Students
                </Button>
                <Button @click="openStartNewYear = true" variant="outline" class="gap-2">
                    <GraduationCap class="h-4 w-4" />
                    Start New Academic Year
                </Button>
                <Button @click="openSettings = true" variant="outline" class="gap-2">
                    <Settings class="h-4 w-4" />
                    Settings
                </Button>
            </div>

            <!-- Grades Overview -->
            <div class="relative min-h-[50vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white md:min-h-min dark:border-sidebar-border dark:bg-sidebar">
                <div class="p-6">
                    <h2 class="mb-4 text-xl font-semibold">Grades Overview</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 dark:bg-sidebar-accent">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Active Students</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-sidebar">
                            <tr v-for="grade in gradesList" :key="grade.id" class="hover:bg-gray-50 dark:hover:bg-sidebar-accent">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    {{ grade.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    {{ grade.students_count || 0 }} students
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span v-if="grade.id >= maxGradeLevel" class="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                            Final Grade
                                        </span>
                                    <span v-else class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Active
                                        </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <Button
                                        v-if="grade.students_count > 0"
                                        variant="ghost"
                                        size="sm"
                                        @click="promoteGradeForm.from_grade_id = grade.id.toString(); openPromoteGrade = true"
                                    >
                                        Promote Grade
                                    </Button>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="gradesList.length === 0" class="py-12 text-center">
                            <p class="text-muted-foreground">No grades found.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promote Grade Modal -->
        <Dialog v-model:open="openPromoteGrade">
            <DialogContent
                class="sm:max-w-2xl"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <DialogHeader>
                    <DialogTitle>Promote Students by Grade</DialogTitle>
                    <DialogDescription>
                        Select a grade to promote all students to the next grade level.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <!-- From Grade -->
                    <div class="grid gap-2">
                        <Label for="from_grade_id">From Grade</Label>
                        <Select v-model="promoteGradeForm.from_grade_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select grade to promote from" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="grade in gradesList" :key="grade.id" :value="grade.id.toString()">
                                    {{ grade.name }} ({{ grade.students_count || 0 }} students)
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="promoteGradeForm.errors.from_grade_id" class="text-sm text-red-600">
                            {{ promoteGradeForm.errors.from_grade_id }}
                        </span>
                    </div>

                    <!-- To Grade -->
                    <div class="grid gap-2">
                        <Label for="to_grade_id">To Grade</Label>
                        <Select v-model="promoteGradeForm.to_grade_id" :disabled="!promoteGradeForm.from_grade_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select target grade" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="grade in filteredToGrades" :key="grade.id" :value="grade.id.toString()">
                                    {{ grade.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="!promoteGradeForm.from_grade_id" class="text-xs text-muted-foreground">
                            Select a grade first
                        </span>
                        <span v-if="promoteGradeForm.errors.to_grade_id" class="text-sm text-red-600">
                            {{ promoteGradeForm.errors.to_grade_id }}
                        </span>
                    </div>

                    <!-- Notes -->
                    <div class="grid gap-2">
                        <Label for="notes">Notes (Optional)</Label>
                        <Textarea
                            id="notes"
                            v-model="promoteGradeForm.notes"
                            placeholder="Add any notes about this promotion..."
                            rows="3"
                        />
                        <span v-if="promoteGradeForm.errors.notes" class="text-sm text-red-600">
                            {{ promoteGradeForm.errors.notes }}
                        </span>
                    </div>

                    <Alert class="border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-950">
                        <AlertCircle class="h-4 w-4" />
                        <AlertDescription>
                            This will promote all active students in the selected grade.
                        </AlertDescription>
                    </Alert>
                </div>

                <DialogFooter class="pt-4">
                    <DialogClose as-child>
                        <Button variant="outline" @click="resetPromoteGradeForm">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        @click="handlePromoteGrade"
                        :disabled="promoteGradeForm.processing"
                    >
                        {{ promoteGradeForm.processing ? 'Promoting...' : 'Promote Grade' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Promote All Modal -->
        <Dialog v-model:open="openPromoteAll">
            <DialogContent
                class="sm:max-w-2xl"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <DialogHeader>
                    <DialogTitle>Promote All Students</DialogTitle>
                    <DialogDescription>
                        This will promote ALL active students to their next grade level.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <!-- Notes -->
                    <div class="grid gap-2">
                        <Label for="promote_all_notes">Notes (Optional)</Label>
                        <Textarea
                            id="promote_all_notes"
                            v-model="promoteAllForm.notes"
                            placeholder="Add any notes about this bulk promotion..."
                            rows="3"
                        />
                    </div>

                    <Alert class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950">
                        <AlertCircle class="h-4 w-4 text-red-600" />
                        <AlertTitle class="text-red-800 dark:text-red-200">Warning</AlertTitle>
                        <AlertDescription class="text-red-700 dark:text-red-300">
                            This action will promote {{ totalActiveStudents }} students across all grades.
                            Students at the maximum grade level will be graduated. This action cannot be easily undone.
                        </AlertDescription>
                    </Alert>
                </div>

                <DialogFooter class="pt-4">
                    <DialogClose as-child>
                        <Button variant="outline" @click="resetPromoteAllForm">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        @click="handlePromoteAll"
                        :disabled="promoteAllForm.processing"
                        variant="destructive"
                    >
                        {{ promoteAllForm.processing ? 'Promoting...' : 'Confirm Promotion' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Start New Year Modal -->
        <Dialog v-model:open="openStartNewYear">
            <DialogContent
                class="sm:max-w-2xl"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <DialogHeader>
                    <DialogTitle>Start New Academic Year</DialogTitle>
                    <DialogDescription>
                        Begin a new academic year and optionally promote all students.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <!-- New Year -->
                    <div class="grid gap-2">
                        <Label for="new_year">New Academic Year</Label>
                        <Input
                            id="new_year"
                            v-model="newYearForm.new_year"
                            type="text"
                            :placeholder="getNextYear()"
                        />
                        <span v-if="newYearForm.errors.new_year" class="text-sm text-red-600">
                            {{ newYearForm.errors.new_year }}
                        </span>
                    </div>

                    <!-- Promote Students Checkbox -->
                    <div class="flex items-center space-x-2">
                        <input
                            id="promote_students"
                            v-model="newYearForm.promote_students"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300"
                        />
                        <Label for="promote_students" class="cursor-pointer">
                            Automatically promote all students to next grade
                        </Label>
                    </div>

                    <Alert class="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950">
                        <AlertCircle class="h-4 w-4" />
                        <AlertDescription>
                            Current year: {{ currentYear }} → New year: {{ newYearForm.new_year || getNextYear() }}
                            <br v-if="newYearForm.promote_students" />
                            <span v-if="newYearForm.promote_students" class="font-medium">
                                All students will be promoted to the next grade level.
                            </span>
                        </AlertDescription>
                    </Alert>
                </div>

                <DialogFooter class="pt-4">
                    <DialogClose as-child>
                        <Button variant="outline" @click="resetNewYearForm">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        @click="handleStartNewYear"
                        :disabled="newYearForm.processing || !newYearForm.new_year"
                    >
                        {{ newYearForm.processing ? 'Starting...' : 'Start New Year' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Settings Modal -->
        <Dialog v-model:open="openSettings">
            <DialogContent
                class="sm:max-w-2xl"
                @interact-outside="(e) => e.preventDefault()"
                @escape-key-down="(e) => e.preventDefault()"
            >
                <DialogHeader>
                    <DialogTitle>Progression Settings</DialogTitle>
                    <DialogDescription>
                        Configure student progression and graduation settings.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <!-- Max Grade Level -->
                    <div class="grid gap-2">
                        <Label for="max_grade_level">Maximum Grade Level</Label>
                        <Input
                            id="max_grade_level"
                            v-model="settingsForm.max_grade_level"
                            type="number"
                            min="1"
                            max="20"
                        />
                        <span class="text-xs text-muted-foreground">
                            Students at this grade level will be graduated when promoted
                        </span>
                        <span v-if="settingsForm.errors.max_grade_level" class="text-sm text-red-600">
                            {{ settingsForm.errors.max_grade_level }}
                        </span>
                    </div>

                    <!-- Auto Graduate -->
                    <div class="flex items-center space-x-2">
                        <input
                            id="auto_graduate_enabled"
                            v-model="settingsForm.auto_graduate_enabled"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300"
                        />
                        <Label for="auto_graduate_enabled" class="cursor-pointer">
                            Automatically graduate students at maximum grade level
                        </Label>
                    </div>
                </div>

                <DialogFooter class="pt-4">
                    <DialogClose as-child>
                        <Button variant="outline" @click="resetSettingsForm">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        @click="handleUpdateSettings"
                        :disabled="settingsForm.processing"
                    >
                        {{ settingsForm.processing ? 'Saving...' : 'Save Settings' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
