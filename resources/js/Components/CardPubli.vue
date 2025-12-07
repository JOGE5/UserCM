<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'; // Importa funciones de Vue
import { router } from '@inertiajs/vue3';
import ReportModal from '@/Components/ReportModal.vue';

// Definición de las props que recibe el componente
const props = defineProps({
  title: {
    type: String,
    default: 'CARD' // Título por defecto
  },
  subtitle: {
    type: String,
    default: '' // Subtítulo por defecto
  },
  image: {
    type: String,
    default: null, // Imagen por defecto
  },
  description: {
    type: String,
    default: '' // Descripción por defecto
  },
  category: {
    type: [String, Number],
    default: null // Categoría por defecto
  },
  id: {
    type: [String, Number],
    default: null // ID del item
  },
  user: {
    type: Object,
    default: null // Usuario que publicó
  },
  currentUserId: {
    type: [String, Number],
    default: null // ID del usuario actual
  },
  isOwner: {
    type: Boolean,
    default: false // Si el usuario actual es propietario
  },
  initialIsFavorite: {
    type: Boolean,
    default: false
  },
  estado: {
    type: String,
    default: 'activa' // Estado de la publicación
  },
  publicacion: {
    type: Object,
    default: null // Objeto de publicación completo
  }
});

const showModal = ref(false); // Controla si el modal está abierto o cerrado
const isFavorite = ref(false); // Controla si está en favoritos
const isLoadingFavorite = ref(false); // Indica si se está procesando la petición
const showReport = ref(false);

const emit = defineEmits(["edit", "contact"]); // Eventos que el componente pueden emitir

// Computed para obtener imágenes (max 3) y aplicar estilo de fondo según el índice del carrusel
const images = computed(() => {
  // preferir imagenes desde publicacion.Imagen_Publicacion
  if (props.publicacion && props.publicacion.Imagen_Publicacion) {
    try {
      const ip = props.publicacion.Imagen_Publicacion;
      if (Array.isArray(ip)) {
        return ip.slice(0,3).map(i => `/files/publicaciones/${i.split('/').pop()}`);
      }
      const parsed = JSON.parse(ip);
      if (Array.isArray(parsed)) {
        return parsed.slice(0,3).map(i => `/files/publicaciones/${i.split('/').pop()}`);
      }
      return ip ? [`/files/publicaciones/${String(ip).split('/').pop()}`] : (props.image ? [props.image] : []);
    } catch (e) {
      const ip = props.publicacion.Imagen_Publicacion;
      return ip ? [`/files/publicaciones/${String(ip).split('/').pop()}`] : (props.image ? [props.image] : []);
    }
  }
  if (props.image) return [props.image];
  return [];
});

const carouselIndex = ref(0);
let carouselTimer = null;

const imageStyle = computed(() => {
  if (!images.value.length) return null;
  return { backgroundImage: 'url(' + images.value[carouselIndex.value] + ')' };
});

// Función para abrir el modal
function open() {
  showModal.value = true;
}

// Función para cerrar el modal
function close() {
  showModal.value = false;
}

// Función que emite el evento "edit" y cierra el modal
function doEdit() {
  // Instrumentación: logs para depurar por qué la navegación a edición provoca pantalla blanca
  console.log('CardPubli: doEdit start', { id: props.id });
  try {
    if (!props.id) {
      console.warn('CardPubli: doEdit sin id');
      return;
    }

    // Usar named route si está disponible para respetar bindings y middleware
    let url = `/publicaciones/${props.id}/edit`;
    try {
      if (typeof route === 'function') {
        url = route('publicaciones.edit', props.id);
      }
    } catch (e) {
      // ignore, ya tenemos url por defecto
    }

    router.visit(url, {
      onStart: () => console.log('CardPubli: router.visit onStart', { url }),
      onSuccess: (page) => {
        console.log('CardPubli: router.visit onSuccess', page);
        close();
      },
      onError: (err) => {
        console.error('CardPubli: router.visit onError', err);
        // fallback: emitir evento para que el padre intente manejar la navegación
        emit('edit', props.id);
      },
      onFinish: () => console.log('CardPubli: router.visit onFinish')
    });

  } catch (e) {
    console.error('CardPubli: doEdit unexpected error', e);
    emit('edit', props.id);
  }
}

// Función que emite el evento "contact" y cierra el modal
function doContact() {
  console.log('doContact called with id:', props.id);
  if (props.id) {
    console.log('Emitting contact event with id:', props.id);
    emit('contact', props.id);
  }
  close();
}

