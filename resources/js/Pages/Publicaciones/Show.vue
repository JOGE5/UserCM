<template>
  <AppLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ publicacion.Titulo_Publicacion }}</h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Imagen / Visual principal -->
              <div class="col-span-1 lg:col-span-2">
                <img v-if="publicacion.Imagen_Publicacion" :src="getImageUrl(publicacion.Imagen_Publicacion)" :alt="publicacion.Titulo_Publicacion" class="w-full h-auto object-cover rounded" />
                <div v-else class="w-full h-64 bg-gray-100 rounded flex items-center justify-center">📷 Sin imagen</div>
              </div>

              <!-- Panel derecho: info y acciones (mantiene funcionalidades del modal) -->
              <div class="col-span-1">
                <div class="space-y-4">
                  <!-- DEBUG: mostrar valor crudo de Imagen_Publicacion -->
                  <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded">
                    <strong>DEBUG Imagen_Publicacion:</strong>
                    <pre style="white-space:pre-wrap;">{{ publicacion.Imagen_Publicacion }}</pre>
                  </div>
                  <div>
                    <div class="text-sm text-gray-500">Publicado por: {{ publicacion.vendedor?.user?.name || 'Vendedor' }}</div>
                    <div class="text-2xl font-bold text-gray-900 mt-2">{{ formatPrice(publicacion.Precio_Publicacion) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Categoría: {{ publicacion.categoria?.Nombre_Categoria || 'Categoría' }}</div>
                  </div>

                  <!-- Sección de Calificación (NO para propietario) -->
                  <div v-if="!isOwner" class="bg-yellow-50 border border-yellow-200 p-4 rounded">
                    <h4 class="font-semibold">⭐ Calificar al vendedor</h4>
                    <div class="text-sm text-yellow-800 mt-2">Te recomendamos calificar luego de interactuar con el vendedor.</div>

                      <div class="mt-3">
                        <div class="rating">
                          <input type="radio" :id="`star-5-${publicacion.id}`" :name="`star-radio-${publicacion.id}`" value="5" v-model.number="selectedRating" />
                          <label :for="`star-5-${publicacion.id}`">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
                          </label>
                          <input type="radio" :id="`star-4-${publicacion.id}`" :name="`star-radio-${publicacion.id}`" value="4" v-model.number="selectedRating" />
                          <label :for="`star-4-${publicacion.id}`">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
                          </label>
                          <input type="radio" :id="`star-3-${publicacion.id}`" :name="`star-radio-${publicacion.id}`" value="3" v-model.number="selectedRating" />
                          <label :for="`star-3-${publicacion.id}`">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
                          </label>
                          <input type="radio" :id="`star-2-${publicacion.id}`" :name="`star-radio-${publicacion.id}`" value="2" v-model.number="selectedRating" />
                          <label :for="`star-2-${publicacion.id}`">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
                          </label>
                          <input type="radio" :id="`star-1-${publicacion.id}`" :name="`star-radio-${publicacion.id}`" value="1" v-model.number="selectedRating" />
                          <label :for="`star-1-${publicacion.id}`">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
                          </label>
                        </div>

                        <p v-if="selectedRating" class="text-sm text-gray-700 mt-2">Has seleccionado: {{ selectedRating }} {{ selectedRating === 1 ? 'estrella' : 'estrellas' }}</p>

                        <button v-if="selectedRating > 0" @click="submitRating" :disabled="ratingSubmitting" class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded font-semibold transition">
                          {{ ratingSubmitting ? 'Enviando...' : '✓ Enviar Calificación' }}
                        </button>
                      </div>
                    </div>

                    <!-- Botones de Acción: Contactar, Favorito, Reportar, Compartir -->
                    <div class="space-y-2">
                      <button @click="handleContact" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold transition">💬 Contactar</button>
                      <button @click="toggleFavorite" :class="isFavorite ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-white border border-gray-300 text-gray-700'" class="w-full py-2 rounded font-semibold hover:opacity-80 transition">
                        {{ isFavorite ? '❤️ Favorito' : '🤍 Favorito' }}
                      </button>
                      <button @click="handleReport" class="w-full bg-yellow-100 hover:bg-yellow-200 border border-yellow-300 text-yellow-800 py-2 rounded font-semibold transition">⚠️ Reportar</button>
                      <button @click="sharePublication" class="w-full bg-gray-100 hover:bg-gray-200 border border-gray-300 py-2 rounded font-semibold transition">🔗 Compartir</button>
                      <!-- Botón para quitar borrador si es propietario y está en estado 'borrador' -->
                      <button v-if="isOwner && props.publicacion.estado === 'borrador'" @click="quitarBorrador" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded font-semibold transition">📤 Quitar borrador</button>
                    </div>

                  <div class="mt-4 border-t pt-4">
                    <h4 class="font-semibold">Descripción</h4>
                    <p class="text-gray-700 mt-2">{{ publicacion.Descripcion_Publicacion }}</p>
                  </div>

                  <!-- Foros / comments link (mantener funcionalidad relacionada a foros) -->
                  <div class="mt-4">
                    <Link v-if="publicacion.forum_link" :href="publicacion.forum_link" class="text-blue-600 underline">Ir al foro relacionado</Link>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { usePage, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  // Inertia will provide 'publicacion' prop from the controller
  publicacion: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([])

const page = usePage()
const currentUserId = page.props.currentUserId || page.props.auth?.user?.id || null

const isOwner = computed(() => {
  return !!(props.publicacion?.vendedor?.user?.id && props.publicacion.vendedor.user.id === currentUserId)
})

const selectedRating = ref(0)
const ratingSubmitting = ref(false)
const isFavorite = ref(false)

const getImageUrl = (imageData) => {
  try {
    if (!imageData) return '/images/placeholder.png'

    let first = null

    // Si viene como JSON array en string, parsear
    if (typeof imageData === 'string') {
      try {
        const parsed = JSON.parse(imageData)
        if (Array.isArray(parsed) && parsed.length) first = parsed[0]
        else first = imageData
      } catch (e) {
        first = imageData
      }
    } else if (Array.isArray(imageData)) {
      first = imageData[0]
    } else {
      first = String(imageData)
    }

    // Extraer nombre de archivo y usar la ruta pública que sirve desde routes/web.php
    const parts = String(first).split('/')
    const filename = parts[parts.length - 1]
    if (!filename) return '/images/placeholder.png'
    return `/files/publicaciones/${filename}`
  } catch (e) {
    return '/images/placeholder.png'
  }
}

onMounted(() => {
  try {
    console.log('Publicaciones/Show mounted - Imagen_Publicacion:', props.publicacion?.Imagen_Publicacion)
  } catch (e) {
    console.error('Error logging Imagen_Publicacion', e)
  }
})

const formatPrice = (price) => {
  return `$${parseFloat(price).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`
}

const sharePublication = async () => {
  try {
    const pubId = props.publicacion?.id || props.publicacion?.ID_Publicacion || null
    if (!pubId) { alert('No se pudo obtener la URL de la publicación.'); return }
    const url = `${window.location.origin}/publicaciones/${pubId}`
    if (navigator.clipboard && navigator.clipboard.writeText) {
      await navigator.clipboard.writeText(url)
      alert('Enlace copiado al portapapeles')
    } else {
      const el = document.createElement('textarea')
      el.value = url
      document.body.appendChild(el)
      el.select()
      document.execCommand('copy')
      document.body.removeChild(el)
      alert('Enlace copiado al portapapeles')
    }
  } catch (e) { console.error('Share error:', e); alert('No se pudo copiar el enlace') }
}

const handleContact = async () => {
  const sellerId = props.publicacion.vendedor?.user?.id
  if (!sellerId) {
    alert('No se puede contactar: vendedor no disponible')
    return
  }

  try {
    // Usar axios para aprovechar headers por defecto (CSRF ya configurado en bootstrap.js)
    const response = await axios.post('/chats/private', { seller_id: sellerId })

    if (!response || !response.data) {
      alert('No se pudo iniciar el chat: respuesta vacía')
      return
    }

    const chatId = response.data.chat_id
    if (chatId) {
      // navegar usando Inertia. Usar opciones de callbacks para evitar estados inconsistentes
      let url = `/chats/${chatId}`
      try {
        if (typeof route === 'function') url = route('chats.show', chatId)
      } catch (e) {
        // ignore
      }

      try {
        router.visit(url, {
          onStart: () => {
            // opcional: mostrar indicador visual si hace falta
            console.log('Iniciando navegación a chat', chatId)
          },
          onError: (errors) => {
            console.error('Error en visit:', errors)
            alert('No se pudo abrir el chat (error en la navegación).')
          },
          onFinish: () => {
            console.log('Navegación a chat finalizada')
          }
        })
      } catch (visitErr) {
        console.error('router.visit error', visitErr)
        // fallback a navegación completa
        window.location.href = url
      }
    } else {
      alert('Chat creado pero no se recibió ID')
    }
  } catch (err) {
    console.error('contact error', err)
    // intento de obtener mensaje de respuesta útil
    if (err.response && err.response.data) {
      const msg = err.response.data.message || JSON.stringify(err.response.data)
      alert('No se pudo iniciar el chat: ' + msg)
    } else if (err.message) {
      alert('Error al iniciar el chat: ' + err.message)
    } else {
      alert('Error al iniciar el chat')
    }
  }
}

const toggleFavorite = () => {
  if (!props.publicacion || !props.publicacion.id) return

  // No permitir favoritos en publicaciones propias
  if (isOwner.value) {
    alert('No puedes marcar tu propia publicación como favorita')
    return
  }

  isFavorite.value = !isFavorite.value
  // enviar al backend
  try {
    router.post(route('favoritos.toggle', props.publicacion.id), {}, {
      onSuccess: () => {
        // ya actualizado localmente
      },
      onError: () => {
        isFavorite.value = !isFavorite.value
        alert('Error al actualizar favorito')
      }
    })
  } catch (e) {
    console.error(e)
  }
}

const handleReport = () => {
  if (!props.publicacion || !props.publicacion.id) return
  if (!confirm('¿Deseas reportar esta publicación?')) return

  try {
    router.post(route('report.publicacion', props.publicacion.id), {}, {
      onSuccess: () => alert('Reporte enviado'),
      onError: () => alert('No se pudo enviar el reporte'),
    })
  } catch (e) {
    console.error(e)
    alert('Error al enviar reporte')
  }
}

const submitRating = async () => {
  if (selectedRating.value === 0) return
  const userId = props.publicacion.vendedor?.user?.id
  if (!userId) { alert('No se puede calificar. Vendedor no disponible.'); return }

  ratingSubmitting.value = true
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
    const response = await fetch(`/api/reputacion/${userId}`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken || '' },
      body: JSON.stringify({ Puntuacion: selectedRating.value }),
    })

    const contentType = response.headers.get('content-type') || ''
    if (!response.ok) {
      if (contentType.includes('application/json')) {
        const err = await response.json()
        console.error('Error response:', err)
        alert('Error: ' + (err.error || err.message || 'No se pudo enviar la calificación'))
      } else {
        const text = await response.text()
        console.error('Error non-json response:', text)
        alert('Error al enviar la calificación (respuesta no JSON). Revisa la consola.')
      }
      return
    }

    if (contentType.includes('application/json')) {
      const data = await response.json()
      // actualizar estado local si el backend devuelve nueva info
      if (data.reputacion_estado) {
        if (props.publicacion && props.publicacion.vendedor && props.publicacion.vendedor.user) {
          props.publicacion.vendedor.user.reputacionEstado = data.reputacion_estado
        }
      }
      alert('Calificación enviada')
      selectedRating.value = 0
    } else {
      const text = await response.text()
      console.warn('Success but non-json response:', text)
      alert('Calificación enviada (respuesta inesperada).')
    }
  } catch (e) {
    console.error('Catch error:', e)
    alert('Error de conexión: ' + e.message)
  } finally {
    ratingSubmitting.value = false
  }
}

