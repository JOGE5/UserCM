<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: [String, Number],
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="w-full px-5 py-3.5 bg-gray-50 dark:bg-black/20 border border-light-border dark:border-dark-border text-gray-900 dark:text-white rounded-2xl placeholder-gray-400 focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-all duration-300"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
