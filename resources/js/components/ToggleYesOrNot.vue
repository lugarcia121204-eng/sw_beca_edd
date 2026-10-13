<template>
    <div class="w-full my-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 gap-2">
            <label class="text-sm font-medium">
                {{ label }}
            </label>

            <div class="inline-flex rounded-lg bg-gray-100 p-1 shadow-sm w-fit">
                <button @click="toggle = true" :class="yesClass">
                    Sí
                </button>

                <button @click="toggle = false" :class="noClass">
                    No
                </button>
            </div>
        </div>
        <InputError :message="error" />
    </div>
</template>
<script setup>
import { computed } from 'vue'
import InputError from './InputError.vue'

defineProps({
    label: {
        type: String,
        required: true
    },
    error: {
        type: String,
        default: null
    }
})

const toggle = defineModel({ type: Boolean, default: false })

const baseClasses =
    'px-5 py-1.5 rounded-md text-xs font-medium transition-all duration-200 ease-in-out cursor-pointer'

const yesClass = computed(() =>
    toggle.value
        ? `${baseClasses} bg-white text-gray-900 shadow-sm`
        : `${baseClasses} text-gray-600 hover:text-gray-900`
)

const noClass = computed(() =>
    !toggle.value
        ? `${baseClasses} bg-white text-gray-900 shadow-sm`
        : `${baseClasses} text-gray-600 hover:text-gray-900`
)
</script>
