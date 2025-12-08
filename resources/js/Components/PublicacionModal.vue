<template>
  <!-- CARD COMPACTA (Lista) -->
  <div class="card">
    <div class="card-image">
      <Link :href="routeToShow">
        <img
          v-if="hasImage"
          :src="getImageUrl(publicacion.Imagen_Publicacion)"
          :alt="publicacion.Titulo_Publicacion || title"
          class="image"
        />
        <div v-else class="image-placeholder">📷</div>
      </Link>
      <button
        v-if="!isOwner"
        @click.prevent="openShow"
        class="star-overlay"
        aria-label="Ver publicación"
      >★</button>
    </div>

    <div class="card-body">
      <h3 class="card-title">{{ title }}</h3>
      <p class="card-sub">{{ subtitle }}</p>
      <p class="card-desc">{{ description }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  title: String,
  subtitle: String,
  description: String,
  category: [String, Number],
  id: [String, Number],
  user: Object,
  currentUserId: [String, Number],
  isOwner: Boolean,
  estado: String,
  publicacion: Object,
});

const routeToShow = computed(() => {
  try {
    return typeof route === 'function' ? route('publicaciones.show', props.id) : `/publicaciones/${props.id}`;
  } catch (e) {
    return `/publicaciones/${props.id}`;
  }
});

function openShow() {
  try {
    if (typeof route === 'function') {
      router.visit(route('publicaciones.show', props.id));
    } else {
      router.visit(`/publicaciones/${props.id}`);
    }
  } catch (e) {
    // fallback simple navigation
    window.location.href = `/publicaciones/${props.id}`;
  }
}

const hasImage = computed(() => {
  const img = props.publicacion && props.publicacion.Imagen_Publicacion;
  return !!img;
});

function getImageUrl(imgField) {
  if (!imgField) return '/images/posters/university-logo.png';
  try {
    if (typeof imgField === 'string') {
      const parsed = JSON.parse(imgField);
      if (Array.isArray(parsed) && parsed.length > 0) return `/storage/${parsed[0]}`;
      // if it's not JSON, treat as single path
      return imgField.startsWith('http') ? imgField : `/storage/${imgField}`;
    }
    if (Array.isArray(imgField) && imgField.length > 0) return `/storage/${imgField[0]}`;
  } catch (e) {
    return imgField.startsWith('http') ? imgField : `/storage/${imgField}`;
  }
  return '/images/posters/university-logo.png';
}
</script>

<style scoped>
.card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 6px 18px rgba(0,0,0,0.05); width: 100%; }
.card-image { position: relative; }
.image { display: block; width: 100%; height: 220px; object-fit: cover; }
.image-placeholder { display:flex; align-items:center; justify-content:center; height:220px; background:#f3f4f6; color:#9ca3af; font-size:48px; }
.star-overlay { position:absolute; top:8px; right:8px; background:rgba(255,255,255,0.9); border-radius:999px; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer; font-weight:700; }
.card-body { padding:12px; }
.card-title { font-weight:700; margin:0 0 4px 0; font-size:16px; }
.card-sub { color:#10b981; font-weight:600; margin:0 0 6px 0; }
.card-desc { color:#6b7280; font-size:13px; margin:0; max-height:40px; overflow:hidden; }

/* Rating / animation styles */
.rating label {
  cursor: pointer;
}

.rating svg {
  width: 2rem;
  height: 2rem;
  overflow: visible;
  fill: transparent;
  stroke: var(--stroke);
  stroke-linejoin: bevel;
  stroke-dasharray: 12;
  animation: idle 4s linear infinite;
  transition: stroke 0.2s, fill 0.5s;
}

@keyframes idle {
  from {
    stroke-dashoffset: 24;
  }
}

.rating label:hover svg {
  stroke: var(--fill);
}

.rating input:checked ~ label svg {
  transition: 0s;
  animation: idle 4s linear infinite, yippee 0.75s backwards;
  fill: var(--fill);
  stroke: var(--fill);
  stroke-opacity: 0;
  stroke-dasharray: 0;
  stroke-linejoin: miter;
  stroke-width: 8px;
}

@keyframes yippee {
  0% {
    transform: scale(1);
    fill: var(--fill);
    fill-opacity: 0;
    stroke-opacity: 1;
    stroke: var(--stroke);
    stroke-dasharray: 10;
    stroke-width: 1px;
    stroke-linejoin: bevel;
  }

  30% {
    transform: scale(0);
    fill: var(--fill);
    fill-opacity: 0;
    stroke-opacity: 1;
    stroke: var(--stroke);
    stroke-dasharray: 10;
    stroke-width: 1px;
    stroke-linejoin: bevel;
  }

  30.1% {
    stroke: var(--fill);
    stroke-dasharray: 0;
    stroke-linejoin: miter;
    stroke-width: 8px;
  }

  60% {
    transform: scale(1.2);
    fill: var(--fill);
  }
}

.rating-info {
  font-size: 13px;
  color: #666;
  margin: 8px 0;
  font-weight: 500;
}

.btn-submit-rating {
  background: #10b981;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
}

.btn-submit-rating:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-submit-rating:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ===== BOTONES DE ACCIÓN ===== */
.modal-actions {
  display: grid;
  /* asegurar botones grandes y alineados: mín 140px por botón */
  grid-template-columns: repeat(3, minmax(140px, 1fr)) !important;
  gap: 14px !important;
  border-top: 1px solid #e5e7eb;
  padding-top: 18px;
}

.action-btn {
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
}

.btn-contactar {
  background: #3b82f6;
  color: white;
}

.btn-contactar:hover {
  background: #2563eb;
  transform: translateY(-2px);
}

.btn-favorito {
  background: #fecaca;
  color: #dc2626;
  border: 1px solid #fca5a5;
}

.btn-favorito:hover {
  background: #fca5a5;
}

.btn-reportar {
  background: #fed7aa;
  color: #d97706;
  border: 1px solid #fdba74;
}

.btn-reportar:hover {
  background: #fdba74;
}

.btn-share {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #e5e7eb;
}

.btn-share:hover { background: #e6e9ee; transform: translateY(-2px); }

/* ===== REPUTACIÓN BADGE ===== */
.reputation-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  text-transform: capitalize;
}

.reputation-badge.small {
  padding: 3px 6px;
  font-size: 10px;
}

.badge-malo {
  background-color: #fee2e2;
  color: #dc2626;
}

.badge-regular {
  background-color: #fef3c7;
  color: #d97706;
}

.badge-bueno {
  background-color: #d1fae5;
  color: #059669;
}

.badge-excelente {
  background-color: #dbeafe;
  color: #0284c7;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .modal-content-wrapper {
    grid-template-columns: 1fr;
    gap: 16px;
    padding: 16px;
  }

  .modal-right {
    max-height: auto;
  }

  .modal-actions {
    grid-template-columns: 1fr;
  }

  .modal-title {
    font-size: 18px;
  }

  .modal-price {
    font-size: 20px;
  }
}
</style>
