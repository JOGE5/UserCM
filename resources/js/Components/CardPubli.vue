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

// El modal ha sido removido: navegamos a la página de la publicación
const isFavorite = ref(false); // Controla si está en favoritos
const isLoadingFavorite = ref(false); // Indica si se está procesando la petición
const showReport = ref(false);
const showModal = ref(false);

const emit = defineEmits(["edit", "contact"]); // Eventos que el componente pueden emitir

// Quitar borrador (activar publicación)
function quitarBorrador() {
  if (!props.publicacion || !props.publicacion.id) return;

  // Confirmación simple
  if (!confirm('¿Deseas publicar esta publicación y quitarla de borradores?')) return;

  const pubId = props.publicacion.id;
  try {
    // Usar named route si está disponible
    let routeUrl = `/publicaciones/${pubId}/active`;
    try {
      if (typeof route === 'function') {
        routeUrl = route('publicaciones.active', pubId);
      }
    } catch (e) {
      // ignore
    }

    // PATCH request via Inertia router
    router.patch(routeUrl, {}, {
      onStart: () => console.log('quitarBorrador: start', { routeUrl }),
      onSuccess: (page) => {
        console.log('quitarBorrador: success', page);
        // Navegar a dashboard (lista de publicaciones)
        try {
          if (typeof route === 'function') {
            router.visit(route('dashboard'));
          } else {
            router.visit('/dashboard');
          }
        } catch (e) {
          window.location.href = '/dashboard';
        }
      },
      onError: (err) => {
        console.error('quitarBorrador error', err);
        alert('No se pudo activar la publicación. Intenta de nuevo.');
      }
    });

  } catch (e) {
    console.error('quitarBorrador unexpected', e);
    alert('Ocurrió un error inesperado');
  }
}

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

// Abrir la vista completa de la publicación (reemplaza el modal)
function open() {
  if (!props.id) return;
  let url = `/publicaciones/${props.id}`;
  try {
    if (typeof route === 'function') {
      url = route('publicaciones.show', props.id);
    }
  } catch (e) {
    // ignore, usar url por defecto
  }

  router.visit(url, {
    onStart: () => console.log('CardPubli: navegando a', url),
    onError: (err) => console.error('CardPubli: error navegando', err),
  });
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
        // No hay modal en esta versión; simplemente loguear
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
  try {
    // Previene contactar a uno mismo
    const ownerId = props.publicacion?.vendedor?.user?.id ?? props.publicacion?.vendedor?.user_id ?? null;
    if (ownerId && props.currentUserId && Number(ownerId) === Number(props.currentUserId)) {
      alert('No puedes contactarte a ti mismo.');
      return;
    }
  } catch (e) {
    // ignore
  }

  if (props.id) {
    console.log('Emitting contact event with id:', props.id);
    emit('contact', props.id);
  }
  // No modal to close here; keep for compatibility
  showModal.value = false;
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

  // Evitar marcar favorito en publicaciones propias
  try {
    const ownerId = props.publicacion.vendedor?.user?.id ?? null;
    if (ownerId && props.currentUserId && ownerId === props.currentUserId) {
      alert('No puedes marcar tu propia publicación como favorita');
      return;
    }
  } catch (e) {
    // ignore and continue
  }

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
// ya no se usa onKeydown porque no hay modal

// Se añade el listener al montar el componente y se inicializa estado de favorito
onMounted(() => {
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
    <!-- Botón Editar solo si es propietario -->
      <button v-if="isOwner" @click.stop="doEdit" class="edit-button" title="Editar publicación">✏️</button>
      <!-- Botón para quitar borrador (activar) visible sólo si es borrador -->
      <button v-if="isOwner && props.estado === 'borrador'" @click.stop="quitarBorrador" class="edit-button" title="Quitar borrador" style="right:60px; background:#10b981">✔️</button>
  </button>

  <!-- El modal fue removido: la tarjeta navega a la página de la publicación completa -->
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

/* Modal removed: styles cleaned */

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

.edit-button {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.edit-button:hover {
  background: #2563eb;
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.edit-button:active {
  transform: scale(0.95);
}

</style>