function openReport() {
  showReport.value = true;
}

function closeReport() {
  showReport.value = false;
}

// Función para toggle favorito
function toggleFavorito() {
  if (!props.publicacion) return;
  
  isLoadingFavorite.value = true;
  router.post(route('favoritos.toggle', props.publicacion.id), {}, {
    onSuccess: () => {
      isFavorite.value = !isFavorite.value;
      isLoadingFavorite.value = false;
    },
    onError: () => {
      isLoadingFavorite.value = false;
    }
  });
}

// Función que cierra el modal al presionar Escape
function onKeydown(e) {
  if (e.key === 'Escape') close();
}

// Se añade el listener al montar el componente y se inicializa estado de favorito
onMounted(() => {
  window.addEventListener('keydown', onKeydown);
  if (props.initialIsFavorite) {
    isFavorite.value = true;
  }
  // iniciar carrusel si hay varias imágenes
  if (images.value.length > 1) {
    carouselTimer = setInterval(() => {
      carouselIndex.value = (carouselIndex.value + 1) % Math.min(images.value.length, 3);
    }, 5000);
  }
});
// Se elimina el listener al desmontar el componente y limpiar timer
onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown);
  if (carouselTimer) clearInterval(carouselTimer);
});
</script>

<template>
  <!-- Card como botón que abre modal -->
  <button type="button" class="card" @click="open" aria-haspopup="dialog" :aria-expanded="showModal">
    <!-- Imagen de fondo de la card -->
    <div v-if="images.length" class="image" :style="imageStyle"></div>
    <!-- Contenido textual de la card -->
    <div class="content">
      <h2>{{ props.title }}</h2> <!-- Título -->
      <p v-if="props.subtitle" class="subtitle">{{ props.subtitle }}</p> <!-- Subtítulo -->
    </div>
  </button>

  <!-- Modal que se muestra cuando showModal es true -->
  <div v-if="showModal" class="modal-overlay" role="dialog" aria-modal="true">
    <div class="modal">
      <!-- Botón para cerrar modal -->
      <button class="modal-close" @click.stop="close" aria-label="Cerrar">✕</button>
      <!-- Imagen dentro del modal -->
      <div class="modal-image" v-if="images.length" :style="imageStyle"></div>
      <!-- Cuerpo del modal -->
      <div class="modal-body">

        <h3 class="modal-title">{{ props.title }}</h3> <!-- Título -->
        <div class="vendedor" v-if="props.user">
          <img v-if="props.user.usuarioCampusMarket && props.user.usuarioCampusMarket.Foto_de_perfil" :src="`/files/perfil/${props.user.usuarioCampusMarket.Foto_de_perfil.split('/').pop()}`" alt="Foto de perfil" class="profile-image" />
          <span>Publicado por: {{ props.user.name }}</span>
        </div>
        <p class="modal-price" v-if="props.subtitle">{{ props.subtitle }}</p> <!-- Subtítulo -->
        <p class="modal-category" v-if="props.category">Categoría: {{ props.category }}</p> <!-- Categoría -->
        <p class="modal-description" v-if="props.description">{{ props.description }}</p> <!-- Descripción -->
        
        <!-- Badge de estado si es borrador -->
        <div v-if="props.estado === 'borrador'" class="inline-block px-3 py-1 mt-2 bg-yellow-100 text-yellow-800 text-xs font-medium rounded">
          Borrador (solo visible para ti)
        </div>

        <!-- Pie del modal con botones -->
        <div class="modal-footer">
          <!-- Fila 1: Favorito (no propietarios) + Editar (propietarios) -->
          <div class="flex gap-2">
            <!-- Botón favorito: solo en publicaciones de terceros -->
            <button v-if="props.publicacion && !props.isOwner"
                    :class="{ 'btn-favorite': !isFavorite, 'btn-favorite-active': isFavorite }"
                    @click.stop="toggleFavorito"
                    :disabled="isLoadingFavorite">
              {{ isFavorite ? '♥ Favorito' : '♡ Favorito' }}
            </button>
            
            <!-- Botón de editar solo si es propietario -->
            <button v-if="props.isOwner" class="btn-secondary" @click.stop="doEdit">Editar</button>
          </div>
          
          <!-- Fila 2: Botón contactar (solo no propietarios) -->
          <button v-if="props.user && props.currentUserId && props.user.id !== props.currentUserId" class="btn-primary" @click.stop="doContact">Contactar</button>
          <!-- Botón reportar (solo en publicaciones de terceros) -->
          <button v-if="props.publicacion && !props.isOwner" class="btn-secondary" @click.stop="openReport">Reportar</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal de reporte -->
  <div v-if="showReport">
    <ReportModal :publicacionId="props.publicacion ? props.publicacion.id : props.id" :ownerId="props.publicacion && props.publicacion.vendedor ? props.publicacion.vendedor.user_id : null" @close="closeReport" />
  </div>