// Función para quitar borrador (activar publicación)
const quitarBorrador = async () => {
  if (!props.publicacion || !props.publicacion.id) return
  if (!confirm('¿Deseas publicar esta publicación y quitarla de borradores?')) return

  try {
    let url = `/publicaciones/${props.publicacion.id}/active`
    try { if (typeof route === 'function') url = route('publicaciones.active', props.publicacion.id) } catch (e) {}

    // Usar fetch/inertia via router.patch
    router.patch(url, {}, {
      onStart: () => console.log('quitarBorrador start', url),
      onSuccess: () => {
        try { if (typeof route === 'function') { router.visit(route('dashboard')) } else { router.visit('/dashboard') } } catch (e) { window.location.href = '/dashboard' }
      },
      onError: (err) => { console.error('quitarBorrador error', err); alert('No se pudo activar la publicación.') }
    })

  } catch (e) {
    console.error('quitarBorrador unexpected', e)
    alert('Ocurrió un error inesperado')
  }
}
</script>

<style scoped>
/* Pequeños ajustes visuales para la página */
img.rounded { border-radius: 8px; }

/* Estrellas animadas */
.rating {
  display: flex;
  flex-direction: row-reverse;
  gap: 0.3rem;
  --stroke: #666;
  --fill: #ffc73a;
  margin: 12px 0;
}
.rating input { appearance: none; }
.rating label { cursor: pointer; }
.rating svg { width: 2rem; height: 2rem; overflow: visible; fill: transparent; stroke: var(--stroke); stroke-linejoin: bevel; stroke-dasharray: 12; animation: idle 4s linear infinite; transition: stroke 0.2s, fill 0.5s; }
@keyframes idle { from { stroke-dashoffset: 24; } }
.rating label:hover svg { stroke: var(--fill); }
.rating input:checked ~ label svg { transition: 0s; animation: idle 4s linear infinite, yippee 0.75s backwards; fill: var(--fill); stroke: var(--fill); stroke-opacity: 0; stroke-dasharray: 0; stroke-linejoin: miter; stroke-width: 8px; }
@keyframes yippee {
  0% { transform: scale(1); fill: var(--fill); fill-opacity: 0; stroke-opacity: 1; stroke: var(--stroke); stroke-dasharray: 10; stroke-width: 1px; stroke-linejoin: bevel; }
  30% { transform: scale(0); fill: var(--fill); fill-opacity: 0; stroke-opacity: 1; stroke: var(--stroke); stroke-dasharray: 10; stroke-width: 1px; stroke-linejoin: bevel; }
  30.1% { stroke: var(--fill); stroke-dasharray: 0; stroke-linejoin: miter; stroke-width: 8px; }
  60% { transform: scale(1.2); fill: var(--fill); }
}
</style>
