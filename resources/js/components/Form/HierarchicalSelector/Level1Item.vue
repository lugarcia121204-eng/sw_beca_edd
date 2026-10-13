<template>
    <div class="border-b border-gray-100 last:border-b-0">
        <div class="sticky top-0 px-4 py-3 bg-gray-200 font-bold text-gray-700 hover:bg-gray-100 transition-colors flex items-center justify-between cursor-pointer"
            @click="$emit('toggle', item.id)">
            <div class="flex items-center gap-2">
                <ChevronRight v-if="!isExpanded" class="h-4 w-4" />
                <ChevronDown v-else class="h-4 w-4" />
                <span>{{ item.name }}</span>
            </div>
            <CircleCheckBig v-if="isSelected" class="h-5 w-5 text-success-400" />
            <Plus v-else class="h-4 w-4 text-forest-100 hover:text-forest-400" @click.stop="$emit('select', item)" />
        </div>

        <div v-if="isExpanded" class="bg-white">
            <div v-if="item.children && item.children.length > 0">
                <Level2Item v-for="level2 in item.children" :key="level2.id" :item="level2"
                    :is-expanded="isLevel2Expanded(level2.id)" :is-selected="isLevel2Selected(level2)"
                    :is-level3-selected="isLevel3Selected" :level-label="level2Label" :level3-label="level3Label"
                    @toggle="$emit('toggle-level2', $event)" @select="$emit('select', $event)" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ChevronRight, ChevronDown, CircleCheckBig, Plus } from "lucide-vue-next";
import Level2Item from './Level2Item.vue';

defineProps({
    item: { type: Object, required: true },
    isExpanded: { type: Boolean, required: true },
    isSelected: { type: Boolean, required: true },
    isLevel2Expanded: { type: Function, required: true },
    isLevel2Selected: { type: Function, required: true },
    isLevel3Selected: { type: Function, required: true },
    level2Label: { type: String, required: true },
    level3Label: { type: String, required: true }
});

defineEmits(['toggle', 'toggle-level2', 'select']);
</script>
