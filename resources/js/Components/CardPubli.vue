<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'; // Importa funciones de Vue

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
  }
});

const showModal = ref(false); // Controla si el modal está abierto o cerrado

const emit = defineEmits(["edit", "contact"]); // Eventos que el componente puede emitir

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

// Función que emite el evento "Contactar" y cierra el modal
function doContact() {
  if (props.id) emit('Contactar', props.id);
  close();
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
        <p class="modal-price" v-if="props.subtitle">{{ props.subtitle }}</p> <!-- Subtítulo -->
        <p class="modal-category" v-if="props.category">Categoría: {{ props.category }}</p> <!-- Categoría -->
        <p class="modal-description" v-if="props.description">{{ props.description }}</p> <!-- Descripción -->

        <!-- Pie del modal con botones -->
        <div class="modal-footer">
          <button class="btn-primary" @click.stop="doContact">Contactar</button> <!-- Botón contactar -->
          <button class="btn-secondary" @click.stop="doEdit">Editar</button> <!-- Botón editar -->
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
  background: #07182ed0;          /* Color de fondo */
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
  color: #cbd5e1;               /* Gris claro */
  font-size: 0.9rem;            /* Tamaño más pequeño que título */
  margin-top: 6px;              /* Espacio superior respecto al título */
}

.card::before {
  content: '';  
  position: absolute;
  width: 100px;                 
  background-image: linear-gradient(180deg, rgb(0, 183, 255), rgb(255, 48, 255));
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
  background: rgba(7, 24, 46, 0.6); /* Overlay translúcido */
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

.modal-footer {
  display: flex;             
  gap: 0.5rem;              
  margin-top: 1rem;         
}

.btn-primary {
  background: #059669;       
  color: white;              
  padding: 0.5rem 0.75rem;  
  border-radius: 8px;       
  border: none;             
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
