<template>
    <div :class="cn(
        'bg-muted rounded-lg mx-auto md:mx-0 flex items-center justify-center border-2 border-dashed border-border shrink-0',
        'w-60 max-h-60',
        props.class
    )">
        <img :src="finalSrc" :alt="alt || typeImage[type]" :class="cn(
            'object-contain rounded-lg',
            'w-60 max-h-60',
            imageClass
        )" />
    </div>
</template>

<script setup>
import { cn } from '@/lib/utils';
import { IMAGES } from '@/utils/image';
import { computed } from 'vue';

const props = defineProps({
    src: String,
    alt: String,
    class: String,
    imageClass: String,
    type: {
        type: String,
        default: 'image',
        validator: (value) => {
            return ['image', 'user'].includes(value);
        },
    },
});

const typeImage = {
    image: IMAGES.image.src,
    user: IMAGES.user.src,
};

const finalSrc = computed(() => props.src ?? typeImage[props.type]);
</script>
