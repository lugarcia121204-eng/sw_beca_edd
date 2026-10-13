<template>
    <div @keyup.esc="closeOnEscape" class="relative">
        <div class="relative">
            <input v-model="searchQuery" type="text" :placeholder="placeholder" :disabled="disabled"
                class="w-full px-2 py-2 pr-10 border bg-white text-black dark:text-slate-200 dark:bg-slate-800 border-slate-400 dark:border-slate-400 rounded focus:ring-2 focus:ring-forest-100 focus:border-forest-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed h-10 text-sm"
                @focus="open" @click="open" @keypress="open" />

            <Search class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />

            <button v-if="modelValue" @click.stop="clearSelection" type="button" title="Limpiar selección"
                class="absolute right-7 top-1/2 -translate-y-1/2 text-gray-400 hover:text-forest-100 transition-colors">
                <X class="h-4 w-4" />
            </button>
        </div>

        <div v-if="isOpen" ref="target" class="relative">
            <div
                class="absolute top-2 left-0 right-0 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-600 rounded-lg shadow-lg max-h-80 overflow-y-auto z-10">
                <div v-if="filteredItems.length === 0" class="p-4 text-gray-500 dark:text-slate-400 text-center">
                    No se encontraron resultados
                </div>

                <div v-for="item in filteredItems" :key="item.id"
                    class="group px-6 py-3 hover:bg-forest-50 dark:hover:bg-slate-700 cursor-pointer transition-colors border-l-4 border-transparent hover:border-forest-100 flex items-center justify-between"
                    @click="selectItem(item)">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-slate-200">{{ item.name }}</div>
                        <div v-if="item.description" class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{
                            item.description }}</div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <CircleCheckBig v-if="isSelected(item)"
                            class="h-5 w-5 text-success-400 group-hover:text-forest-400" />
                        <Plus v-else
                            class="h-4 w-4 text-forest-100 opacity-0 group-hover:opacity-100 transition-opacity" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, useTemplateRef } from "vue";
import { Search, CircleCheckBig, Plus, X } from "lucide-vue-next";
import { onClickOutside } from '@vueuse/core';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    items: { type: Array, required: true },
    placeholder: { type: String, default: "Buscar..." },
    disabled: { type: Boolean, default: false }
})

const emit = defineEmits(["update:modelValue"])

const searchQuery = ref("")
const isOpen = ref(false)
const target = useTemplateRef('target');

const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items
    return props.items.filter(item =>
        [item.name, item.description].some(text =>
            (text ?? '').toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    )
})

const selectedItem = computed(() => {
    if (!props.modelValue) return null
    return props.items.find(item => item.id == props.modelValue)
})

const isSelected = (item) => item.id == props.modelValue

const selectItem = (item) => {
    emit("update:modelValue", item.id)
    searchQuery.value = item.name
    close()
}

const clearSelection = () => {
    emit("update:modelValue", '')
    searchQuery.value = ''
}

watch(selectedItem, (newItem) => {
    if (newItem) {
        searchQuery.value = newItem.name
    } else {
        searchQuery.value = ''
    }
}, { immediate: true })

watch(isOpen, (newVal) => {
    if (!newVal && selectedItem.value) {
        searchQuery.value = selectedItem.value.name
    } else if (!newVal && !selectedItem.value) {
        searchQuery.value = ''
    }
})

const open = () => {
    isOpen.value = true
}

const close = () => {
    isOpen.value = false
}

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && isOpen.value) {
        close()
    }
}

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape)
})

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape)
})

onClickOutside(target, () => close())
</script>
