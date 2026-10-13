<template>
    <div v-if="selectedItems.length > 0" class="space-y-3">
        <h3 class="text-sm font-medium text-gray-700 dark:text-slate-200">
            Seleccionados ({{ selectedItems.length }})
        </h3>

        <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
            <div v-for="item in selectedItems" :key="item.id"
                class="group flex items-center justify-between p-3 bg-mono-100 dark:bg-slate-800 border border-forest-50 rounded-lg hover:bg-forest-100 transition-colors">
                <div class="flex-1">
                    <div class="font-medium text-forest-400 group-hover:text-white dark:text-blue-600">
                        {{ item.name }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5 group-hover:text-white">{{ getLevelLabel(item) }}</div>
                </div>
                <button title="Eliminar" @click="$emit('remove', item)"
                    class="hover:cursor-pointer p-1 text-black group-hover:bg-white rounded transition-colors">
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
    <CardBoxComponentEmpty v-else />
</template>

<script setup>
import { X } from "lucide-vue-next";
import CardBoxComponentEmpty from "@/components/CardBoxComponentEmpty.vue";

defineProps({
    selectedItems: { type: Array, required: true },
    getLevelLabel: { type: Function, required: true }
});

defineEmits(['remove']);
</script>

