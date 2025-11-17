<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary' // primary, secondary, danger, ghost
  },
  size: {
    type: String,
    default: 'md' // sm, md, lg
  },
  disabled: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'button'
  },
  icon: {
    type: String,
    default: null
  }
});

defineEmits(['click']);
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    @click="$emit('click')"
    :class="[
      'inline-flex items-center justify-center font-medium rounded-lg transition-colors',
      // Sizes
      {
        'px-2 py-1 text-sm': size === 'sm',
        'px-4 py-2 text-base': size === 'md',
        'px-6 py-3 text-lg': size === 'lg',
      },
      // Variants
      {
        'bg-blue-600 text-white hover:bg-blue-700 disabled:bg-blue-400': variant === 'primary',
        'bg-gray-200 text-gray-800 hover:bg-gray-300 disabled:bg-gray-100': variant === 'secondary',
        'bg-red-600 text-white hover:bg-red-700 disabled:bg-red-400': variant === 'danger',
        'bg-transparent text-gray-600 hover:bg-gray-100 disabled:text-gray-400': variant === 'ghost',
      },
      // Disabled state
      { 'opacity-50 cursor-not-allowed': disabled }
    ]"
  >
    <slot />
  </button>
</template>
