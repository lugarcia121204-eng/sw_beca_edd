<template>
    <div class="relative my-quill-wrapper">
        <QuillEditor theme="snow" v-model:content="content" content-type="html" :placeholder="placeholder"
            class="my-quill" />
        <span
            class="bg-slate-50/80 rounded-2xl absolute bottom-4 right-5 text-xs text-gray-500 dark:text-gray-400 pointer-events-none">
            {{ length }} / {{ props.maxLength }}
        </span>
    </div>
</template>
<script setup>
import { QuillEditor } from '@vueup/vue-quill'
import { computed, watch } from 'vue'

const props = defineProps({
    maxLength: {
        type: [String, Number],
        default: 255
    },
    placeholder: {
        type: String,
        default: 'Ingresa una descripción'
    },
})

const content = defineModel({
    type: String,
    default: ''
})

watch(content, (val) => {
    const isQuillEmpty =
        val === '<p><br></p>' ||
        val === '<p></p>' ||
        val.trim() === '' ||
        val.replace(/<[^>]*>/g, '').trim() === ''

    if (isQuillEmpty) {
        content.value = ''
    }
})

const length = computed(() => {
    if (!content.value) return 0
    const plainText = content.value.replace(/<[^>]*>/g, '')
    return plainText.trim().length
})
</script>
<style scoped>
.my-quill-wrapper {
    border: 1px solid #90a1b9;
    border-radius: 8px;
    transition: border-color .15s ease, box-shadow .15s ease;
    overflow: hidden;
}

.my-quill-wrapper ::v-deep .ql-container {
    border: none;
    border-radius: inherit;
    box-shadow: none;
}

.my-quill-wrapper ::v-deep .ql-editor {
    min-height: 230px !important;
    padding: 0.75rem 1rem;
}

.my-quill-wrapper:focus-within {
    border-color: #86857E;
    border-width: 2px;
}
</style>