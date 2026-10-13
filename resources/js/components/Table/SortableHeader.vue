<template>
    <th @click="handleSort"
        class="w-min hover:cursor-pointer hover:bg-forest-50/15 transition-colors duration-200 px-4 py-2 text-left">
        <div class="flex items-center gap-1">
            <span :class="{ 'capitalize': !noCaps }">{{ label }}</span>
            <ArrowUp v-if="isActive" class="w-4 h-4 transition-transform duration-200 text-forest-400"
                :class="direction === 'desc' ? 'rotate-180' : ''" />
            <ArrowUpDown v-else class="w-4 h-4" />
        </div>
    </th>
</template>

<script setup>
import { ArrowUp, ArrowUpDown } from 'lucide-vue-next'
import { computed, inject } from 'vue'

const props = defineProps({
    column: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    noCaps: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['sort']);
const injectedFilters = inject('filters', null);
const injectedSortByColumn = inject('sortByColumn', null);

const activeFilters = computed(() => injectedFilters || props.filters);

const isActive = computed(() => activeFilters.value?.order === props.column);
const direction = computed(() => isActive.value ? activeFilters.value?.direction : null);

const handleSort = () => {
    let newDirection = 'asc';
    if (isActive.value) {
        newDirection = activeFilters.value.direction === 'asc' ? 'desc' : 'asc';
    }

    if (injectedSortByColumn) {
        injectedSortByColumn(props.column, newDirection)
    } else {
        emit('sort', props.column, newDirection)
    }
}
</script>
