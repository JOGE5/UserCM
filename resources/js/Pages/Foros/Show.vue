<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import {
    MessageCircle, Send, Trash2, Edit3, ArrowLeft,
    User, Calendar, Hash, AlertTriangle, X, ChevronRight,
    Clock, MessageSquare
} from 'lucide-vue-next';

const props = defineProps({
    foro: Object,
    currentUserId: Number,
    canEdit: Boolean,
});

const form = useForm({ texto: '' });
const alertMessage = ref('');
const charCount = computed(() => form.texto.length);

function submitComentario() {
    if (!form.texto.trim()) return;
    alertMessage.value = '';
    form.post(route('productos.comentarios.store', props.foro.ID_Foro), {
        preserveScroll: true,
        onSuccess: () => form.reset('texto'),
        onError: (errors) => {
            const msg = errors.texto || Object.values(errors)[0] || 'Error al enviar.';
            alertMessage.value = Array.isArray(msg) ? msg[0] : msg;
            setTimeout(() => { alertMessage.value = ''; }, 6000);
        },
    });
}

const showDeleteModal = ref(false);
const deleteError = ref('');
const deleting = ref(false);

function confirmDelete() {
    deleting.value = true;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch(route('productos.destroy', props.foro.ID_Foro), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '' },
        credentials: 'same-origin',
        body: JSON.stringify({ _method: 'DELETE' }),
    }).then(r => {
        if (r.ok) router.visit(route('productos'));
        else r.json().then(j => { deleteError.value = j.message || 'Error al eliminar'; deleting.value = false; });
    }).catch(() => { deleteError.value = 'Error al eliminar'; deleting.value = false; });
}

const getImageUrl = (img) => img ? `/files/foros/${img.split('/').pop()}` : null;