</template>

<style scoped>
/* From Uiverse.io by bhaveshxrawat */
.card {
  width: 600px;                 /* Ancho de la card */
  height: 400px;                /* Alto de la card */
  background: #07182ea2;          /* Color de fondo */
  position: relative;           /* Posicionamiento relativo para hijos */
  display: flex;                /* Flex para centrar contenido */
  place-content: center;        /* Centra contenido */
  place-items: center;          /* Centra items */
  overflow: hidden;             /* Oculta overflow */
  border-radius: 20px;          /* Bordes redondeados */
  border: none;                 /* Sin borde */
  padding: 0;                   /* Sin padding */
  cursor: pointer;              /* Cursor de puntero */
}

.card .content {
  z-index: 1;                   /* Nivel de superposición */
  text-align: center;           /* Texto centrado */
}

.card h2 {
  z-index: 1;                   /* Nivel de superposición */
  color: rgb(255, 252, 252);                 /* Color blanco */
  font-size: 1.25rem;           /* Tamaño del título */
  margin: 0;                    /* Sin margen */
}

.card .subtitle {
  color: #fdeded;               /* Gris claro */
  font-size: 0.9rem;            /* Tamaño más pequeño que título */
  margin-top: 6px;              /* Espacio superior respecto al título */
}

.card::before {
  content: '';
  position: absolute;
  width: 100px;
  background-image: linear-gradient(180deg, rgb(3, 16, 129), rgb(255, 48, 255));
  height: 130%;
  animation: rotBGimg 3s linear infinite;  /* Animación de rotación */
  transition: all 0.2s linear;
}

@keyframes rotBGimg {
  from { transform: rotate(0deg); }  /* Inicio de la animación */
  to { transform: rotate(360deg); }  /* Final de la animación */
}

.card::after {
  content: '';
  position: absolute;
  background: rgba(7, 24, 46, 0.322); /* Overlay translúcido */
  inset: 5px;                        /* Espaciado desde los bordes */
  border-radius: 15px;                /* Bordes redondeados */
}

.card .image {
  position: absolute;      /* Posicionamiento absoluto */
  inset: 5px;              /* Margen interno */
  border-radius: 15px;
  background-position: center; /* Centrado de imagen */
  background-size: cover;      /* Cubrir todo el bloque */
  z-index: 0;                  /* Detrás de contenido */
}

.card .content { z-index: 2; }  /* Por encima de la imagen */

.modal-overlay {
  position: fixed;           /* Fijo en pantalla */
  inset: 0;                  /* Ocupa toda la pantalla */
  background: rgba(0,0,0,0.5); /* Fondo negro semi-transparente */
  display: flex;
  align-items: center;       /* Centra vertical */
  justify-content: center;   /* Centra horizontal */
  z-index: 50;               /* Encima de todo */
  padding: 1rem;
}

.modal {
  background: white;         /* Fondo blanco */
  max-width: 720px;
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  position: relative;
  display: grid;
  grid-template-columns: 1fr 1fr; /* Dos columnas: imagen y contenido */
}

.modal-close {
  position: absolute;        /* Posición absoluta */
  top: 8px;
  right: 8px;
  background: transparent;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
}

.modal-image {
  background-position: center;
  background-size: cover;
  min-height: 240px;
}

.modal-body {
  padding: 1rem 1.5rem;
}

.modal-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
}

.modal-category {
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.modal-description {
  color: #374151;
}

.modal-price {
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.vendedor {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.profile-image {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.modal-footer {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn-primary {
  background: #09a775;
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
}

.btn-favorite {
  background: transparent;
  color: #6b7280;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  cursor: pointer;
}

.btn-favorite-active {
  background: #fecaca;
  color: #dc2626;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #dc2626;
  cursor: pointer;
}

.btn-secondary {
  background: transparent;
  color: #111827;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  cursor: pointer;
}

</style>
