<template>
    <div v-if="items.length > 0" class="flex flex-wrap mt-2 gap-2">
        <Badge v-for="item in items" :key="item.id" variant="solid" :color="color">
            {{ item[nameKey] }}
        </Badge>
    </div>
    <p v-else>{{ emptyText }}</p>
</template>

<script setup>
import Badge from "./Badge.vue";

defineProps({
    items: {
        type: Array,
        required: false,
        default: () => [],
    },
    nameKey: {
        type: String,
        default: 'name',
    },
    color: {
        type: String,
        default: 'forest',
    },
    emptyText: {
        type: String,
        default: 'Sin datos',
    },
});
</script>
