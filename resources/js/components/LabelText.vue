<template>
    <div>
        <label class="text-md font-medium text-forest-400">{{ label }}</label>
        <div v-if="text" v-html="text" class="ql-content leading-relaxed mt-1"></div>
        <div v-else-if="!text && !$slots.default" class="mt-1"> Sin datos </div>

        <div v-if="$slots.default" class="mt-1">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { useSlots } from 'vue';

const props = defineProps({
    label: String,
    text: String | Number,
});

const slots = useSlots();
</script>