<template>
    <div class="space-y-2">
        <div class="flex space-x-2">
            <FormControl v-model="input" type="text" maxlength="50" placeholder="Escriba una palabra y presione Enter"
                @keydown.enter.prevent="addKeyword" :disabled="keywords.length >= limit" class="flex-1" />
            <BaseButton @click="addKeyword" label="+ Agregar" color="lightDark" small outline
                :disabled="keywords.length >= limit || disabled" />
        </div>

        <KeywordContent :keywords="keywords">
            <template #default="{ keyword, index }">
                <button :disabled="disabled" @click="removeKeyword(index)"
                    class="inline-flex items-center cursor-pointer hover:text-error-300 disabled:hover:text-mono-100">
                    {{ keyword.name }}
                    <BaseIcon :path="mdiClose" />
                </button>
            </template>
        </KeywordContent>

        <InputError v-if="keywords.length >= limit" :message="`Máximo de ${limit} palabras alcanzado`" />
    </div>
</template>
<script setup>
import { ref } from "vue"
import FormControl from "./FormControl.vue"
import InputError from "./InputError.vue"
import BaseButton from "./BaseButton.vue"
import BaseIcon from "./BaseIcon.vue"
import { mdiClose } from "@mdi/js"
import KeywordContent from "./KeywordContent.vue"

const props = defineProps({
    limit: {
        type: Number,
        default: 5,
    },
    type: {
        type: [String],
        default: null
    },
    disabled: Boolean,
})

const keyword = defineModel({
    type: Array,
    default: () => [],
})
const keywords = ref([...keyword.value])
const input = ref("")

const addKeyword = () => {
    const value = input.value.trim()

    if (
        value &&
        !keywords.value.some(k => k.name === value) &&
        keywords.value.length < props.limit &&
        value.length < 255
    ) {
        keywords.value.push({ name: value, type: props.type })
        keyword.value = [...keywords.value]
    }

    input.value = ""
}

const removeKeyword = (index) => {
    keywords.value.splice(index, 1)
    keyword.value = [...keywords.value]
}
</script>
