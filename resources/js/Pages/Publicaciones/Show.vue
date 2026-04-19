<template>
  <AppLayout :title="publicacion.Titulo_Publicacion">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center gap-2 text-brand-500 dark:text-brand-400 font-black tracking-widest text-[10px] uppercase">
          <Link :href="route('dashboard')" class="hover:underline">Marketplace</Link>
          <ChevronRight class="w-3 h-3" />
          <span>Detalles del Producto</span>
        </div>
        <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">
          {{ publicacion.Titulo_Publicacion }}
        </h1>
      </div>
    </template>

    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        
        <!-- Galería de Imágenes (Izquierda/Centro) -->
        <div class="lg:col-span-2 space-y-6">
          <div class="relative aspect-[16/10] overflow-hidden rounded-[3rem] bg-gray-100 dark:bg-black/40 border border-light-border dark:border-dark-border shadow-2xl group">
            <img 
              v-if="publicacion.Imagen_Publicacion" 
              :src="getImageUrl(publicacion.Imagen_Publicacion)" 
              :alt="publicacion.Titulo_Publicacion" 
              class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
            />
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-4">
              <CameraOff class="w-16 h-16 opacity-20" />
              <span class="font-bold tracking-widest uppercase text-xs">Sin imagen disponible</span>
            </div>

            <!-- Badge de Precio flotante -->
            <div class="absolute bottom-8 right-8">
              <div class="px-8 py-4 bg-brand-600 dark:bg-brand-500 text-white rounded-3xl shadow-2xl backdrop-blur-md border border-white/20">
                <span class="text-3xl font-black">{{ formatPrice(publicacion.Precio_Publicacion) }}</span>
              </div>
            </div>
          </div>

          <!-- Detalles / Descripción -->
          <div class="p-10 bg-white/50 dark:bg-dark-surface/50 backdrop-blur-xl border border-light-border dark:border-dark-border rounded-[3rem]">
            <div class="flex items-center gap-3 mb-6">
              <div class="p-2 rounded-xl bg-brand-500/10 border border-brand-500/20">
                <FileText class="w-5 h-5 text-brand-600" />
              </div>
              <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Descripción del Producto</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line text-lg font-medium">
              {{ publicacion.Descripcion_Publicacion }}
            </p>

            <div v-if="publicacion.forum_link" class="mt-10 pt-8 border-t border-light-border dark:border-dark-border">
                <a :href="publicacion.forum_link" target="_blank" class="inline-flex items-center gap-3 px-6 py-3 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold rounded-2xl hover:scale-105 transition-transform">
                    <MessageSquare class="w-5 h-5" />
                    Ver foro de discusión
                </a>
            </div>
          </div>
        </div>

        <!-- Panel Lateral (Derecha) -->
        <div class="space-y-8">
          <!-- Card de Vendedor -->
          <div class="p-8 bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[3rem] shadow-xl shadow-black/5">
            <div class="flex flex-col items-center text-center mb-8">
              <div class="relative w-24 h-24 mb-4">
                <img 
                  v-if="publicacion.vendedor?.user?.profile_photo_url"
                  :src="publicacion.vendedor.user.profile_photo_url"
                  class="w-full h-full rounded-3xl object-cover ring-4 ring-brand-500/10 shadow-lg"
                />
                <div v-else class="w-full h-full rounded-3xl bg-brand-500 flex items-center justify-center text-3xl font-black text-white shadow-lg">
                  {{ publicacion.vendedor?.user?.name?.charAt(0) }}
                </div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 border-4 border-white dark:border-dark-surface rounded-full"></div>
              </div>
              
              <h4 class="text-lg font-black text-gray-900 dark:text-white">{{ publicacion.vendedor?.user?.name }}</h4>
              <p class="text-[10px] font-black tracking-widest text-gray-400 uppercase">Vendedor Verificado</p>
              
              <div class="mt-4 flex items-center gap-1">
                 <div v-for="i in 5" :key="i" class="w-4 h-4">
                    <Star class="w-full h-full" :class="i <= 4 ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200'" />
                 </div>
                 <span class="ml-2 text-xs font-bold text-gray-500">(24 ventas)</span>
              </div>
            </div>

            <div class="space-y-3">
              <PrimaryButton @click="handleContact" class="w-full">
                <div class="flex items-center justify-center gap-3">
                    <MessageCircle class="w-5 h-5" />
                    Contactar por Chat
                </div>
              </PrimaryButton>

              <div class="grid grid-cols-2 gap-3">
                <SecondaryButton @click="toggleFavorite" class="!px-0">
                  <Heart class="w-5 h-5 mx-auto" :class="isFavorite ? 'fill-rose-500 text-rose-500' : ''" />
                </SecondaryButton>
                <SecondaryButton @click="sharePublication" class="!px-0">
                  <Share2 class="w-5 h-5 mx-auto" />
                </SecondaryButton>
              </div>

              <button @click="handleReport" class="w-full py-4 text-[10px] font-black tracking-widest text-gray-400 uppercase hover:text-rose-500 transition-colors">
                ⚠️ Reportar esta publicación
              </button>
            </div>
          </div>

          <!-- Información Adicional -->
          <div class="p-8 bg-brand-500/5 border border-brand-500/10 rounded-[2.5rem]">
            <h5 class="text-xs font-black tracking-widest text-brand-600 dark:text-brand-400 uppercase mb-4">Detalles Técnicos</h5>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500">Categoría</span>
                <span class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ publicacion.categoria?.Nombre_Categoria || 'General' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500">Publicado</span>
                <span class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ formatDate(publicacion.created_at) }}</span>
              </div>
               <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500">Estado</span>
                <span class="px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-600 text-[10px] font-black uppercase tracking-tighter border border-emerald-500/20">
                  Activo
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500">Vistas</span>
                <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-white/5 px-2 py-0.5 rounded-lg border border-light-border dark:border-dark-border">
                  <Eye class="w-3 h-3 text-brand-500" />
                  <span class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ publicacion.Vistas_Publicacion || 0 }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección de Mi Publicación (Borrador) -->
          <div v-if="isOwner && publicacion.estado === 'borrador'" class="p-8 bg-emerald-500/5 border border-emerald-500/10 rounded-[2.5rem] animate-pulse">
             <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-4">Esta publicación está en borradores y no es pública aún.</p>
             <button @click="quitarBorrador" class="w-full py-3 bg-emerald-600 text-white rounded-2xl font-black text-[10px] tracking-widest uppercase shadow-lg shadow-emerald-500/20">
                Publicar Ahora
             </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { 
    ChevronRight, 
    MessageCircle, 
    Heart, 
    Share2, 
    Star, 
    FileText, 
    CameraOff,
    MessageSquare,
    Clock,
    Eye
} from 'lucide-vue-next'

