<template>
  <div class="rating-container">
    <div class="radio">
      <input
        v-for="star in 5"
        :key="star"
        :value="star"
        name="rating"
        type="radio"
        :id="`rating-${star}`"
        v-model.number="selectedRating"
        @change="submitRating"
      />
      <label :title="`${star} star${star !== 1 ? 's' : ''}`" :for="`rating-${star}`">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512">
          <path
            d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"
          ></path>
        </svg>
      </label>
    </div>
    <p v-if="selectedRating" class="rating-text">{{ selectedRating }} {{ selectedRating === 1 ? 'estrella' : 'estrellas' }}</p>
    <button
      v-if="selectedRating && !isSubmitting"
      @click="submitRating"
      class="submit-btn"
    >
      Enviar Calificación
    </button>
    <button v-else-if="isSubmitting" class="submit-btn loading" disabled>
      Enviando...
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  userId: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['rating-submitted'])

const selectedRating = ref(0)
const isSubmitting = ref(false)

const submitRating = async () => {
  if (selectedRating.value === 0) return

  isSubmitting.value = true

  try {
    const response = await fetch(`/api/reputacion/${props.userId}`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        Puntuacion: selectedRating.value,
        Comentario: '',
      }),
    })

    // Manejar respuestas no-JSON para evitar "Unexpected token '<'"
    const contentType = response.headers.get('content-type') || ''
    if (!response.ok) {
      if (contentType.includes('application/json')) {
        const err = await response.json()
        console.error('Rating error json:', err)
        alert(err.message || err.error || 'Error al enviar la calificación')
      } else {
        const text = await response.text()
        console.error('Rating error text:', text)
        alert('Error al enviar la calificación (respuesta no JSON). Revisa la consola para más detalles.')
      }
      return
    }

    if (contentType.includes('application/json')) {
      const data = await response.json()
      emit('rating-submitted', data)
      selectedRating.value = 0
      alert('¡Calificación enviada exitosamente!')
    } else {
      // Caso raro: 200 pero HTML
      const text = await response.text()
      console.warn('Rating success but non-json response:', text)
      alert('Calificación enviada (respuesta inesperada).')
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error de conexión')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.rating-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 16px 0;
}

.radio {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.radio > input {
  position: absolute;
  appearance: none;
}

.radio > label {
  cursor: pointer;
  font-size: 30px;
  position: relative;
  display: inline-block;
  transition: transform 0.3s ease;
}

.radio > label > svg {
  fill: #666;
  transition: fill 0.3s ease;
  width: 32px;
  height: 32px;
}

.radio > label::before,
.radio > label::after {
  content: "";
  position: absolute;
  width: 6px;
  height: 6px;
  background-color: #ff9e0b;
  border-radius: 50%;
  opacity: 0;
  transform: scale(0);
  transition:
    transform 0.4s ease,
    opacity 0.4s ease;
  animation: particle-explosion 1s ease-out;
}

.radio > label::before {
  top: -15px;
  left: 50%;
  transform: translateX(-50%) scale(0);
}

.radio > label::after {
  bottom: -15px;
  left: 50%;
  transform: translateX(-50%) scale(0);
}

.radio > label:hover::before,
.radio > label:hover::after {
  opacity: 1;
  transform: translateX(-50%) scale(1.5);
}

.radio > label:hover {
  transform: scale(1.2);
  animation: pulse 0.6s infinite alternate;
}

.radio > label:hover > svg {
  fill: #ff9e0b;
  filter: drop-shadow(0 0 15px rgba(255, 158, 11, 0.9));
  animation: shimmer 1s ease infinite alternate;
}

.radio > input:checked + label > svg {
  fill: #ff9e0b;
  filter: drop-shadow(0 0 15px rgba(255, 158, 11, 0.9));
  animation: pulse 0.8s infinite alternate;
}

.radio > input:checked + label ~ label > svg,
.radio > input:checked + label > svg {
  fill: #ff9e0b;
}

.rating-text {
  font-size: 14px;
  color: #666;
  margin: 0;
  font-weight: 500;
}

.submit-btn {
  background-color: #10b981;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
}

.submit-btn:hover:not(.loading) {
  background-color: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.submit-btn.loading {
  background-color: #9ca3af;
  cursor: not-allowed;
  opacity: 0.7;
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(1.1);
  }
}

@keyframes particle-explosion {
  0% {
    opacity: 0;
    transform: scale(0.5);
  }
  50% {
    opacity: 1;
    transform: scale(1.2);
  }
  100% {
    opacity: 0;
    transform: scale(0.5);
  }
}

@keyframes shimmer {
  0% {
    filter: drop-shadow(0 0 10px rgba(255, 158, 11, 0.5));
  }
  100% {
    filter: drop-shadow(0 0 20px rgba(255, 158, 11, 1));
  }
}
</style>
