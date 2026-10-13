<script setup>

import { defineProps } from "vue";

const props = defineProps({
    name: "Index",
    titulo: {
        type: String,
        required: true,
    },

    isDarkMode: {
        type: Boolean,
        default: false, 
    },
    estancias: {
        type: Object,
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    loadingResults: { type: Boolean, required: true, default: true },
    search: { type: String, required: true },
    status: { type: Boolean, required: true, default: true },
});
</script>

<template>
<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-300 dark:border-gray-600">
    <h1 class="text-2xl font-bold mb-8 text-center text-gray-900 dark:text-gray-100 flex items-center justify-center">
      <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.969 9.969 0 0012 21c2.21 0 4.253-.715 5.878-1.923M15 10l4.879 4.879a9.969 9.969 0 001.122-5.879c0-5.523-4.477-10-10-10S2 4.477 2 10c0 2.21.715 4.253 1.923 5.878L15 10z"></path></svg>
      Información Personal
    </h1>
    <div v-for="item in estancias.data" :key="item.id" class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div>
        <label for="zip_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código Postal</label>
        <div class="relative">
          <input 
            type="text" 
            id="zip_code" 
            aria-label="Código Postal" 
            class="mt-1 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg shadow-md focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 pl-10 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500" 
            :value="item.zip_code" 
            disabled
          >
        </div>
      </div>
      <div>
        <label for="institution_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Institución</label>
        <div class="relative">
          <input 
            type="text" 
            id="institution_name" 
            aria-label="Nombre de la Institución" 
            class="mt-1 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg shadow-md focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 pl-10 cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500" 
            :value="item.institutions_name" 
            disabled 
            readonly
          >
        </div>
      </div>
    </div>
  </div>
</template>