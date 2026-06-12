<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CardPubli from '@/Components/CardPubli.vue';
import ModalCalificar from '@/Components/ModalCalificar.vue';
import {
  Star,
  GraduationCap,
  BookOpen,
  ShieldCheck,
  BadgeCheck,
  MessageCircle,
  Award,
  BarChart2,
  Package,
} from 'lucide-vue-next';

const props = defineProps({
  vendedor:             { type: Object, required: true },
  reputacion:           { type: Object, default: null },
  promedio:             { type: Number, default: null },
  total_calificaciones: { type: Number, default: 0 },
  publicaciones:        { type: Array, default: () => [] },
  isOwner:              { type: Boolean, default: false },
  yaCalifico:           { type: Boolean, default: false },
  currentUserId:        { type: [Number, String], default: null },
});

const showModal = ref(false);

// Estado de reputación reactivo (se actualiza al calificar sin recarga)
const reputacionData    = ref(props.reputacion);
const promedioData      = ref(props.promedio);
const totalCalif        = ref(props.total_calificaciones);
const yaCalifico        = ref(props.yaCalifico);

const estadoColor = computed(() => {
  const map = {
    Malo:      { bg: 'bg-rose-500',    text: 'text-white', border: 'border-rose-400',    bar: '#ef4444' },
    Regular:   { bg: 'bg-amber-500',   text: 'text-white', border: 'border-amber-400',   bar: '#f59e0b' },
    Bueno:     { bg: 'bg-emerald-400', text: 'text-white', border: 'border-emerald-300', bar: '#34d399' },
    Excelente: { bg: 'bg-emerald-600', text: 'text-white', border: 'border-emerald-500', bar: '#059669' },
  };
  return map[reputacionData.value?.estado_actual] ?? { bg: 'bg-gray-400', text: 'text-white', border: 'border-gray-300', bar: '#9ca3af' };
});

const probBars = computed(() => {
  if (!reputacionData.value) return [];
  return [
    { label: 'Malo',      value: reputacionData.value.p_malo,      color: '#ef4444' },
    { label: 'Regular',   value: reputacionData.value.p_regular,   color: '#f59e0b' },
    { label: 'Bueno',     value: reputacionData.value.p_bueno,     color: '#34d399' },
    { label: 'Excelente', value: reputacionData.value.p_excelente, color: '#059669' },
  ];
});

const starsArray = computed(() => {
  const avg = promedioData.value ?? 0;
  return Array.from({ length: 5 }, (_, i) => {
    if (i + 1 <= Math.floor(avg)) return 'full';
    if (i < avg) return 'half';
    return 'empty';
  });
});

function onCalificacionExitosa(data) {
  showModal.value = false;
  yaCalifico.value = true;
  if (data.reputacion_estado)    reputacionData.value = data.reputacion_estado;
  if (data.promedio_puntuacion)  promedioData.value   = Math.round(data.promedio_puntuacion * 10) / 10;
  if (data.total_calificaciones) totalCalif.value     = data.total_calificaciones;
}
</script>