const formatDate = (d) => d
    ? new Intl.DateTimeFormat('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d))
    : '';

const formatTime = (d) => d
    ? new Intl.DateTimeFormat('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }).format(new Date(d))
    : '';
</script>

<template>
    <AppLayout :title="foro.Titulo_Foro">
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('productos')" class="flex items-center gap-1.5 text-[10px] font-black tracking-widest uppercase text-gray-400 hover:text-brand-500 dark:hover:text-brand-400 transition-colors">
                    <ArrowLeft class="w-3.5 h-3.5" />
                    Foros
                </Link>
                <ChevronRight class="w-3 h-3 text-gray-300 dark:text-gray-600" />
                <span class="text-[10px] font-black tracking-widest uppercase text-brand-500 dark:text-brand-400 truncate max-w-xs">
                    {{ foro.Titulo_Foro }}
                </span>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-8 pb-20">

            <!-- Card principal del foro -->
            <article class="bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] overflow-hidden shadow-xl shadow-black/5">

                <!-- Imagen de portada -->
                <div v-if="foro.Imagen_Foro" class="relative h-72 w-full overflow-hidden">
                    <img :src="getImageUrl(foro.Imagen_Foro)" class="w-full h-full object-cover" :alt="foro.Titulo_Foro" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <!-- Badge categoría sobre imagen -->
                    <div v-if="foro.categoria" class="absolute top-6 left-6">
                        <span class="flex items-center gap-1.5 px-3 py-1.5 text-[9px] font-black tracking-widest uppercase text-brand-300 bg-black/50 border border-brand-500/30 backdrop-blur-md rounded-xl">
                            <Hash class="w-3 h-3" />
                            {{ foro.categoria.Nombre_Categoria }}
                        </span>
                    </div>
                </div>

                <!-- Header sin imagen: banner de color -->
                <div v-else class="relative h-32 w-full bg-gradient-to-br from-brand-600/80 via-brand-500/60 to-indigo-600/80 overflow-hidden">
                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                    <div v-if="foro.categoria" class="absolute top-4 left-6">
                        <span class="flex items-center gap-1.5 px-3 py-1.5 text-[9px] font-black tracking-widest uppercase text-white bg-white/20 border border-white/30 backdrop-blur-md rounded-xl">
                            <Hash class="w-3 h-3" />
                            {{ foro.categoria.Nombre_Categoria }}
                        </span>
                    </div>
                </div>

                <div class="p-8 lg:p-10">
                    <!-- Título y acciones -->
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4 mb-6">
                        <h1 class="flex-1 text-2xl lg:text-3xl font-black text-gray-900 dark:text-white leading-tight">
                            {{ foro.Titulo_Foro }}
                        </h1>
                        <div v-if="canEdit" class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="route('productos.edit', foro.ID_Foro)"
                                class="flex items-center gap-1.5 px-4 py-2 text-[10px] font-black tracking-widest uppercase text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-xl hover:bg-indigo-500/20 transition-all"
                            >
                                <Edit3 class="w-3.5 h-3.5" />
                                Editar
                            </Link>
                            <button
                                @click="showDeleteModal = true"
                                class="flex items-center gap-1.5 px-4 py-2 text-[10px] font-black tracking-widest uppercase text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-xl hover:bg-rose-500/20 transition-all"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                                Eliminar
                            </button>
                        </div>
                    </div>

                    <!-- Meta: creador + fecha -->
                    <div class="flex items-center gap-4 mb-8 pb-6 border-b border-light-border dark:border-dark-border">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full overflow-hidden bg-brand-500/10 border border-brand-500/20 flex items-center justify-center shrink-0">
                                <img v-if="foro.creador?.user?.profile_photo_url" :src="foro.creador.user.profile_photo_url" class="w-full h-full object-cover" />
                                <User v-else class="w-4 h-4 text-brand-500" />
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-gray-900 dark:text-white">
                                    {{ foro.creador?.user?.name ?? 'Comunidad' }}
                                </span>
                                <span class="text-[10px] text-gray-400">Autor</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 text-gray-400">
                            <Calendar class="w-3.5 h-3.5" />
                            <span class="text-[10px] font-bold">{{ formatDate(foro.created_at) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-gray-400">
                            <MessageCircle class="w-3.5 h-3.5" />
                            <span class="text-[10px] font-bold">{{ foro.comentarios?.length ?? 0 }} respuestas</span>
                        </div>
                    </div>

                    <!-- Contenido del foro -->
                    <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap text-sm">
                        {{ foro.Descripcion_Foro }}
                    </div>
                </div>
            </article>

            <!-- Sección de comentarios -->
            <section class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-brand-500/10 border border-brand-500/20">
                        <MessageSquare class="w-5 h-5 text-brand-500" />
                    </div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">
                        Respuestas
                        <span class="ml-2 px-2.5 py-0.5 text-[10px] font-black bg-brand-500/10 border border-brand-500/20 text-brand-600 dark:text-brand-400 rounded-full">
                            {{ foro.comentarios?.length ?? 0 }}
                        </span>
                    </h2>
                </div>

                <!-- Lista de comentarios -->
                <div v-if="foro.comentarios?.length" class="space-y-4">
                    <div
                        v-for="c in foro.comentarios"
                        :key="c.id"
                        class="flex gap-4 p-5 bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-2xl"
                    >
                        <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-100 dark:bg-white/5 border border-light-border dark:border-dark-border flex items-center justify-center shrink-0 mt-0.5">
                            <img v-if="c.usuario?.profile_photo_url" :src="c.usuario.profile_photo_url" class="w-full h-full object-cover" />
                            <User v-else class="w-4 h-4 text-gray-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-xs font-black text-gray-900 dark:text-white">
                                    {{ c.usuario?.name ?? 'Usuario' }}
                                </span>
                                <div class="flex items-center gap-1 text-gray-400">
                                    <Clock class="w-3 h-3" />
                                    <span class="text-[10px]">{{ formatTime(c.created_at) }}</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-wrap">{{ c.texto }}</p>
                        </div>
                    </div>
                </div>

                <!-- Empty comentarios -->
                <div v-else class="flex flex-col items-center justify-center py-12 bg-white/50 dark:bg-dark-surface/50 border border-dashed border-light-border dark:border-dark-border rounded-2xl text-center">
                    <MessageCircle class="w-10 h-10 text-gray-200 dark:text-gray-700 mb-3" />
                    <p class="text-sm font-bold text-gray-400">Sé el primero en responder</p>
                </div>

                <!-- Formulario para comentar -->
                <div class="bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-2xl p-6">
                    <h3 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Tu respuesta</h3>

                    <div v-if="alertMessage" class="flex items-start gap-2.5 mb-4 p-3.5 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-600 dark:text-rose-400">
                        <AlertTriangle class="w-4 h-4 shrink-0 mt-0.5" />
                        <p class="text-xs font-bold">{{ alertMessage }}</p>
                    </div>

                    <textarea
                        v-model="form.texto"
                        rows="4"
                        maxlength="2000"
                        placeholder="Escribe una respuesta constructiva..."
                        class="w-full px-4 py-3 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-black/30 border border-light-border dark:border-dark-border rounded-xl focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all resize-none placeholder-gray-400 outline-none"
                    ></textarea>

                    <div class="flex items-center justify-between mt-3">
                        <span class="text-[10px] font-bold" :class="charCount > 1800 ? 'text-rose-500' : 'text-gray-400'">
                            {{ charCount }}/2000
                        </span>
                        <div class="flex items-center gap-3">
                            <button
                                v-if="form.texto"
                                @click="form.reset('texto')"
                                class="flex items-center gap-1.5 px-3 py-2 text-[10px] font-black tracking-widest uppercase text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
                            >
                                <X class="w-3.5 h-3.5" />
                                Limpiar
                            </button>
                            <button
                                @click.prevent="submitComentario"
                                :disabled="form.processing || !form.texto.trim()"
                                class="flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-[10px] font-black tracking-widest uppercase rounded-xl shadow-lg shadow-brand-500/20 transition-all hover:scale-105 active:scale-95"
                            >
                                <Send class="w-3.5 h-3.5" />
                                Responder
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Modal Eliminar -->
        <Transition name="fade-overlay">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div class="w-full max-w-sm bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-3xl p-8 shadow-2xl">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 mx-auto mb-5">
                        <Trash2 class="w-7 h-7 text-rose-500" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white text-center mb-2">¿Eliminar foro?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Esta acción eliminará el foro y todos sus comentarios. No se puede deshacer.</p>
                    <div v-if="deleteError" class="mb-4 p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 text-center">{{ deleteError }}</div>
                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false; deleteError = ''" class="flex-1 px-4 py-3 text-xs font-black tracking-widest uppercase text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-white/5 border border-light-border dark:border-dark-border rounded-xl hover:bg-gray-200 dark:hover:bg-white/10 transition-all">
                            Cancelar
                        </button>
                        <button @click="confirmDelete" :disabled="deleting" class="flex-1 px-4 py-3 text-xs font-black tracking-widest uppercase text-white bg-rose-600 hover:bg-rose-500 disabled:opacity-60 rounded-xl shadow-lg shadow-rose-500/20 transition-all">
                            {{ deleting ? 'Eliminando...' : 'Sí, eliminar' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<style scoped>
.fade-overlay-enter-active, .fade-overlay-leave-active { transition: all 0.25s ease; }
.fade-overlay-enter-from, .fade-overlay-leave-to { opacity: 0; }
</style>
