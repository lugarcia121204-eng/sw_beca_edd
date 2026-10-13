<script setup>
import { computed } from 'vue';
import { mdiContentCopy } from '@mdi/js';
import BaseButton from './BaseButton.vue';
import { ref } from 'vue';
import { Clipboard, ClipboardCheck } from 'lucide-vue-next';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/components/ui/tooltip"

const props = defineProps({
    value: {
        type: [String, Number, Array],
        default: null,
    },
    height: {
        type: String,
        default: 'h-auto',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
})

const labelClass = computed(() => {
    const base = [
        "text-sm shadow-none rounded px-2 border relative pr-10 py-2.5 items-center min-h-10",
        props.disabled ?
            'bg-slate-100 text-slate-500 dark:text-slate-400 dark:bg-transparent border-slate-300 dark:border-slate-700/75 cursor-not-allowed' : 'bg-white text-black dark:text-slate-200 dark:bg-slate-800 border-slate-400 dark:border-slate-400',
        props.height,
    ];

    if (!props.value) base.push("text-gray-400")

    return base;
});

const copied = ref(false)

const copyToClipboard = () => {
    if (!props.value) return

    const copyText = props.value

    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(copyText)
            .then(() => showCopiedIcon())
            .catch(err => console.error('Error copiando al portapapeles:', err))
    } else {
        const textarea = document.createElement('textarea')
        textarea.value = copyText
        textarea.style.position = 'absolute'
        textarea.style.opacity = '0'
        document.body.appendChild(textarea)
        textarea.select()
        document.execCommand('copy')
        document.body.removeChild(textarea)
        showCopiedIcon()
    }
}

const showCopiedIcon = () => {
    copied.value = true
    setTimeout(() => {
        copied.value = false
    }, 1500)
}
</script>

<template>
    <div :class="labelClass">
        <span>{{ props.value }}</span>
        <span v-if="!props.value">Sin información</span>

        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger asChild>
                    <button @click="copyToClipboard"
                        class="p-0.5 absolute top-2.5 right-1 rounded-sm hover:bg-slate-100 hover:cursor-pointer transition-colors duration-200">
                        <component :is="copied ? ClipboardCheck : Clipboard" class="w-4 h-4" />
                    </button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ copied ? 'Copiado' : 'Copiar en el portapapeles' }}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    </div>
</template>
