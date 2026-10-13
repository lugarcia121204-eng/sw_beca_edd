<script setup>
import { messageConfirm } from "@/Hooks/useErrorsForm";
import { computed } from "vue";

const props = defineProps({
  as: {
    type: String,
    default: "td",
  },
  type: {
    type: String,
    default: "checkbox",
    validator: (value) => ["checkbox", "radio", "switch"].includes(value),
  },
  confirm: {
    type: Boolean,
    default: false,
  },
  confirmMessage: {
    type: String,
    default: "Puedes revertir el valor más tarde",
  },
});

const model = defineModel({ default: false });
const emit = defineEmits(['confirm']);

const checkedValue = computed(() => model.value);

const inputType = computed(() =>
  props.type === "radio" ? "radio" : "checkbox"
);


const handleClick = async (event) => {
  event.preventDefault();

  if (!props.confirm) {
    model.value = !model.value;
    return;
  }

  const result = await messageConfirm(props.confirmMessage);
  if (!result.isConfirmed) return;

  const success = await emitAsync("confirm", !model.value);

  if (success) {
    model.value = !model.value;
  }
};

const emitAsync = (event, payload) => {
  return new Promise((resolve) => {
    emit(event, payload, resolve);
  });
};

</script>

<template>
  <component :is="as" class="lg:w-1">
    <label :class="type" @click="handleClick($event)">
      <input :checked="checkedValue" :type="inputType" />
      <span class="check" />
    </label>
  </component>
</template>