const props = defineProps({
  publicacion: { type: Object, required: true },
})

const page = usePage()
const currentUserId = page.props.auth?.user?.id || null
const isOwner = computed(() => props.publicacion?.vendedor?.user?.id === currentUserId)
const isFavorite = ref(false)

const getImageUrl = (imageData) => {
  if (!imageData) return null
  try {
    const parsed = typeof imageData === 'string' ? (imageData.startsWith('[') ? JSON.parse(imageData) : [imageData]) : imageData
    const first = Array.isArray(parsed) ? parsed[0] : parsed
    const filename = String(first).split('/').pop()
    return `/files/publicaciones/${filename}`
  } catch (e) { return null }
}

const formatPrice = (price) => `Bs ${parseFloat(price).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`
const formatDate = (date) => new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })

const handleContact = async () => {
  const sellerId = props.publicacion.vendedor?.user?.id
  if (!sellerId) return
  if (isOwner.value) { alert('No puedes contactar tu propia publicación.'); return }

  try {
    const response = await axios.post('/chats/private', { seller_id: sellerId })
    const chatId = response.data.chat_id
    if (chatId) router.visit(route('chats.show', chatId))
  } catch (err) { alert('Error al iniciar el chat') }
}

const toggleFavorite = () => {
  if (isOwner.value) return alert('No puedes marcar tu propia publicación como favorita')
  isFavorite.value = !isFavorite.value
  router.post(route('favoritos.toggle', props.publicacion.id), {}, {
    onError: () => { isFavorite.value = !isFavorite.value; alert('Error al actualizar favorito') }
  })
}

const sharePublication = async () => {
  const url = window.location.href
  await navigator.clipboard.writeText(url)
  alert('Enlace copiado al portapapeles')
}

const handleReport = () => {
  if (!confirm('¿Deseas reportar esta publicación?')) return
  router.post(route('report.publicacion', props.publicacion.id), {}, {
    onSuccess: () => alert('Reporte enviado'),
  })
}

const quitarBorrador = () => {
  if (!confirm('¿Deseas publicar esta oferta?')) return
  router.patch(route('publicaciones.active', props.publicacion.id), {}, {
    onSuccess: () => router.visit(route('dashboard'))
  })
}
</script>
