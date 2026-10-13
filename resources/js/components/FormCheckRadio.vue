<script setup>
import { computed } from "vue";

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: "checkbox",
    validator: (value) => ["checkbox", "radio", "switch"].includes(value),
  },
  label: {
    type: String,
    default: null,
  },
  modelValue: {
    type: [Array, String, Number, Boolean],
    default: null,
  },
  inputValue: {
    type: [String, Number, Boolean],
    required: true,
  },
  labelClass: String,
  componentClass: String,
});

const emit = defineEmits(["update:modelValue"]);

const computedValue = computed({
  get: () => props.modelValue,
  set: (value) => {
    emit("update:modelValue", value);
  },
});

const inputType = computed(() =>
  props.type === "radio" ? "radio" : "checkbox"
);

const classes = computed(() => {
  return [
    props.type,
    props.componentClass,
  ];
});
</script>

<template>
  <label :class="classes">
    <div>
      <input v-model="computedValue" :type="inputType" :name="name" :value="inputValue" />
      <span class="check" />
    </div>
    <span class="pl-2" :class="labelClass">{{ label }}</span>
  </label>
</template>
