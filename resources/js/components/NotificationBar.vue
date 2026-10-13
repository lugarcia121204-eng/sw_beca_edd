<script setup>
import { ref } from 'vue';
import BaseIcon from './BaseIcon.vue';
import { computed } from 'vue';
import { colorsBgLight, colorsOutline, colorsText } from '@/config/colors';
import BaseButton from './BaseButton.vue';
import { mdiClose } from '@mdi/js';
import { getButtonColor } from '@/config/colors';

const props = defineProps({
    icon: {
        type: String,
        default: null,
    },
    color: {
        type: String,
        default: null,
    },
    outline: Boolean
});

const isDismissed = ref(false);

const dismiss = () => {
    isDismissed.value = true;
};

</script>

<template>
    <div v-if="!isDismissed" class="md:py-3 mb-6 w-full p-4 rounded-lg border"
        :class="outline ? colorsOutline[props.color] : colorsBgLight[props.color]" role="alert">
        <div class="flex">
            <div v-if="icon" :class="getButtonColor(props.color)"
                class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg">
                <BaseIcon :path="icon" />
            </div>
            <div class="ms-3 text-sm font-normal" :class="colorsText[props.color]">
                <span class="mb-1 text-sm font-semibold">
                    <slot name="title" />
                </span>
                <div class="text-sm font-normal">
                    <slot name="description" />
                </div>
            </div>
            <!-- <button type="button" @click="dismiss"
                class="ms-auto -mx-1.5 -my-1.5 bg-white items-center justify-center shrink-0 text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
                aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button> -->
            <div class="ms-auto shrink-0 -mx-1.5 -my-1.5 h-8 w-8 bg-white items-center justify-center">
                <BaseButton @click="dismiss" :icon="mdiClose" small :color="props.color" />
            </div>
        </div>
    </div>
</template>