<script setup>
import { containerMaxW } from "@/config.js";

</script>

<template>
  <section class="px-4 md:py-6 md:px-8 mx-auto" :class="containerMaxW">
    <slot />
  </section>
</template>
