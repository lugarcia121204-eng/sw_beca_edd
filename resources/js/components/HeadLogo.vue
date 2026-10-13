<script setup>
import { IMAGES } from "@/utils/image";
import { Head } from "@inertiajs/vue3"

defineProps({
  title: {
    type: String,
    default: 'page'
  }
})

</script>

<template>
    <Head :title="title">
        <link rel="shortcut icon" type="image/png" :href="IMAGES.faviconLogo.src">
    </Head>
</template>
