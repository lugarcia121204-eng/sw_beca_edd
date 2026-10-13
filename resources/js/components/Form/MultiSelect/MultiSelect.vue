<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <div :id="inputId" @keydown.enter.space.prevent="toggleDropdown" @keydown.escape="closeDropdown"
                :tabindex="disabled ? -1 : 0" class="flex items-center justify-between min-h-10 py-1 px-2 rounded border border-slate-500 text-sm transition-all
             bg-white text-black cursor-pointer disabled:cursor-not-allowed">
                <div class="flex-1 min-w-0 overflow-hidden">
                    <span v-if="selectedItems.length === 0" class="text-gray-400 truncate">
                        {{ placeholder }}
                    </span>

                    <div v-else class="flex flex-wrap gap-1 items-center">
                        <span v-for="item in displayedItems" :key="item[valueKey]"
                            class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-sand-50 text-forest-500">
                            {{ item[labelKey] }}
                            <BaseIcon v-if="!disabled" @click.stop="removeItem(item[valueKey])" :size="12"
                                :path="mdiClose" class="hover:text-red-600" />
                        </span>

                        <span v-if="remainingCount > 0" class="px-1.5 py-0.5 bg-gray-100 rounded text-xs text-gray-500">
                            +{{ remainingCount }} más
                        </span>
                    </div>
                </div>

                <ChevronRight class="w-5 h-5 transition-transform" :class="{ 'rotate-90': isOpen }" />
            </div>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="start" class="w-max p-0 rounded-md shadow-lg">
            <div class="bg-white border rounded-md overflow-hidden">
                <div v-if="searchable" class="p-2 border-b">
                    <FormControl ref="searchInput" type="text" v-model="searchTerm" :placeholder="searchPlaceholder"
                        @input="handleSearchInput" @keydown.escape="closeDropdown" class="w-full" />
                </div>

                <div v-if="isLoading" class="flex items-center justify-center gap-2 p-3 text-gray-500 text-sm">
                    <div class="h-2 w-2 bg-gray-500 rounded-full animate-bounce"></div>
                    <div class="h-2 w-2 bg-gray-500 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                    <div class="h-2 w-2 bg-gray-500 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                </div>

                <div v-if="showSelectAll && !isLoading" class="flex gap-2 p-2 border-b">
                    <BaseButton @click="selectAll" :disabled="filteredOptions.length === selectedItems.length"
                        color="lightDark" label="Seleccionar todo" class="flex-1" />
                    <BaseButton @click="clearAll" :disabled="selectedItems.length === 0" color="lightDark"
                        label="Limpiar" class="flex-1" />
                </div>

                <div v-if="!isLoading" class="overflow-y-auto max-h-60 divide-y">
                    <div v-for="option in filteredOptions" :key="option[valueKey]" @click="toggleOption(option)"
                        class="flex items-center px-3 py-2 cursor-pointer select-none transition-colors hover:bg-sand-50"
                        :class="{ 'bg-transparent text-forest-600': isSelected(option[valueKey]) }">
                        <input type="checkbox" :checked="isSelected(option[valueKey])" :disabled="disabled"
                            tabindex="-1"
                            class="mr-2 w-4 h-4 rounded-sm border-forest-600 checked:bg-forest-600 focus:ring-forest-700" />
                        <span class="text-sm flex-1 truncate">
                            {{ option[labelKey] }}
                        </span>
                    </div>

                    <div v-if="filteredOptions.length === 0" class="p-3 text-center text-sm text-gray-500">
                        {{ noOptionsText }}
                    </div>
                </div>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup>
import BaseButton from '@/components/BaseButton.vue';
import BaseIcon from '@/components/BaseIcon.vue';
import FormControl from '@/components/FormControl.vue';
import { useMultiSelectState } from './useMultiSelectState';
import { useMultiSelectSelection } from './useMultiSelectSelection';
import { useMultiSelectSearch } from './useMultiSelectSearch';
import { useMultiSelectDropdown } from './useMultiSelectDropdown';
import { useMultiSelectValidation } from './useMultiSelectValidation';
import { ChevronRight } from 'lucide-vue-next';
import { mdiClose } from '@mdi/js';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const props = defineProps({
    options: {
        type: Array,
        required: true,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Seleccionar opciones...'
    },
    valueKey: {
        type: String,
        default: 'value'
    },
    labelKey: {
        type: String,
        default: 'label'
    },
    searchable: {
        type: Boolean,
        default: true
    },
    searchPlaceholder: {
        type: String,
        default: 'Buscar...'
    },
    showSelectAll: {
        type: Boolean,
        default: true
    },
    maxHeight: {
        type: Number,
        default: 200
    },
    maxTags: {
        type: Number,
        default: 3
    },
    disabled: {
        type: Boolean,
        default: false
    },
    required: {
        type: Boolean,
        default: false
    },
    errorMessage: {
        type: String,
        default: ''
    },
    noOptionsText: {
        type: String,
        default: 'No hay opciones disponibles'
    },
    asyncSearch: {
        type: Boolean,
        default: false
    },
    searchDebounce: {
        type: Number,
        default: 300
    },
    loading: {
        type: Boolean,
        default: false
    },
    initialSelectedOptions: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['change', 'open', 'close', 'search']);

const modelValue = defineModel({
    type: Array,
    default: () => []
});

const {
    isOpen,
    searchTerm,
    searchInput,
    isLoading,
    cachedSelectedOptions,
    inputId,
    hasError
} = useMultiSelectState(props);

const {
    selectedItems,
    displayedItems,
    remainingCount,
    isSelected,
    toggleOption: _toggleOption,
    removeItem: _removeItem,
    selectAll: _selectAll,
    clearAll: _clearAll
} = useMultiSelectSelection(props, modelValue, cachedSelectedOptions);

const {
    filteredOptions,
    handleSearchInput: _handleSearchInput,
    clearSearchTimeout
} = useMultiSelectSearch(props, searchTerm, isLoading);

const {
    toggleDropdown,
    closeDropdown
} = useMultiSelectDropdown(
    props,
    isOpen,
    searchTerm,
    searchInput,
    isLoading,
    clearSearchTimeout,
    inputId,
    emit
);

useMultiSelectValidation(props, modelValue, isLoading, emit);

const toggleOption = (option) => _toggleOption(option, emit);
const removeItem = (value) => _removeItem(value, emit);
const selectAll = () => _selectAll(filteredOptions, emit);
const clearAll = () => _clearAll(emit);
const handleSearchInput = () => _handleSearchInput(emit);
</script>
