<script setup>
import { containerMaxW } from "@/config.js";
import BaseLevel from "@/components/BaseLevel.vue";
import { Link } from "@inertiajs/vue3";

const year = new Date().getFullYear();
</script>

<template>
  <footer class="py-2 px-6" :class="containerMaxW">
    <BaseLevel>
      <div class="text-center md:text-left">
        <b>&copy;{{ year }},
          <Link href="dashboard">PIVITMor</Link>.
        </b>
        <slot />
      </div>
    </BaseLevel>
  </footer>
</template>

