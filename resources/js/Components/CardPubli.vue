<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: 'CARD'
  },
  subtitle: {
    type: String,
    default: ''
  },
  image: {
    type: String,
    default: null,
  }
});

const imageStyle = computed(() => {
  return props.image ? { backgroundImage: 'url(' + props.image + ')' } : null;
});
</script>

<template>
  <!-- From Uiverse.io by bhaveshxrawat -->
  <div class="card">
    <div v-if="props.image" class="image" :style="imageStyle"></div>
    <div class="content">
      <h2>{{ title }}</h2>
      <p v-if="subtitle" class="subtitle">{{ subtitle }}</p>
    </div>
  </div>
</template>

<style scoped>
/* From Uiverse.io by bhaveshxrawat */
.card {
  width: 190px;
  height: 254px;
  background: #07182E;
  position: relative;
  display: flex;
  place-content: center;
  place-items: center;
  overflow: hidden;
  border-radius: 20px;
}

.card .content {
  z-index: 1;
  text-align: center;
}

.card h2 {
  z-index: 1;
  color: white;
  font-size: 1.25rem;
  margin: 0;
}

.card .subtitle {
  color: #cbd5e1;
  font-size: 0.9rem;
  margin-top: 6px;
}

.card::before {
  content: '';
  position: absolute;
  width: 100px;
  background-image: linear-gradient(180deg, rgb(0, 183, 255), rgb(255, 48, 255));
  height: 130%;
  animation: rotBGimg 3s linear infinite;
  transition: all 0.2s linear;
}

@keyframes rotBGimg {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.card::after {
  content: '';
  position: absolute;
  /* overlay ligeramente translúcido para dejar ver la imagen */
  background: rgba(7, 24, 46, 0.6);
  inset: 5px;
  border-radius: 15px;
}

.card .image {
  position: absolute;
  inset: 5px;
  border-radius: 15px;
  background-position: center;
  background-size: cover;
  z-index: 0;
}

.card .content {
  z-index: 2;
}

</style>
