<template>

    <div>
        <h1>Hapa</h1>
        {{links}}
    </div>
    <div v-if="links.length > 3" class="flex justify-between items-center border-t px-6 py-4">
        <div class="text-sm text-muted-foreground">
            Showing {{ from }} to {{ to }} of {{ total }} results
        </div>
        <div class="flex gap-2">
            <Button
                v-for="(link, index) in links"
                :key="index"
                variant="outline"
                size="sm"
                :disabled="!link.url || link.active"
                @click="link.url && router.visit(link.url)"
                :class="{ 'bg-primary text-primary-foreground': link.active }"
            >
                <span v-if="link.label.includes('Previous')">Previous</span>
                <span v-else-if="link.label.includes('Next')">Next</span>
                <span v-else>{{ link.label }}</span>
            </Button>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'

defineProps({
    links: Array,
    from: Number,
    to: Number,
    total: Number
})
</script>
