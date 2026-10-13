<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import TeacherLayout from '@/layouts/TeacherLayout.vue'
import EvaluatorLayout from '@/layouts/EvaluatorLayout.vue'
import MultiRoleLayout from '@/layouts/MultiRoleLayout.vue'

const page = usePage()

const layouts = {
  LayoutAuthenticated,
  AdminLayout,
  EvaluatorLayout,
  TeacherLayout,
  MultiRoleLayout,
}

const currentLayout = computed(() => {
  const layoutName = page.props.auth.layoutName
  const layoutMap = {
    'Docente': TeacherLayout,
    'Evaluador': EvaluatorLayout,
    'LayoutAuthenticated': LayoutAuthenticated,
    'AdminLayout': AdminLayout,
    'MultiRoleLayout': MultiRoleLayout,
  }
  return layoutMap[layoutName] || layouts[layoutName] || LayoutAuthenticated
})
</script>

<template>
  <component :is="currentLayout">
    <slot />
  </component>
</template>
