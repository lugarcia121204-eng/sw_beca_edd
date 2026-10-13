<template>
    <ol v-if="assessments.length > 0" :class="classes">
        <li v-for="assessment in assessments" :key="assessment.id" class="mb-10 ms-4 last:mb-0">
            <div class="absolute z-0 w-3 h-3 bg-gray-200 rounded-full mt-1.5 -start-1.5 border border-white">
            </div>

            <div class="flex flex-col gap-2">
                <time class="text-xs text-gray-400 dark:text-gray-500" :datetime="assessment.created_at.formatted">
                    {{ assessment.created_at.human }}
                </time>

                <div class="flex  items-center gap-3 mb-2">

                    <img class="w-10 h-10 rounded-full object-cover"
                        :src="assessment.user?.photo?.url ?? IMAGES.user.src"
                        :alt="assessment.user?.name ?? IMAGES.user.alt" />
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ assessment.user?.name }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ assessment.user?.email }}
                        </p>
                    </div>
                </div>
            </div>

            <p class="mb-4 text-sm text-gray-800">
                {{ assessment.body }}
            </p>
        </li>
    </ol>

    <CardBoxComponentEmpty v-else />
</template>

<script setup>
import CardBoxComponentEmpty from './CardBoxComponentEmpty.vue';
import { IMAGES } from "@/utils/image";

defineProps({
    assessments: {
        type: Array,
        default: () => ([]),
    },
    classes: {
        type: String,
        default: 'relative border-s border-gray-200'
    }
})
</script>