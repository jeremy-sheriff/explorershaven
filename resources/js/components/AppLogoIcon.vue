<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg width="400" height="400" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="200" cy="180" r="120" fill="#FFF9E6" />

        <path d="M200 30L215 70H185L200 30Z" fill="#FFB800" />
        <path d="M200 330L215 290H185L200 330Z" fill="#FFB800" />
        <path d="M370 180L330 195V165L370 180Z" fill="#FFB800" />
        <path d="M30 180L70 195V165L30 180Z" fill="#FFB800" />

        <path d="M100 220L200 260L300 220L200 240L100 220Z" fill="#4A90E2" />
        <path d="M200 240L200 130L260 220H140L200 130" stroke="#4A90E2" stroke-width="8" stroke-linejoin="round" />

        <text x="200" y="360" font-family="Arial, sans-serif" font-size="32" font-weight="bold" fill="#2C3E50" text-anchor="middle">EXPLORERS</text>
        <text x="200" y="390" font-family="Arial, sans-serif" font-size="24" letter-spacing="4" fill="#4A90E2" text-anchor="middle">HAVEN</text>
    </svg>

</template>
