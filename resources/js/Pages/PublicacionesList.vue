<template>
  <AppLayout title="Publicaciones">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Publicaciones
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Grid de publicaciones -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <PublicacionModal
            v-for="publicacion in publicaciones"
            :key="publicacion.id"
            :publicacion="publicacion"
            @favorite="handleFavorite"
            @report="handleReport"
            @contact="handleContact"
          />
        </div>

        <!-- Sin publicaciones -->
        <div v-if="publicaciones.length === 0" class="text-center py-12">
          <p class="text-gray-500 text-lg">No hay publicaciones disponibles</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PublicacionModal from '@/Components/PublicacionModal.vue'

const publicaciones = ref([])

onMounted(async () => {
  try {
    console.log('Cargando publicaciones...')
    const response = await fetch('/api/publicaciones')
    console.log('Respuesta recibida:', response.status, response.statusText)
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    console.log('Datos recibidos:', data)
    publicaciones.value = data
    console.log('Publicaciones cargadas:', publicaciones.value.length)
  } catch (error) {
    console.error('Error al cargar publicaciones:', error)
  }
})

const handleFavorite = (publicacion) => {
  console.log('Agregado a favoritos:', publicacion)
  // Implementar lógica de favoritos
}

const handleReport = (publicacion) => {
  console.log('Reportar publicación:', publicacion)
  // Implementar lógica de reporte
}

const handleContact = (publicacion) => {
  console.log('Contactar vendedor:', publicacion)
  // Implementar lógica de contacto
}
</script>

<style scoped>
</style>
