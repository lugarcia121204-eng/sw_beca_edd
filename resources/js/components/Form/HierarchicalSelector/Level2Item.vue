<template>
    <div>
        <div class="group px-6 py-3 hover:bg-forest-50/50 cursor-pointer transition-colors border-l-4 border-transparent hover:border-forest-100 flex items-center justify-between"
            @click="handleClick">
            <div class="flex-1 flex items-center gap-2">
                <ChevronRight v-if="hasChildren && !isExpanded" class="h-3 w-3 text-gray-400" />
                <ChevronDown v-else-if="hasChildren" class="h-3 w-3 text-gray-400" />
                <div class="flex-1">
                    <div class="font-medium" :class="isSelected ? 'text-success-400' : 'text-forest-100'">
                        {{ item.name }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ levelLabel }}</div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <CircleCheckBig v-if="isSelected" class="h-5 w-5 text-success-400 group-hover:text-forest-400" />
                <Plus v-else class="h-4 w-4 text-forest-100 group-hover:text-forest-400"
                    @click.stop="$emit('select', item)" />
            </div>
        </div>

        <div v-if="isExpanded && hasChildren" class="bg-gray-50">
            <Level3Item v-for="level3 in item.children" :key="level3.id" :item="level3"
                :is-selected="isLevel3Selected(level3)" :level-label="level3Label" @select="$emit('select', $event)" />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronRight, ChevronDown, CircleCheckBig, Plus } from "lucide-vue-next";
import Level3Item from './Level3Item.vue';

const props = defineProps({
    item: { type: Object, required: true },
    isExpanded: { type: Boolean, required: true },
    isSelected: { type: Boolean, required: true },
    isLevel3Selected: { type: Function, required: true },
    levelLabel: { type: String, required: true },
    level3Label: { type: String, required: true }
});

const emit = defineEmits(['toggle', 'select']);

const hasChildren = computed(() => props.item.children && props.item.children.length > 0);

const handleClick = () => {
    if (hasChildren.value) {
        emit('toggle', props.item.id);
    } else {
        emit('select', props.item);
    }
};
</script>
