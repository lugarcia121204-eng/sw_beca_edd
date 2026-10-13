<template>
    <div class="flex flex-col lg:flex-row items-center justify-between mt-6">
        <div class="mt-4 lg:mt-0 lg:pr-2 order-2">
            <ul class="pagination flex flex-wrap gap-1 md:flex-nowrap">
                <li v-for="({ url, label, active }, key) in links" :key="key">
                    <button type="button" :class="getLinkClasses(url, label, active)" :disabled="!url"
                        :aria-label="getAriaLabel(label, active)" :aria-current="active ? 'page' : undefined"
                        @click="handleClick(url)">
                        <span v-if="isPrevious(label)" class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>

                        <span v-else-if="isNext(label)" class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>

                        <span v-else v-html="label"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="order-1 text-sm text-gray-600 dark:text-gray-400">
            <span v-if="total > 0">
                Mostrando del {{ from }} al {{ to }} de un total de
                {{ total }} {{ typeRecords }}
            </span>
            <span v-else>Sin {{ typeRecords }}</span>
        </div>
    </div>
</template>

<script setup>
import { usePaginationApi } from "@/Hooks/usePaginationApi";

const props = defineProps({
    typeRecords: {
        type: String,
        default: 'registros',
    },
    links: {
        type: Array,
        required: true,
    },
    total: {
        type: Number,
        default: 0,
    },
    from: {
        type: Number,
        default: 0,
    },
    to: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['page-change']);

const { extractPage, getAriaLabel, getLinkClasses, isPrevious, isNext } = usePaginationApi();

const handleClick = (url) => {
    const page = extractPage(url);
    if (page) {
        emit('page-change', page);
    }
};
</script>