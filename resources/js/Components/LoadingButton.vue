<template>
  <button
    :disabled="loading || disabled"
    class="loading-button"
    @click="handleClick"
    :aria-busy="loading ? 'true' : 'false'"
  >
    <span class="content" v-show="!loading"><slot /></span>
    <span v-if="loading" class="loader" aria-hidden="true"></span>
  </button>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['click']);

function handleClick(e) {
  if (props.loading || props.disabled) return;
  emit('click', e);
}
</script>

<style scoped>
.loading-button {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: .5rem 1rem;
  border-radius: .375rem;
  border: 1px solid #ccc;
  background: #fff;
  cursor: pointer;
}
.loading-button[disabled] {
  opacity: .6;
  cursor: not-allowed;
}

.loader {
  position: relative;
  width: 20px;
  height: 20px;
  background: linear-gradient(to left, #000, #222);
  border-radius: 50%;
  animation: animate 1s linear infinite;
  z-index: 1;
  display: inline-block;
}
.loader::before {
  content: "8";
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2;
  position: absolute;
  background: white;
  text-align: center;
  color: #000;
  font-weight: 800;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
@keyframes animate {
  0% { transform: rotate(0); }
  50% { transform: rotate(3.1415rad); }
  100% { transform: rotate(720deg); }
}
</style>
