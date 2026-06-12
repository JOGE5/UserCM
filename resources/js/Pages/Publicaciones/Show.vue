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

    <!-- Banner de moderación (solo visible para el dueño cuando está oculta) -->
    <div v-if="publicacion.estado === 'oculta' && isOwner" class="max-w-7xl mx-auto px-4 pt-6">
      <div class="flex items-start gap-3 p-5 bg-amber-500/10 border border-amber-500/30 rounded-2xl">
        <span class="text-xl shrink-0">⚠️</span>
        <div>
          <p class="text-sm font-black text-amber-700 dark:text-amber-300 uppercase tracking-widest mb-1">Publicación bajo revisión</p>
          <p class="text-xs font-medium text-amber-800 dark:text-amber-200/80 leading-relaxed">
            Esta publicación está bajo revisión por reportes de la comunidad. Solo tú puedes verla.
            El equipo de Campus Market la revisará pronto.
          </p>
        </div>
      </div>
    </div>

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

              <!-- Overlay VENDIDO sobre imagen principal -->
              <div v-if="publicacion.estado === 'vendida'" class="absolute inset-0 z-10 bg-black/50 flex items-center justify-center pointer-events-none rounded-[3.5rem]">
                <span class="-rotate-12 px-16 py-4 bg-emerald-500 border-y-4 border-white/30 text-white text-3xl font-black tracking-widest uppercase shadow-2xl">
                  VENDIDO
                </span>
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
                
                <div class="flex items-center justify-center gap-2 mb-1">
                  <Link
                    :href="route('usuarios.show', publicacion.vendedor?.user?.id)"
                    class="text-xl font-black text-gray-900 dark:text-white hover:text-brand-500 dark:hover:text-brand-400 transition-colors"
                  >
                    {{ publicacion.vendedor?.user?.name }}
                  </Link>
                  <BadgeCheck
                    v-if="publicacion.vendedor?.verificado"
                    class="w-5 h-5 text-sky-500 shrink-0"
                    title="Vendedor verificado"
                  />
                </div>

                <!-- Mini-badge de reputación -->
                <div class="mt-2 mb-3 flex items-center justify-center">
                  <span
                    v-if="publicacion.vendedor?.user?.reputacion_estado?.estado_actual"
                    :class="{
                      'bg-rose-500 border-rose-400':    publicacion.vendedor.user.reputacion_estado.estado_actual === 'Malo',
                      'bg-amber-500 border-amber-400':  publicacion.vendedor.user.reputacion_estado.estado_actual === 'Regular',
                      'bg-emerald-400 border-emerald-300': publicacion.vendedor.user.reputacion_estado.estado_actual === 'Bueno',
                      'bg-emerald-600 border-emerald-500': publicacion.vendedor.user.reputacion_estado.estado_actual === 'Excelente',
                    }"
                    class="px-3 py-1 text-[9px] font-black uppercase tracking-widest text-white rounded-full border shadow-sm"
                  >
                    {{ publicacion.vendedor.user.reputacion_estado.estado_actual }}
                  </span>
                  <span v-else class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Sin reputación</span>
                </div>

                <div class="mt-2 flex items-center gap-1 bg-gray-50 dark:bg-white/5 px-4 py-2 rounded-2xl border border-light-border dark:border-dark-border">
                   <template v-if="promedioVendedor !== null">
                     <div v-for="i in 5" :key="i" class="w-4 h-4">
                       <Star class="w-full h-full" :class="i <= Math.round(promedioVendedor) ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200'" />
                     </div>
                     <span class="ml-2 text-xs font-black text-gray-500">{{ promedioVendedor.toFixed(1) }}/5</span>
                   </template>
                   <template v-else>
                     <div v-for="i in 5" :key="i" class="w-4 h-4">
                       <Star class="w-full h-full text-gray-200 fill-gray-200" />
                     </div>
                     <span class="ml-2 text-xs font-black text-gray-500">Sin calificaciones aún</span>
                   </template>
                </div>
              </div>

              <div class="space-y-4">
                <!-- Publicación vendida: no disponible -->
                <div
                  v-if="publicacion.estado === 'vendida'"
                  class="w-full py-5 text-center text-[10px] font-black tracking-widest uppercase text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl"
                >
                  Esta publicación ya no está disponible
                </div>

                <PrimaryButton
                  v-else
                  @click="handleContact"
                  :disabled="contacting || isOwner"
                  class="w-full !py-5 shadow-2xl shadow-brand-600/30 disabled:opacity-50 disabled:cursor-not-allowed"
                >
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

                <!-- Botón / Badge de venta (solo para el dueño) -->
                <div v-if="isOwner" class="mt-4 pt-4 border-t border-light-border dark:border-dark-border">
                  <!-- Ya está vendida -->
                  <div v-if="publicacion.estado === 'vendida'" class="flex items-center gap-3 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                    <BadgeCheck class="w-6 h-6 text-emerald-500 shrink-0" />
                    <div>
                      <p class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Vendido</p>
                      <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                        {{ publicacion.vendido_at ? formatDate(publicacion.vendido_at) : 'Fecha no registrada' }}
                      </p>
                    </div>
                  </div>
                  <!-- No vendida aún -->
                  <button
                    v-else
                    @click="showVendidoModal = true"
                    class="w-full flex items-center justify-center gap-2 py-4 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black tracking-widest uppercase rounded-2xl shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] active:scale-95"
                  >
                    <ShoppingBag class="w-4 h-4" />
                    Marcar como vendido
                  </button>
                </div>
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
                <div v-if="publicacion.condicion_producto" class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <BadgeCheck class="w-4 h-4 text-gray-400" />
                    <span class="text-xs font-bold text-gray-500">Condición</span>
                  </div>
                  <span :class="[
                    'px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest',
                    publicacion.condicion_producto === 'nuevo'
                      ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400'
                      : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
                  ]">
                    {{ publicacion.condicion_producto === 'nuevo' ? '✨ Nuevo' : '♻ Usado' }}
                  </span>
                </div>
                <div v-if="publicacion.ubicacion" class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <MapPin class="w-4 h-4 text-gray-400" />
                    <span class="text-xs font-bold text-gray-500">Ubicación</span>
                  </div>
                  <span class="text-xs font-black text-gray-800 dark:text-white">{{ publicacion.ubicacion }}</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-brand-500/10">
                  <span class="text-xs font-bold text-gray-500">Estado</span>
                  <span :class="[
                    'px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-lg',
                    publicacion.estado === 'vendida'
                      ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                      : 'bg-brand-500 text-white shadow-brand-500/20'
                  ]">
                    {{ publicacion.estado === 'vendida' ? 'Vendido' : 'Disponible' }}
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

  <!-- Modal: Confirmar venta -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showVendidoModal" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showVendidoModal = false"></div>

        <!-- Panel -->
        <div class="relative z-10 w-full max-w-md bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-2xl p-8">
          <!-- Header -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20">
                <ShoppingBag class="w-6 h-6 text-emerald-600" />
              </div>
              <div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">¿Confirmar venta?</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Acción permanente</p>
              </div>
            </div>
            <button @click="showVendidoModal = false" class="p-2 rounded-xl text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
              <X class="w-5 h-5" />
            </button>
          </div>

          <p class="text-sm text-gray-600 dark:text-gray-400 font-medium leading-relaxed mb-8">
            Esta acción marcará <span class="font-black text-gray-900 dark:text-white">«{{ publicacion.Titulo_Publicacion }}»</span> como vendida. La publicación dejará de aparecer en el marketplace para otros usuarios.
          </p>

          <div class="flex gap-3">
            <button
              @click="showVendidoModal = false"
              class="flex-1 py-4 text-xs font-black tracking-widest uppercase border border-light-border dark:border-dark-border text-gray-600 dark:text-gray-400 rounded-2xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
            >
              Cancelar
            </button>
            <button
              @click="confirmarVendido"
              class="flex-1 py-4 text-xs font-black tracking-widest uppercase bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] active:scale-95"
            >
              Sí, marcar vendido
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- Modal: Confirmar Borrador -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showBorradorModal" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showBorradorModal = false"></div>

        <!-- Panel -->
        <div class="relative z-10 w-full max-w-md bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] shadow-2xl p-8">
          <!-- Header -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20">
                <CheckCircle class="w-6 h-6 text-emerald-600" />
              </div>
              <div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">¿Publicar ahora?</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hacer público</p>
              </div>
            </div>
            <button @click="showBorradorModal = false" class="p-2 rounded-xl text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
              <X class="w-5 h-5" />
            </button>
          </div>

          <p class="text-sm text-gray-600 dark:text-gray-400 font-medium leading-relaxed mb-8">
            Esta acción activará <span class="font-black text-gray-900 dark:text-white">«{{ publicacion.Titulo_Publicacion }}»</span> y será visible para todos en el marketplace.
          </p>

          <div class="flex gap-3">
            <button
              @click="showBorradorModal = false"
              class="flex-1 py-4 text-xs font-black tracking-widest uppercase border border-light-border dark:border-dark-border text-gray-600 dark:text-gray-400 rounded-2xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
            >
              Cancelar
            </button>
            <button
              @click="confirmarBorrador"
              class="flex-1 py-4 text-xs font-black tracking-widest uppercase bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] active:scale-95"
            >
              Sí, publicar
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <ReportModal
    v-if="showReportModal"
    :publicacionId="publicacion.id"
    :ownerId="publicacion.vendedor?.user?.id"
    @close="showReportModal = false"
  />
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import axios from 'axios'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import ReportModal from '@/Components/ReportModal.vue'
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
    MapPin,
    BadgeCheck,
    ShoppingBag,
    CheckCircle,
    X,
} from 'lucide-vue-next'

const props = defineProps({
  publicacion:           { type: Object,  required: true },
  vendedor_promedio_real: { type: Number, default: null },
})

const selectedIndex      = ref(0)
const showVendidoModal   = ref(false)
const showReportModal    = ref(false)
const showBorradorModal  = ref(false)
const reportSent         = ref(false)

const promedioVendedor = computed(() => props.vendedor_promedio_real)

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
  showReportModal.value = true
}

const confirmarReporte = () => {
  router.post(route('report.publicacion', props.publicacion.id), {}, {
    onSuccess: () => { showReportModal.value = false; reportSent.value = true },
  })
}

const quitarBorrador = () => {
  showBorradorModal.value = true
}

const confirmarBorrador = () => {
  router.patch(route('publicaciones.active', props.publicacion.id), {}, {
    onSuccess: () => { showBorradorModal.value = false; }
  })
}

const confirmarVendido = () => {
  router.patch(route('publicaciones.vendida', props.publicacion.id), {}, {
    onSuccess: () => { showVendidoModal.value = false },
  })
}
</script>
