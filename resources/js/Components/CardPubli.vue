<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'; // Importa funciones de Vue
import { router } from '@inertiajs/vue3';

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

const emit = defineEmits(["edit", "contact"]); // Eventos que el componente pueden emitir

// Computed para aplicar la imagen de fondo si existe
const imageStyle = computed(() => {
  return props.image ? { backgroundImage: 'url(' + props.image + ')' } : null;
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
  if (props.id) emit('edit', props.id);
  close();
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

// Se añade el listener al montar el componente
onMounted(() => window.addEventListener('keydown', onKeydown));
// Se elimina el listener al desmontar el componente
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
  <!-- Card como botón que abre modal -->
  <button type="button" class="card" @click="open" aria-haspopup="dialog" :aria-expanded="showModal">
    <!-- Imagen de fondo de la card -->
    <div v-if="props.image" class="image" :style="imageStyle"></div>
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
      <div class="modal-image" v-if="props.image" :style="imageStyle"></div>
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
            <!-- Botón favorito para todos excepto el propietario -->
            <button v-if="!props.isOwner && props.user && props.currentUserId && props.user.id !== props.currentUserId" 
                    :class="{ 'btn-favorite': !isFavorite, 'btn-favorite-active': isFavorite }" 
                    @click.stop="toggleFavorito"
                    :disabled="isLoadingFavorite || !props.publicacion">
              {{ isFavorite ? '♥ Favorito' : '♡ Favorito' }}
            </button>
            
            <!-- Botón de editar solo si es propietario -->
            <button v-if="props.isOwner" class="btn-secondary" @click.stop="doEdit">Editar</button>
          </div>
          
          <!-- Fila 2: Botón contactar (solo no propietarios) -->
          <button v-if="props.user && props.currentUserId && props.user.id !== props.currentUserId" class="btn-primary" @click.stop="doContact">Contactar</button>
        </div>
      </div>
    </div>
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
  color: white;                 /* Color blanco */
  font-size: 1.25rem;           /* Tamaño del título */
  margin: 0;                    /* Sin margen */
}

.card .subtitle {
  color: #000000a4;               /* Gris claro */
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
