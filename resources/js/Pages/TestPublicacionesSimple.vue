<template>
  <div>
    <h1>Test Simple - PublicacionModal</h1>
    
    <!-- Datos de prueba -->
    <div style="margin-bottom: 20px;">
      <h2>Publicaciones cargadas: {{ publicaciones.length }}</h2>
      <button @click="loadPublicaciones" style="padding: 10px 20px; font-size: 16px;">
        Cargar Publicaciones
      </button>
    </div>

    <!-- Grid -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
      <PublicacionModal
        v-for="pub in publicaciones"
        :key="pub.id"
        :publicacion="pub"
      />
    </div>

    <!-- Debug -->
    <details style="margin-top: 40px;">
      <summary>Debug Info</summary>
      <pre>{{ JSON.stringify(publicaciones, null, 2) }}</pre>
    </details>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PublicacionModal from '@/Components/PublicacionModal.vue'

const publicaciones = ref([])

const loadPublicaciones = async () => {
  try {
    console.log('Cargando...')
    const response = await fetch('/api/publicaciones')
    console.log('Status:', response.status)
    const data = await response.json()
    console.log('Data:', data)
    publicaciones.value = data
  } catch (error) {
    console.error('Error:', error)
    alert('Error: ' + error.message)
  }
}

// Auto-cargar al montar
onMounted(() => {
  loadPublicaciones()
})
</script>

<style>
body {
  font-family: Arial, sans-serif;
  padding: 20px;
}
</style>
