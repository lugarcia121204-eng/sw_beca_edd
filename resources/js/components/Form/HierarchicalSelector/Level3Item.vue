<template>
    <div class="group px-6 py-2.5 hover:bg-forest-50/50 cursor-pointer transition-colors border-l-4 border-transparent hover:border-forest-100 flex items-center justify-between ml-6"
        @click="$emit('select', item)">
        <div class="flex-1">
            <div class="font-medium text-sm" :class="isSelected ? 'text-success-400' : 'text-gray-700'">
                {{ item.name }}
            </div>
            <div class="text-xs text-gray-500 mt-0.5">{{ levelLabel }}</div>
        </div>
        <div class="flex items-center space-x-2">
            <CircleCheckBig v-if="isSelected" class="h-5 w-5 text-success-400 group-hover:text-forest-400" />
            <Plus v-else class="h-4 w-4 text-forest-100 group-hover:text-forest-400" />
        </div>
    </div>
</template>

<script setup>
import { CircleCheckBig, Plus } from "lucide-vue-next";

defineProps({
    item: { type: Object, required: true },
    isSelected: { type: Boolean, required: true },
    levelLabel: { type: String, required: true }
});

defineEmits(['select']);
</script>
