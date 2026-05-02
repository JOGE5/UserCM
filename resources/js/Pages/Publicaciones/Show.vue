<template>
  <AppLayout :title="publicacion.Titulo_Publicacion">
    <template #header>
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex flex-col gap-2">
          <div class="flex items-center gap-2 text-brand-500 dark:text-brand-400 font-black tracking-widest text-[10px] uppercase">
            <Link :href="route('dashboard')" class="hover:underline">Marketplace</Link>
            <ChevronRight class="w-3 h-3" />
            <span>Detalles del Producto</span>
          </div>
          <h1 class="text-4xl font-black tracking-tight text-gray-900 dark:text-white">
            {{ publicacion.Titulo_Publicacion }}
          </h1>
        </div>
        <div class="flex flex-col items-end">
           <div class="px-6 py-3 bg-brand-600 dark:bg-brand-500 text-white rounded-2xl shadow-xl shadow-brand-500/20 border border-white/20">
              <span class="text-3xl font-black">{{ formatPrice(publicacion.Precio_Publicacion) }}</span>
           </div>
        </div>
      </div>
    </template>

    <div class="max-w-7xl mx-auto pb-20 px-4">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Galería de Imágenes (Izquierda) -->
        <div class="lg:col-span-7 space-y-8">
          <div class="space-y-4">
            <div class="relative aspect-square max-w-2xl mx-auto overflow-hidden rounded-[3.5rem] bg-gray-100 dark:bg-black/40 border-2 border-light-border dark:border-dark-border shadow-2xl group">
              <img 
                v-if="currentSelectedImage" 
                :src="currentSelectedImage" 
                :alt="publicacion.Titulo_Publicacion" 
                class="w-full h-full object-cover transition-all duration-1000 group-hover:scale-105" 
              />
              <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-4">
                <CameraOff class="w-16 h-16 opacity-20" />
                <span class="font-bold tracking-widest uppercase text-xs">Sin imagen disponible</span>
              </div>

              <!-- Badge de Vistas flotante -->
              <div class="absolute top-8 right-8">
                <div class="flex items-center gap-2 px-4 py-2 bg-black/40 backdrop-blur-xl border border-white/20 rounded-2xl text-white">
                  <Eye class="w-4 h-4 text-brand-400" />
                  <span class="text-xs font-black">{{ publicacion.Vistas_Publicacion || 0 }} Vistas</span>
                </div>
              </div>
            </div>

            <!-- Miniaturas -->
            <div v-if="allImages.length > 1" class="flex gap-4 px-2 overflow-x-auto pb-2">
               <button 
                v-for="(img, idx) in allImages" 
                :key="idx"
                @click="selectedIndex = idx"
                class="relative w-24 h-24 rounded-2xl overflow-hidden border-2 transition-all shrink-0"
                :class="selectedIndex === idx ? 'border-brand-500 scale-105 shadow-lg' : 'border-transparent opacity-60 hover:opacity-100'"
               >
                 <img :src="img" class="w-full h-full object-cover" />
               </button>
            </div>
          </div>

          <!-- Detalles / Descripción -->
          <div class="p-10 bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[3.5rem] shadow-sm">
            <div class="flex items-center gap-3 mb-8">
              <div class="p-3 rounded-2xl bg-brand-500/10 border border-brand-500/20">
                <FileText class="w-6 h-6 text-brand-600" />
              </div>
              <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Descripción detallada</h3>
            </div>
            
            <div class="prose prose-indigo dark:prose-invert max-w-none">
              <p class="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line text-xl font-medium">
                {{ publicacion.Descripcion_Publicacion }}
              </p>
            </div>

            <div v-if="publicacion.forum_link" class="mt-12 pt-10 border-t border-light-border dark:border-dark-border">
                <a :href="publicacion.forum_link" target="_blank" class="group inline-flex items-center gap-4 px-8 py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-500 transition-all shadow-xl shadow-indigo-600/20">
                    <MessageSquare class="w-6 h-6" />
                    <span>Participar en el foro de discusión</span>
                    <ChevronRight class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                </a>
            </div>
          </div>
        </div>

        <!-- Panel Lateral (Derecha) -->
        <div class="lg:col-span-5 space-y-8">
          <!-- Card de Vendedor -->
          <div class="sticky top-32 space-y-8">
            <div class="p-8 bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[3rem] shadow-xl shadow-black/5">
              <div class="flex flex-col items-center text-center mb-8">
                <div class="relative w-28 h-28 mb-6">
                  <img 
                    v-if="publicacion.vendedor?.user?.profile_photo_url"
                    :src="publicacion.vendedor.user.profile_photo_url"
                    class="w-full h-full rounded-[2rem] object-cover ring-4 ring-brand-500/10 shadow-2xl"
                  />
                  <div v-else class="w-full h-full rounded-[2rem] bg-brand-500 flex items-center justify-center text-4xl font-black text-white shadow-2xl">
                    {{ publicacion.vendedor?.user?.name?.charAt(0) }}
                  </div>
                  <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-emerald-500 border-4 border-white dark:border-dark-surface rounded-full shadow-lg"></div>
                </div>
                
                <h4 class="text-xl font-black text-gray-900 dark:text-white mb-1">{{ publicacion.vendedor?.user?.name }}</h4>
                <div class="flex items-center gap-1.5 justify-center">
                  <CheckCircle class="w-3.5 h-3.5 text-brand-500" />
                  <p class="text-[10px] font-black tracking-widest text-gray-400 uppercase">Vendedor Verificado</p>
                </div>
                
                <div class="mt-6 flex items-center gap-1 bg-gray-50 dark:bg-white/5 px-4 py-2 rounded-2xl border border-light-border dark:border-dark-border">
                   <div v-for="i in 5" :key="i" class="w-4 h-4">
                      <Star class="w-full h-full" :class="i <= 4 ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200'" />
                   </div>
                   <span class="ml-2 text-xs font-black text-gray-500">(24 ventas)</span>
                </div>
              </div>

              <div class="space-y-4">
                <PrimaryButton @click="handleContact" :disabled="contacting || isOwner" class="w-full !py-5 shadow-2xl shadow-brand-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                  <div class="flex items-center justify-center gap-3">
                      <MessageCircle class="w-6 h-6" :class="contacting ? 'animate-pulse' : ''" />
                      <span class="text-sm font-black tracking-widest">
                        {{ contacting ? 'ABRIENDO CHAT...' : 'CONTACTAR AHORA' }}
                      </span>
                  </div>
                </PrimaryButton>

                <div class="grid grid-cols-2 gap-4">
                  <SecondaryButton @click="toggleFavorite" class="!px-0 !py-4">
                    <Heart class="w-6 h-6 mx-auto" :class="isFavorite ? 'fill-rose-500 text-rose-500' : ''" />
                  </SecondaryButton>
                  <SecondaryButton @click="sharePublication" class="!px-0 !py-4">
                    <Share2 class="w-6 h-6 mx-auto" />
                  </SecondaryButton>
                </div>

                <button @click="handleReport" class="w-full py-4 text-[10px] font-black tracking-widest text-gray-400 uppercase hover:text-rose-500 transition-colors border border-transparent hover:border-rose-500/20 rounded-2xl mt-4">
                  ⚠️ Reportar esta publicación
                </button>
              </div>
            </div>

            <!-- Información Adicional -->
            <div class="p-8 bg-brand-500/5 border border-brand-500/10 rounded-[3rem] space-y-6">
              <h5 class="text-xs font-black tracking-widest text-brand-600 dark:text-brand-400 uppercase">Detalles Técnicos</h5>
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <Layers class="w-4 h-4 text-gray-400" />
                    <span class="text-xs font-bold text-gray-500">Categoría</span>
                  </div>
                  <span class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ publicacion.categoria?.Nombre_Categoria || 'General' }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <Clock class="w-4 h-4 text-gray-400" />
                    <span class="text-xs font-bold text-gray-500">Publicado</span>
                  </div>
                  <span class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ formatDate(publicacion.created_at) }}</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-brand-500/10">
                  <span class="text-xs font-bold text-gray-500">Estado</span>
                  <span class="px-3 py-1 rounded-full bg-emerald-500 text-white text-[9px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20">
                    Disponible
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección de Mi Publicación (Borrador) -->
      <div v-if="isOwner && publicacion.estado === 'borrador'" class="p-8 mt-12 bg-emerald-500/5 border border-emerald-500/10 rounded-[3rem] animate-pulse">
        <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mb-4 text-center">Esta publicación está en borradores y no es pública aún.</p>
        <button @click="quitarBorrador" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-black text-xs tracking-widest uppercase shadow-xl shadow-emerald-500/20">
          Publicar Ahora
        </button>
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
    Eye,
    Layers,
    CheckCircle
} from 'lucide-vue-next'

