<script setup>
import CardBox from "@/components/CardBox.vue";
import NumberDynamic from "@/components/NumberDynamic.vue";
import BaseIcon from "@/components/BaseIcon.vue";
import BaseLevel from "@/components/BaseLevel.vue";

defineProps({
    number: {
        type: Number,
        default: 0,
    },
    icon: {
        type: String,
        default: null,
    },
    prefix: {
        type: String,
        default: null,
    },
    suffix: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: null,
    },
    color: {
        type: String,
        default: null,
    },
    trend: {
        type: String,
        default: null,
    },
    trendType: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <CardBox class="group transition-all duration-300 hover:scale-[1.02] hover:shadow-lg hover:shadow-forest-200/50 
           hover:border-forest-400 border-2 border-forest-50/60 dark:border-gray-700
           h-full" :is-hoverable="true">
        <BaseLevel mobile>
            <div>
                <h3 class="text-sm md:text-base lg:text-lg leading-tight text-forest-400 dark:text-forest-300 
                   group-hover:text-forest-400 dark:group-hover:text-forest-300 
                   transition-colors duration-300">
                    {{ label }}
                </h3>
                <h1 class="text-2xl md:text-3xl leading-tight font-semibold
                   text-forest-400 dark:text-forest-300
                   group-hover:text-forest-400 dark:group-hover:text-forest-200
                   transition-colors duration-300">
                    <NumberDynamic :value="number" :prefix="prefix" :suffix="suffix" />
                </h1>
            </div>
            <BaseIcon v-if="icon" :path="icon" size="48" w="" h="h-16"
                :class="['text-forest-400 dark:text-forest-300', color, 'group-hover:scale-110 group-hover:text-forest-400 dark:group-hover:text-forest-200 transition-transform duration-300']" />
        </BaseLevel>
    </CardBox>
</template>

