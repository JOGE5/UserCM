<template>
  <div class="reputation-badge" :class="`badge-${estadoActual.toLowerCase()}`">
    <span class="badge-icon">{{ getBadgeIcon(estadoActual) }}</span>
    <span class="badge-text">{{ estadoActual }}</span>
  </div>
</template>

<script setup>
defineProps({
  estadoActual: {
    type: String,
    required: true,
    validator: (value) => ['Malo', 'Regular', 'Bueno', 'Excelente'].includes(value),
  },
})

const getBadgeIcon = (estado) => {
  const icons = {
    Malo: '⚠️',
    Regular: '⭐',
    Bueno: '⭐⭐',
    Excelente: '⭐⭐⭐',
  }
  return icons[estado] || '⭐'
}
</script>

<style scoped>
.reputation-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-weight: 600;
  font-size: 0.875rem;
}

.badge-malo {
  background-color: #fee2e2;
  color: #dc2626;
  border: 1px solid #fca5a5;
}

.badge-regular {
  background-color: #fef3c7;
  color: #d97706;
  border: 1px solid #fcd34d;
}

.badge-bueno {
  background-color: #d1fae5;
  color: #059669;
  border: 1px solid #6ee7b7;
}

.badge-excelente {
  background-color: #dbeafe;
  color: #0284c7;
  border: 1px solid #7dd3fc;
}

.badge-icon {
  font-size: 1rem;
}

.badge-text {
  text-transform: capitalize;
}
</style>
