<template>
    <label :class="labelClass">
        <input type="radio" class="hidden" :value="props.value" v-model="model" />

        <div :class="radioClasses">
            <div v-if="model === props.value" class="h-2 w-2 rounded-full bg-white"></div>
        </div>

        <div class="flex-1 text-left">
            <p class="font-medium text-foreground">{{ props.title }}</p>
            <p v-if="model === props.value" class="text-xs text-muted-foreground">{{ props.description }}</p>
        </div>

        <BaseIcon v-if="model === props.value && icon" size="22" w="w-auto" h="h-auto" :path="icon" />
    </label>
</template>
<script setup>
import BaseIcon from '@/components/BaseIcon.vue';
import { computed } from 'vue';

const model = defineModel({ type: [String, Number] })

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ""
    },
    value: {
        type: [String, Number],
        required: true
    },
    color: {
        type: String,
        default: "green"
    },
    icon: String
})

const colorClasses = {
    wine: {
        border: 'border-wine-400',
        bg: 'bg-wine-400',
        labelBorder: 'border-wine-400',
        labelBg: 'bg-wine-100'
    },
    green: {
        border: 'border-green-400',
        bg: 'bg-green-400',
        labelBorder: 'border-green-500',
        labelBg: 'bg-green-100'
    },
    red: {
        border: 'border-red-400',
        bg: 'bg-red-400',
        labelBorder: 'border-red-500',
        labelBg: 'bg-red-100'
    },
    forest: {
        border: 'border-forest-400',
        bg: 'bg-forest-400',
        labelBorder: 'border-forest-500',
        labelBg: 'bg-forest-100'
    },
    yellow: {
        border: 'border-yellow-400',
        bg: 'bg-yellow-400',
        labelBorder: 'border-yellow-500',
        labelBg: 'bg-yellow-100'
    }
}

const radioClasses = computed(() => [
    'flex h-5 w-5 items-center justify-center rounded-full border-2',
    model.value === props.value
        ? [colorClasses[props.color].border, colorClasses[props.color].bg]
        : 'border-muted-foreground'
]);

const labelClass = computed(() => [
    'flex w-full cursor-pointer items-center gap-3 rounded-lg border-2 p-4 transition-all',
    model.value === props.value
        ? [colorClasses[props.color].labelBorder, colorClasses[props.color].labelBg]
        : 'border-border bg-transparent hover:border-muted'
]);
</script>
