<template>
  <div class="flex items-center justify-center flex-col text-center py-24 gap-4 text-gray-500 dark:text-slate-400">
    <img :class="imageClasses" :src="currentVariant.src" :alt="currentVariant.defaultText">
    <p>
      {{ props.message || currentVariant.defaultText }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'empty',
    validator: (value) => ['empty', 'notFound', 'idle'].includes(value)
  },
  message: {
    type: String,
    default: null
  },
  imageClasses: {
    type: String,
    default: 'w-24 h-24'
  }
});

const variants = {
  empty: {
    src: '/img/no_data.svg',
    defaultText: 'No hay nada aquí'
  },
  notFound: {
    src: '/img/match_not_found.svg',
    defaultText: 'No se encontraron coincidencias para tu búsqueda...'
  },
  idle: {
    src: '/img/idle_search.svg',
    defaultText: 'Selecciona filtros o escribe palabras clave para comenzar la búsqueda...'
  }
};

const currentVariant = computed(() => variants[props.type]);
</script>