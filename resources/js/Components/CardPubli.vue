<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { 
    Edit3, 
    CheckCircle, 
    Heart, 
    MessageCircle, 
    MoreHorizontal, 
    Eye,
    TrendingUp,
    Clock
} from 'lucide-vue-next';
import ReportModal from '@/Components/ReportModal.vue';

const props = defineProps({
  title: { type: String, default: 'Publicación' },
  subtitle: { type: String, default: '' },
  image: { type: String, default: null },
  description: { type: String, default: '' },
  category: { type: [String, Number], default: null },
  id: { type: [String, Number], default: null },
  user: { type: Object, default: null },
  currentUserId: { type: [String, Number], default: null },
  isOwner: { type: Boolean, default: false },
  initialIsFavorite: { type: Boolean, default: false },
  estado: { type: String, default: 'activa' },
  publicacion: { type: Object, default: null }
});

const isFavorite = ref(false);
const isLoadingFavorite = ref(false);
const showReport = ref(false);
const carouselIndex = ref(0);
let carouselTimer = null;

const emit = defineEmits(["edit", "contact"]);

const images = computed(() => {
  if (props.publicacion?.Imagen_Publicacion) {
    try {
      const ip = props.publicacion.Imagen_Publicacion;
      const parsed = Array.isArray(ip) ? ip : JSON.parse(ip);
      return parsed.slice(0, 3).map(i => `/files/publicaciones/${i.split('/').pop()}`);
    } catch (e) {
      const ip = props.publicacion.Imagen_Publicacion;
      return ip ? [`/files/publicaciones/${String(ip).split('/').pop()}`] : (props.image ? [props.image] : []);
    }
  }
  return props.image ? [props.image] : [];
});

const currentImage = computed(() => images.value[carouselIndex.value] || '/images/placeholder.jpg');

function toggleFavorito() {
  if (!props.publicacion || props.isOwner) return;
  isLoadingFavorite.value = true;
  router.post(route('favoritos.toggle', props.publicacion.id), {}, {
    onSuccess: () => {
      isFavorite.value = !isFavorite.value;
      isLoadingFavorite.value = false;
    },
    onError: () => isLoadingFavorite.value = false
  });
}

function open() {
  if (props.id) router.visit(route('publicaciones.show', props.id));
}

function activateBorrador() {
  if (!confirm('¿Deseas publicar esta oferta ahora?')) return;
  router.patch(route('publicaciones.active', props.id), {}, {
    onSuccess: () => router.visit(route('dashboard'))
  });
}

onMounted(() => {
  if (props.initialIsFavorite) isFavorite.value = true;
  if (images.value.length > 1) {
    carouselTimer = setInterval(() => {
      carouselIndex.value = (carouselIndex.value + 1) % images.value.length;
    }, 4000);
  }
});

onBeforeUnmount(() => {
  if (carouselTimer) clearInterval(carouselTimer);
});
</script>

