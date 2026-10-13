<template>
    <div class="p-4 bg-secondary rounded-lg border mb-6" :class="classes">
        <div class="flex items-center justify-between mb-4">
            <slot v-if="$slots.content" />

            <div v-else class="flex items-center gap-3">
                <component :is="icon" class="h-5 w-5" :class="[switcher ? 'text-success' : 'text-muted-foreground']" />
                <div>
                    <label class="text-card-foreground font-medium cursor-pointer">
                        {{ label }}
                    </label>
                    <p class="text-xs text-muted-foreground">{{ description }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <FormCheckRadio v-model="switcher" :inputValue="inputValue" :name="name" :type="type" />
                <HelpCircle v-if="tooltip" class="h-4 w-4 text-muted-foreground" />
            </div>
        </div>

        <slot />
    </div>
</template>

<script setup>
import { HelpCircle, Star } from 'lucide-vue-next';
import FormCheckRadio from './FormCheckRadio.vue';

const switcher = defineModel({ default: null });

defineProps({
    classes: String,
    label: String,
    description: String,
    name: String,
    tooltip: Boolean,
    inputValue: {
        type: [String, Number, Boolean],
        required: true
    },
    type: {
        type: String,
        default: 'switch',
        validator: (value) => {
            return ['switch', 'checkbox', 'radio'].includes(value)
        }
    },
    icon: Function
})
</script>