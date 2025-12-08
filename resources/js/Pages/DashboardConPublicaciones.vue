<template>
  <!-- Ejemplo de integración en Dashboard.vue o similar -->
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Explorar Publicaciones
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Encabezado con filtros -->
        <div class="mb-8 bg-white rounded-lg shadow p-6">
          <h1 class="text-2xl font-bold mb-4">Publicaciones Disponibles</h1>
          <div class="flex gap-4 flex-wrap">
            <select v-model="selectedCategory" class="px-4 py-2 border border-gray-300 rounded-lg">
              <option value="">Todas las categorías</option>
              <option v-for="cat in categories" :key="cat.Cod_Categoria" :value="cat.Cod_Categoria">
                {{ cat.Nombre_Categoria }}
              </option>
            </select>
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Buscar publicación..."
              class="px-4 py-2 border border-gray-300 rounded-lg flex-1"
            />
          </div>
        </div>

        <!-- Grid de publicaciones con PublicacionModal -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <PublicacionModal
            v-for="publicacion in filteredPublicaciones"
            :key="publicacion.id"
            :publicacion="publicacion"
            @favorite="handleFavorite"
            @report="handleReport"
            @contact="handleContact"
          />
        </div>

        <!-- Sin resultados -->
        <div v-if="filteredPublicaciones.length === 0" class="text-center py-12">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="mx-auto h-12 w-12 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
            />
          </svg>
          <h3 class="mt-2 text-lg font-medium text-gray-900">
            No hay publicaciones disponibles
          </h3>
          <p class="mt-1 text-sm text-gray-500">
            Intenta cambiar los filtros o busca otro término
          </p>
        </div>

        <!-- Cargando -->
        <div v-if="isLoading" class="text-center py-12">
          <p class="text-gray-500">Cargando publicaciones...</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PublicacionModal from '@/Components/PublicacionModal.vue'

const publicaciones = ref([])
const categories = ref([])
const selectedCategory = ref('')
const searchTerm = ref('')
const isLoading = ref(true)

// Cargar publicaciones al montar
onMounted(async () => {
  try {
    const response = await fetch('/api/publicaciones')
    publicaciones.value = await response.json()
    
    // Extraer categorías únicas
    const uniqueCats = new Map()
    publicaciones.value.forEach(pub => {
      if (pub.categoria && !uniqueCats.has(pub.categoria.Cod_Categoria)) {
        uniqueCats.set(pub.categoria.Cod_Categoria, pub.categoria)
      }
    })
    categories.value = Array.from(uniqueCats.values())
  } catch (error) {
    console.error('Error al cargar publicaciones:', error)
  } finally {
    isLoading.value = false
  }
})

// Publicaciones filtradas
const filteredPublicaciones = computed(() => {
  return publicaciones.value.filter(pub => {
    const matchCategory = !selectedCategory.value || pub.categoria?.Cod_Categoria === parseInt(selectedCategory.value)
    const matchSearch = !searchTerm.value || 
      pub.titulo.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      pub.descripcion.toLowerCase().includes(searchTerm.value.toLowerCase())
    
    return matchCategory && matchSearch
  })
})

// Manejadores de eventos
const handleFavorite = (pub) => {
  console.log('Agregado a favoritos:', pub)
  // Aquí implementarías la lógica para guardar en favoritos
}

const handleReport = (pub) => {
  console.log('Reportar publicación:', pub)
  // Aquí implementarías la lógica para reportar
}

const handleContact = (pub) => {
  console.log('Contactar vendedor de:', pub)
  // Aquí implementarías la lógica para contactar
}
</script>

<style scoped>
</style>
