<template>
  <div class="reputation-page">
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-8">Sistema de Reputación</h1>

      <!-- Sección de Calificación -->
      <div class="rating-section bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Calificar Usuario</h2>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2">Selecciona tu puntuación:</label>
            <RatingComponent v-model="newRating" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2">Comentario (opcional):</label>
            <textarea
              v-model="comentario"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="4"
              placeholder="Comparte tu experiencia..."
            ></textarea>
          </div>

          <button
            @click="submitRating"
            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition"
            :disabled="newRating === 0 || isLoading"
          >
            {{ isLoading ? 'Enviando...' : 'Enviar Calificación' }}
          </button>
        </div>
      </div>

      <!-- Sección de Reputación del Usuario -->
      <div class="user-reputation-section bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Estado de Reputación</h2>

        <div v-if="userReputation" class="space-y-4">
          <div class="flex items-center gap-4">
            <ReputationBadge :estado-actual="userReputation.reputacion.estado_actual" />
            <div>
              <p class="text-sm text-gray-600">Promedio de calificaciones:</p>
              <p class="text-2xl font-bold">
                {{ (userReputation.promedio_puntuacion || 0).toFixed(2) }}/5
              </p>
            </div>
          </div>

          <div class="grid grid-cols-4 gap-4 mt-4">
            <div class="text-center">
              <div class="text-lg font-semibold">{{ (userReputation.reputacion.p_malo * 100).toFixed(1) }}%</div>
              <div class="text-xs text-gray-600">Malo</div>
            </div>
            <div class="text-center">
              <div class="text-lg font-semibold">{{ (userReputation.reputacion.p_regular * 100).toFixed(1) }}%</div>
              <div class="text-xs text-gray-600">Regular</div>
            </div>
            <div class="text-center">
              <div class="text-lg font-semibold">{{ (userReputation.reputacion.p_bueno * 100).toFixed(1) }}%</div>
              <div class="text-xs text-gray-600">Bueno</div>
            </div>
            <div class="text-center">
              <div class="text-lg font-semibold">{{ (userReputation.reputacion.p_excelente * 100).toFixed(1) }}%</div>
              <div class="text-xs text-gray-600">Excelente</div>
            </div>
          </div>

          <p class="text-sm text-gray-600">
            Total de calificaciones: <strong>{{ userReputation.total_calificaciones }}</strong>
          </p>
        </div>
        <div v-else class="text-gray-600">
          Cargando información de reputación...
        </div>
      </div>

      <!-- Sección de Publicaciones Ordenadas -->
      <div class="publications-section bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Publicaciones por Reputación</h2>

        <div v-if="publicaciones.length > 0" class="space-y-4">
          <div
            v-for="pub in publicaciones"
            :key="pub.id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold">{{ pub.titulo }}</h3>
                <p class="text-gray-600 text-sm mt-1">{{ pub.descripcion.substring(0, 100) }}...</p>
                <p class="text-lg font-bold text-green-600 mt-2">${{ pub.precio }}</p>
              </div>

              <div class="ml-4 text-right">
                <div v-if="pub.vendedor.reputacion">
                  <ReputationBadge :estado-actual="pub.vendedor.reputacion.estado" />
                  <p class="text-xs text-gray-600 mt-2">{{ pub.vendedor.nombre }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-gray-600">
          No hay publicaciones disponibles
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import RatingComponent from '@/Components/RatingComponent.vue'
import ReputationBadge from '@/Components/ReputationBadge.vue'

const newRating = ref(0)
const comentario = ref('')
const isLoading = ref(false)
const userReputation = ref(null)
const publicaciones = ref([])

const submitRating = async () => {
  if (newRating.value === 0) return

  isLoading.value = true
  try {
    const response = await fetch(`/api/reputacion/1`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        Puntuacion: newRating.value,
        Comentario: comentario.value || null,
      }),
    })

    if (response.ok) {
      newRating.value = 0
      comentario.value = ''
      await loadUserReputation()
    }
  } catch (error) {
    console.error('Error al enviar calificación:', error)
  } finally {
    isLoading.value = false
  }
}

const loadUserReputation = async () => {
  try {
    const response = await fetch(`/api/reputacion/1`)
    userReputation.value = await response.json()
  } catch (error) {
    console.error('Error al cargar reputación:', error)
  }
}

const loadPublicaciones = async () => {
  try {
    const response = await fetch('/api/publicaciones')
    publicaciones.value = await response.json()
  } catch (error) {
    console.error('Error al cargar publicaciones:', error)
  }
}

onMounted(() => {
  loadUserReputation()
  loadPublicaciones()
})
</script>

<style scoped>
.reputation-page {
  background-color: #f9fafb;
  min-height: 100vh;
}
</style>