<template>
  <div 
    class="group relative flex flex-col w-full bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2rem] overflow-hidden transition-all duration-500 hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] hover:-translate-y-1.5 active:scale-[0.98]"
  >
    <!-- Imagen / Header de la Card -->
    <div @click="open" class="relative aspect-[4/3] overflow-hidden cursor-pointer">
      <img 
        :src="currentImage" 
        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
        :alt="props.title"
      />
      
      <!-- Overlay de Degradado -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>

      <!-- Badge de Categoría -->
      <div class="absolute top-4 left-4">
        <span class="px-3 py-1 text-[10px] font-black tracking-widest text-white uppercase bg-white/10 backdrop-blur-md border border-white/20 rounded-full">
          {{ props.category || 'Varios' }}
        </span>
      </div>

      <!-- Favorito -->
      <button 
        v-if="!isOwner"
        @click.stop="toggleFavorito"
        class="absolute top-4 right-4 p-2.5 rounded-xl transition-all duration-300 backdrop-blur-md border"
        :class="isFavorite 
          ? 'bg-rose-500 border-rose-400 text-white shadow-lg' 
          : 'bg-white/10 border-white/20 text-white hover:bg-white/20'"
      >
        <Heart class="w-4 h-4" :class="{ 'fill-current': isFavorite }" />
      </button>

      <!-- Badge de Estado (Borrador/Vendido) -->
      <div v-if="props.estado !== 'activa'" class="absolute bottom-4 left-4">
        <span 
          :class="[
            'px-3 py-1 text-[10px] font-black tracking-widest uppercase rounded-full border shadow-sm',
            props.estado === 'borrador' ? 'bg-amber-500 border-amber-400 text-white' : 'bg-emerald-500 border-emerald-400 text-white'
          ]"
        >
          {{ props.estado }}
        </span>
      </div>
    </div>

    <!-- Contenido -->
    <div class="flex flex-col flex-1 p-6">
      <div class="flex items-start justify-between gap-3 mb-2">
        <h3 
          @click="open"
          class="text-base font-bold text-gray-800 dark:text-white line-clamp-1 cursor-pointer hover:text-brand-500 dark:hover:text-brand-400 transition-colors"
        >
          {{ props.title }}
        </h3>
        <span class="text-sm font-black text-brand-600 dark:text-brand-400 shrink-0">
          {{ props.subtitle }}
        </span>
      </div>

      <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-6 leading-relaxed">
        {{ props.description }}
      </p>

      <!-- Footer / Acciones -->
      <div class="mt-auto flex items-center justify-between pt-4 border-t border-light-border dark:border-dark-border">
        <!-- Usuario Vendedor -->
        <div class="flex items-center gap-2 max-w-[60%]">
          <div class="relative w-6 h-6 shrink-0">
            <img 
              v-if="props.user?.profile_photo_url"
              :src="props.user.profile_photo_url"
              class="w-full h-full rounded-full object-cover ring-1 ring-light-border dark:ring-dark-border"
            />
            <div v-else class="w-full h-full rounded-full bg-brand-100 dark:bg-brand-900/40 flex items-center justify-center text-[8px] font-bold text-brand-600 dark:text-brand-400">
              {{ props.user?.name?.charAt(0) || 'U' }}
            </div>
          </div>
          <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400 truncate">{{ props.user?.name || 'Vendedor' }}</span>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center gap-1.5">
          <template v-if="isOwner">
            <button 
              @click.stop="activateBorrador" 
              v-if="props.estado === 'borrador'"
              class="p-2 text-emerald-500 hover:bg-emerald-500/10 rounded-xl transition-colors"
              title="Publicar ahora"
            >
              <CheckCircle class="w-4 h-4" />
            </button>
            <button 
              @click.stop="router.visit(route('publicaciones.edit', props.id))"
              class="p-2 text-indigo-500 hover:bg-indigo-500/10 rounded-xl transition-colors"
              title="Editar"
            >
              <Edit3 class="w-4 h-4" />
            </button>
          </template>
          <template v-else>
            <button 
              @click.stop="emit('contact', props.id)"
              class="flex items-center gap-1.5 px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white text-[10px] font-black rounded-xl shadow-lg shadow-brand-500/20 transition-all active:scale-90"
            >
              <MessageCircle class="w-3.5 h-3.5" />
              CONTACTAR
            </button>
          </template>
        </div>
      </div>
    </div>

    <!-- Indicador de Imágenes (Dot indicator) -->
    <div v-if="images.length > 1" class="absolute bottom-[40%] left-1/2 -translate-x-1/2 flex gap-1 z-10">
      <div 
        v-for="(_, idx) in images" 
        :key="idx"
        :class="['h-1 rounded-full transition-all duration-300', carouselIndex === idx ? 'w-4 bg-white shadow-sm' : 'w-1 bg-white/40']"
      ></div>
    </div>
  </div>

  <!-- Modal de reporte -->
  <ReportModal 
    v-if="showReport" 
    :publicacionId="props.id" 
    :ownerId="props.publicion?.vendedor?.user_id" 
    @close="showReport = false" 
  />
</template>

<style scoped>
/* Transiciones sutiles para el hover de la card */
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
