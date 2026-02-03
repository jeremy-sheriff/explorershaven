<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Download, FileText } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
  grades: { id: number; name: string }[];
  terms: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Students', href: '/students' },
  { title: 'Balance Report', href: '/students/balance-report' },
];

const filters = ref({
  grade_id: 'all',
  term: 'all',
  only_with_balance: false,
});

const exporting = ref(false);

const exportReport = async () => {
  if (exporting.value) return;

  exporting.value = true;
  try {
    const params = new URLSearchParams();

    if (filters.value.grade_id !== 'all') {
      params.append('grade_id', filters.value.grade_id);
    }
    if (filters.value.term !== 'all') {
      params.append('term', filters.value.term);
    }
    if (filters.value.only_with_balance) {
      params.append('only_with_balance', '1');
    }

    const response = await axios.get(`/students/balance-report/download?${params.toString()}`, {
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `student-balances-${new Date().toISOString().split('T')[0]}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting PDF:', error);
    alert('Failed to export PDF. Please try again.');
  } finally {
    exporting.value = false;
  }
};
</script>

<template>
  <Head title="Student Balance Report" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar">
        <div class="mb-6 flex items-center gap-3">
          <FileText class="h-6 w-6" />
          <h1 class="text-xl font-semibold">Student Balance Report</h1>
        </div>

        <p class="mb-6 text-sm text-muted-foreground">
          Generate a PDF report showing student fee balances. Select filters below and click download.
        </p>

        <!-- Filters -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <!-- Grade Filter -->
          <div class="space-y-2">
            <Label>Grade</Label>
            <Select v-model="filters.grade_id">
              <SelectTrigger>
                <SelectValue placeholder="All Grades" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Grades</SelectItem>
                <SelectItem
                    v-for="grade in grades"
                    :key="grade.id"
                    :value="String(grade.id)"
                >
                  {{ grade.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Term Filter -->
          <div class="space-y-2">
            <Label>Term</Label>
            <Select v-model="filters.term">
              <SelectTrigger>
                <SelectValue placeholder="All Terms" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Terms</SelectItem>
                <SelectItem
                    v-for="term in terms"
                    :key="term"
                    :value="term"
                >
                  {{ term }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Only With Balance -->
          <div class="flex items-end space-x-2 pb-2">
            <Checkbox
                id="only_with_balance"
                :checked="filters.only_with_balance"
                @update:checked="filters.only_with_balance = $event"
            />
            <Label for="only_with_balance" class="cursor-pointer">
              Only students with balance
            </Label>
          </div>
        </div>

        <!-- Download Button -->
        <Button
            @click="exportReport"
            :disabled="exporting"
            class="gap-2"
        >
          <Download class="h-4 w-4" />
          {{ exporting ? 'Generating...' : 'Download PDF Report' }}
        </Button>
      </div>
    </div>
  </AppLayout>
</template>