<template>
    <div @keyup.esc="closeOnEscape" class="space-y-2">
        <div class="relative" ref="searchInputContainer">
            <input v-model="searchQuery" type="text" :placeholder="placeholder"
                class="w-full px-4 py-2.5 pr-10 border bg-white text-black dark:text-slate-200 dark:bg-slate-800 border-slate-400 dark:border-slate-400 rounded focus:ring-2 focus:ring-forest-100 focus:border-forest-100 transition-colors"
                @focus="open" @keypress="open" />
            <Search class="absolute right-3 top-3.5 h-4 w-4 text-gray-400" />
        </div>

        <div v-if="isOpen" ref="target" class="relative">
            <div
                class="absolute top-2 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg max-h-80 overflow-y-auto z-10">
                <div v-if="filteredItems.length === 0" class="p-4 text-gray-500 text-center">
                    No se encontraron resultados
                </div>

                <Level1Item v-for="level1 in filteredItems" :key="level1.id" :item="level1"
                    :is-expanded="expandedLevel1.has(level1.id)" :is-selected="isSelected(level1)"
                    :is-level2-expanded="(id) => expandedLevel2.has(id)" :is-level2-selected="isSelected"
                    :is-level3-selected="isSelected" :level2-label="levelLabels[1]" :level3-label="levelLabels[2]"
                    @toggle="toggleLevel1" @toggle-level2="toggleLevel2" @select="selectItem" />
            </div>
        </div>

        <SelectedItemsList :selected-items="selectedItems" :get-level-label="getItemLevelLabel" @remove="removeItem" />
    </div>
</template>

<script setup>
import { Search } from "lucide-vue-next";
import { useHierarchicalSelector } from './useHierarchicalSelector.js';
import Level1Item from './Level1Item.vue';
import SelectedItemsList from './SelectedItemsList.vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    items: { type: Array, required: true },
    placeholder: { type: String, default: "Buscar..." },
    levelLabels: { type: Array, default: () => ['Nivel 1', 'Nivel 2', 'Nivel 3'] }
})

const emit = defineEmits(["update:modelValue"])

const {
    searchQuery,
    isOpen,
    selectedItems,
    expandedLevel1,
    expandedLevel2,
    filteredItems,
    toggleLevel1,
    toggleLevel2,
    isSelected,
    getItemLevelLabel,
    selectItem,
    removeItem,
    open,
    closeOnEscape
} = useHierarchicalSelector(props, emit);
</script>