const props = defineProps({
  publicacion: { type: Object, required: true },
})

const selectedIndex = ref(0)
const allImages = computed(() => {
  if (!props.publicacion.Imagen_Publicacion) return []
  try {
    const ip = props.publicacion.Imagen_Publicacion
    const parsed = typeof ip === 'string' ? (ip.startsWith('[') ? JSON.parse(ip) : [ip]) : ip
    return (Array.isArray(parsed) ? parsed : [parsed]).map(img => {
       const filename = String(img).split('/').pop()
       return `/files/publicaciones/${filename}`
    })
  } catch (e) { return [] }
})

const currentSelectedImage = computed(() => allImages.value[selectedIndex.value] || null)

const route = window.route
const page = usePage()
const currentUserId = page.props.auth?.user?.id || null
const isOwner = computed(() => props.publicacion?.vendedor?.user?.id === currentUserId)
const isFavorite = ref(false)
const contacting = ref(false)

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
  if (contacting.value) return

  contacting.value = true
  try {
    const { data } = await axios.post(route('chats.private.create'), { seller_id: sellerId })
    const { chat_id } = data

    // Obtener imagen principal de la publicación
    const imgRaw = props.publicacion.Imagen_Publicacion
    let imagen = null
    if (imgRaw) {
      try {
        const p = JSON.parse(imgRaw)
        imagen = `/files/publicaciones/${(Array.isArray(p) ? p[0] : p).split('/').pop()}`
      } catch {
        imagen = `/files/publicaciones/${String(imgRaw).split('/').pop()}`
      }
    }

    // Siempre enviar tarjeta del producto al iniciar conversación
    await axios.post(route('chats.messages.store', chat_id), {
      type: 'product_card',
      metadata: {
        publicacion_id: props.publicacion.id,
        titulo:  props.publicacion.Titulo_Publicacion,
        precio:  props.publicacion.Precio_Publicacion,
        imagen,
        url: route('publicaciones.show', props.publicacion.id),
      },
    })

    router.visit(route('chats.show', chat_id))
  } catch {
    alert('Error al iniciar el chat. Intenta de nuevo.')
  } finally {
    contacting.value = false
  }
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
