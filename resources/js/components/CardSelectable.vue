<template>
    <div class="">
        <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <BaseIcon :path="icon" size="20" h="h-auto" w="w-auto" class="p-2 rounded-full bg-forest-400 text-white" />
            {{ title }}
        </h3>
        <div class="space-y-2">
            <div v-if="itemsFiltered.length > 0" v-for="i in itemsFiltered" :key="i.id"
                class="group flex items-center justify-between bg-mono-100 hover:bg-forest-100 border border-gray-300 dark:bg-slate-950 rounded-lg px-3 py-2">
                <span class="text-slate-800 group-hover:text-white dark:text-slate-200">
                    {{ i.name }}
                </span>
            </div>
            <CardBoxComponentEmpty v-else />
        </div>
    </div>
</template>
<script setup>
import { mdiFormatListBulleted } from '@mdi/js'
import { computed } from 'vue'
import BaseIcon from '@/components/BaseIcon.vue'
import CardBoxComponentEmpty from '@/components/CardBoxComponentEmpty.vue'

const props = defineProps({
    icon: {
        type: String,
        default: mdiFormatListBulleted,
    },
    title: {
        type: String,
        required: true,
    },
    items: {
        type: Array,
        default: () => [],
    },
    isFiltered: {
        type: Boolean,
        default: true,
    }
})

const model = defineModel({
    type: Array,
    default: () => [],
})

const itemsFiltered = computed(() => {
    return props.isFiltered ? props.items.filter(item => model.value.includes(item.id)) : props.items
})
</script>