<template>
  <AppLayout :title="`Perfil de ${vendedor.name}`">
    <template #header>
      <div class="flex items-center gap-2 text-brand-500 dark:text-brand-400 font-black tracking-widest text-[10px] uppercase">
        <Link :href="route('dashboard')" class="hover:underline">Marketplace</Link>
        <span class="opacity-40">/</span>
        <span>Perfil del Vendedor</span>
      </div>
    </template>

    <div class="max-w-6xl mx-auto pb-20 px-4">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <!-- Sidebar izquierdo: datos del vendedor -->
        <div class="lg:col-span-4 space-y-6">

          <!-- Card principal del vendedor -->
          <div class="bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[3rem] shadow-xl p-8 flex flex-col items-center text-center">
            <!-- Foto -->
            <div class="relative mb-5">
              <img
                v-if="vendedor.profile_photo_url"
                :src="vendedor.profile_photo_url"
                class="w-32 h-32 rounded-[2rem] object-cover ring-4 ring-brand-500/20 shadow-2xl"
              />
              <div v-else class="w-32 h-32 rounded-[2rem] bg-brand-500 flex items-center justify-center text-5xl font-black text-white shadow-2xl">
                {{ vendedor.name?.charAt(0) || 'V' }}
              </div>
              <!-- Indicador online -->
              <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 border-4 border-white dark:border-dark-surface rounded-full shadow"></div>
            </div>

            <!-- Nombre + badge verificado -->
            <div class="flex items-center justify-center gap-2 mb-1">
              <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ vendedor.name }}</h1>
              <BadgeCheck v-if="vendedor.verificado" class="w-6 h-6 text-sky-500 shrink-0" title="Vendedor verificado" />
            </div>

            <!-- Gamificación: Insignia y Nivel -->
            <div class="flex items-center gap-2 mt-2 mb-3">
              <div class="px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-1 shadow-sm">
                <Award class="w-3 h-3" />
                {{ vendedor.insignia }}
              </div>
              <div class="px-3 py-1 bg-brand-500/10 border border-brand-500/20 text-brand-600 dark:text-brand-400 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm">
                Nivel {{ vendedor.nivel }}
              </div>
            </div>

            <!-- Carrera / Universidad -->
            <div v-if="vendedor.carrera" class="flex items-center gap-1.5 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
              <BookOpen class="w-3 h-3" />
              {{ vendedor.carrera }}
            </div>
            <div v-if="vendedor.universidad" class="flex items-center gap-1.5 text-[10px] font-bold text-gray-400 tracking-widest mb-4">
              <GraduationCap class="w-3 h-3" />
              {{ vendedor.universidad }}
            </div>

            <!-- Badge de reputación GRANDE -->
            <div
              v-if="reputacionData"
              :class="[estadoColor.bg, estadoColor.text, estadoColor.border]"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl border font-black text-sm tracking-widest uppercase shadow-lg mb-5"
            >
              <Award class="w-4 h-4" />
              {{ reputacionData.estado_actual }}
            </div>
            <div v-else class="px-6 py-2.5 rounded-2xl border border-gray-200 dark:border-gray-700 text-gray-400 text-[10px] font-black uppercase tracking-widest mb-5">
              Sin calificaciones aún
            </div>

            <!-- Estrellas promedio -->
            <div class="flex items-center gap-1 mb-1">
              <template v-for="(type, i) in starsArray" :key="i">
                <Star
                  class="w-5 h-5"
                  :class="type !== 'empty' ? 'text-amber-400 fill-amber-400' : 'text-gray-200 dark:text-gray-700 fill-gray-200 dark:fill-gray-700'"
                />
              </template>
            </div>
            <p class="text-xs font-black text-gray-600 dark:text-gray-300 mb-1">
              {{ promedioData !== null ? promedioData + ' / 5' : 'Sin promedio' }}
            </p>
            <p class="text-[10px] font-bold text-gray-400 mb-6">
              {{ totalCalif }} {{ totalCalif === 1 ? 'calificación recibida' : 'calificaciones recibidas' }}
            </p>

            <!-- Botón calificar -->
            <template v-if="!isOwner && currentUserId">
              <button
                v-if="!yaCalifico"
                @click="showModal = true"
                class="w-full flex items-center justify-center gap-2 py-4 bg-brand-600 hover:bg-brand-500 text-white text-xs font-black tracking-widest uppercase rounded-2xl shadow-lg shadow-brand-500/20 transition-all hover:scale-[1.02] active:scale-95"
              >
                <Star class="w-4 h-4" />
                Calificar a este vendedor
              </button>
              <div v-else class="w-full flex items-center justify-center gap-2 py-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-black tracking-widest uppercase rounded-2xl">
                <ShieldCheck class="w-4 h-4" />
                Ya calificaste a este vendedor
              </div>
            </template>
          </div>

          <!-- Gráfico Markov: barras de probabilidad -->
          <div
            v-if="reputacionData"
            class="bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] p-7 shadow-sm"
          >
            <div class="flex items-center gap-2 mb-5">
              <BarChart2 class="w-4 h-4 text-brand-500" />
              <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Distribución de probabilidad</h3>
            </div>
            <p class="text-[9px] font-medium text-gray-400 mb-5 leading-relaxed">
              Modelo de Markov — probabilidad de transición al siguiente estado de reputación.
            </p>

            <div class="space-y-4">
              <div v-for="bar in probBars" :key="bar.label">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-[10px] font-black uppercase tracking-widest" :style="{ color: bar.color }">{{ bar.label }}</span>
                  <span class="text-[10px] font-black text-gray-500 dark:text-gray-400">{{ (bar.value * 100).toFixed(0) }}%</span>
                </div>
                <div class="h-2 w-full bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-700"
                    :style="{ width: (bar.value * 100).toFixed(1) + '%', backgroundColor: bar.color }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Publicaciones activas -->
        <div class="lg:col-span-8">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2.5 rounded-2xl bg-brand-500/10 border border-brand-500/20">
              <Package class="w-5 h-5 text-brand-600" />
            </div>
            <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
              Publicaciones activas
            </h2>
            <span class="ml-auto px-3 py-1 text-[10px] font-black bg-brand-500/10 text-brand-600 dark:text-brand-400 rounded-full border border-brand-500/20 uppercase tracking-widest">
              {{ publicaciones.length }}
            </span>
          </div>

          <div v-if="publicaciones.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            <CardPubli
              v-for="pub in publicaciones"
              :key="pub.id"
              :id="pub.id"
              :title="pub.Titulo_Publicacion"
              :subtitle="`Bs ${parseFloat(pub.Precio_Publicacion).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`"
              :description="pub.Descripcion_Publicacion"
              :category="pub.categoria?.Nombre_Categoria"
              :publicacion="pub"
              :user="vendedor"
              :current-user-id="currentUserId"
              :is-owner="isOwner"
              :estado="pub.estado"
            />
          </div>

          <div v-else class="flex flex-col items-center justify-center py-24 text-gray-400">
            <Package class="w-16 h-16 mb-4 opacity-20" />
            <p class="text-sm font-black uppercase tracking-widest">Sin publicaciones activas</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de calificación -->
    <ModalCalificar
      v-if="showModal"
      :user-id="vendedor.id"
      :user-name="vendedor.name"
      @close="showModal = false"
      @success="onCalificacionExitosa"
    />
  </AppLayout>
</template>
