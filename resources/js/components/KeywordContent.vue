<template>
    <div class="relative flex flex-wrap gap-2 min-h-[40px] px-2 py-4 border rounded-md bg-gray-50">
        <Badge class="absolute top-1 right-1" size="xs" :title="`${keywords.length} palabras agregadas`">
            {{ keywords.length }}
        </Badge>

        <div v-for="(keyword, index) in keywords" :key="index"
            class="px-3 py-1 rounded-full text-sm bg-forest-100 text-mono-100 border cursor-default">
            <slot :keyword="keyword" :index="index" />
        </div>

        <span v-if="keywords.length === 0" class="text-gray-400 text-sm">
            No hay palabras agregadas
        </span>
    </div>
</template>
<script setup>
import Badge from './Badge.vue';

defineProps({
    keywords: {
        type: Array,
        default: () => [],
    },
})
</script>
