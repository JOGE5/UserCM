<template>
  <div class="rating-component">
    <div class="stars-container">
      <button
        v-for="star in 5"
        :key="star"
        class="star"
        :class="{ active: star <= hoverRating || star <= modelValue }"
        @click="$emit('update:modelValue', star)"
        @mouseover="hoverRating = star"
        @mouseleave="hoverRating = 0"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="currentColor"
          class="w-6 h-6"
        >
          <path
            d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2l-2.81 6.63L2 9.24l5.46 4.73L5.82 21z"
          />
        </svg>
      </button>
    </div>
    <p class="rating-text">{{ modelValue }} de 5 estrellas</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  modelValue: {
    type: Number,
    default: 0,
  },
})

defineEmits(['update:modelValue'])

const hoverRating = ref(0)
</script>

<style scoped>
.rating-component {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.stars-container {
  display: flex;
  gap: 0.5rem;
}

.star {
  background: none;
  border: none;
  cursor: pointer;
  color: #d1d5db;
  transition: color 0.2s ease;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.star:hover,
.star.active {
  color: #fbbf24;
}

.star svg {
  width: 1.5rem;
  height: 1.5rem;
}

.rating-text {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}
</style>
