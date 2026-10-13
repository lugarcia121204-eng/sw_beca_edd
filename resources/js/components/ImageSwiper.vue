<template>
    <div class="w-full mx-auto p-4 space-y-6">
        <swiper v-if="photos?.length > 0" :spaceBetween="10" :slidesPerView="slides" :autoplay="{
            delay: 2500,
            disableOnInteraction: false,
        }" :pagination="{ clickable: true }" :navigation="true" :modules="[Pagination, Navigation]"
            :breakpoints="breakpoints" class="mySwiper">
            <swiper-slide v-for="(photo, index) in photos" :key="index" class="mb-8">
                <div class="relative group w-full h-64 md:h-72 lg:h-80 overflow-hidden rounded-lg shadow"
                    :class="{ 'border-4 border-red-600': errors[`photos.${index}.title`] }">
                    <img :src="photo.path" :alt="`Imagen ${index + 1}`"
                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                    <div
                        class="absolute inset-0 bg-black/50 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-4">
                        <slot name="actions" :index="index" :photo="photo" />
                    </div>
                    <div class="absolute top-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
                        {{ index + 1 }}/{{ photos?.length }}
                    </div>
                    <div class="absolute bottom-0 left-0 w-full bg-black/40 text-white p-2 text-xs sm:text-sm">
                        <p class="font-semibold truncate" :class="{ 'text-red-600': errors[`photos.${index}.title`] }">
                            {{ photo.title }}
                        </p>
                        <p class="opacity-80 not-hover:truncate hover:line-clamp-2"
                            :class="{ 'text-red-600': errors[`photos.${index}.description`] }">
                            {{ photo.description ?? 'Sin descripción' }}
                        </p>
                        <p :class="{ 'text-red-600': errors[`photos.${index}.file`] }">
                            {{ errors[`photos.${index}.file`] }}
                        </p>
                    </div>
                </div>
            </swiper-slide>
        </swiper>
        <CardBoxComponentEmpty v-else-if="showEmpty" />
    </div>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Pagination, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import { computed } from 'vue';
import CardBoxComponentEmpty from './CardBoxComponentEmpty.vue';

const props = defineProps({
    photos: {
        type: Object,
        default: () => ({}),
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    slides: {
        type: Number,
        default: 3,
    },
    showEmpty: Boolean
});

const breakpoints = computed(() => {
    const maxSlides = props.slides
    const mediumSlides = Math.max(2, Math.ceil(maxSlides / 2))
    const space = (value) => (value <= 2 ? 20 : 40)

    return {
        '@0.00': {
            slidesPerView: 1,
            spaceBetween: 10
        },
        '@0.75': {
            slidesPerView: mediumSlides,
            spaceBetween: space(mediumSlides)
        },
        '@1.00': {
            slidesPerView: maxSlides,
            spaceBetween: space(maxSlides)
        }
    }
})
</script>