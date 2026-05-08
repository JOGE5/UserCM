<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import { Hash, Image as ImageIcon, ArrowLeft, ChevronRight, X, Save, Trash2 } from 'lucide-vue-next';

const props = defineProps({ foro: Object, categorias: Array });

const form = useForm({
    Titulo_Foro: props.foro.Titulo_Foro || '',
    Descripcion_Foro: props.foro.Descripcion_Foro || '',
    Cod_Categoria: props.foro.Cod_Categoria || null,
    Imagen_Foro: null,
    _method: 'PUT',
});

const imagePreview = ref(
    props.foro.Imagen_Foro
        ? `/files/foros/${props.foro.Imagen_Foro.split('/').pop()}`
        : null
);
const imageError = ref('');
const titleCount = computed(() => form.Titulo_Foro.length);
const descCount = computed(() => form.Descripcion_Foro.length);

function handleImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    imageError.value = '';
    if (!file.type.startsWith('image/')) {
        imageError.value = 'Solo se aceptan archivos de imagen.';
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        imageError.value = 'La imagen no puede superar los 2MB.';
        return;
    }
    form.Imagen_Foro = file;
    const reader = new FileReader();
    reader.onload = () => imagePreview.value = reader.result;
    reader.readAsDataURL(file);
}

function removeImage() {
    form.Imagen_Foro = null;
    imagePreview.value = null;
    imageError.value = '';
}

function submit() {
    form.post(route('productos.update', props.foro.ID_Foro));
}

// Eliminar
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
        else r.json().then(j => { deleteError.value = j.message || 'Error'; deleting.value = false; });
    }).catch(() => { deleteError.value = 'Error al eliminar'; deleting.value = false; });
}
</script>

<template>
    <AppLayout title="Editar Discusión">
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('productos.show', foro.ID_Foro)" class="flex items-center gap-1.5 text-[10px] font-black tracking-widest uppercase text-gray-400 hover:text-brand-500 transition-colors">
                    <ArrowLeft class="w-3.5 h-3.5" />
                    Ver foro
                </Link>
                <ChevronRight class="w-3 h-3 text-gray-300 dark:text-gray-600" />
                <span class="text-[10px] font-black tracking-widest uppercase text-brand-500">Editar</span>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-20">
            <div class="bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] overflow-hidden shadow-xl shadow-black/5">

                <!-- Banner -->
                <div class="relative h-24 bg-gradient-to-br from-indigo-600/80 via-brand-500/60 to-brand-600/80 overflow-hidden">
                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                    <div class="absolute inset-0 flex items-center px-8 gap-3">
                        <div class="p-2.5 rounded-xl bg-white/20 border border-white/30 backdrop-blur-md">
                            <Hash class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg font-black text-white leading-none">Editar Discusión</h1>
                            <p class="text-[10px] text-white/70 font-bold mt-0.5 truncate max-w-xs">{{ foro.Titulo_Foro }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 lg:p-10 space-y-7">

                    <!-- Título -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="titulo" class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Título</label>
                            <span class="text-[10px] font-bold" :class="titleCount > 180 ? 'text-rose-500' : 'text-gray-400'">{{ titleCount }}/200</span>
                        </div>
                        <input
                            id="titulo"
                            v-model="form.Titulo_Foro"
                            type="text"
                            maxlength="200"
                            class="w-full px-5 py-3.5 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-black/30 border border-light-border dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all outline-none font-medium"
                        />
                        <InputError :message="form.errors.Titulo_Foro" />
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="descripcion" class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Descripción</label>
                            <span class="text-[10px] font-bold text-gray-400">{{ descCount }} caracteres</span>
                        </div>
                        <textarea
                            id="descripcion"
                            v-model="form.Descripcion_Foro"
                            rows="6"
                            class="w-full px-5 py-3.5 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-black/30 border border-light-border dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all outline-none resize-none leading-relaxed"
                        ></textarea>
                        <InputError :message="form.errors.Descripcion_Foro" />
                    </div>

                    <!-- Categoría -->
                    <div class="space-y-2">
                        <label for="categoria" class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Categoría</label>
                        <select
                            id="categoria"
                            v-model="form.Cod_Categoria"
                            class="w-full px-5 py-3.5 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-black/30 border border-light-border dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all outline-none font-medium appearance-none cursor-pointer"
                        >
                            <option :value="null" disabled>Selecciona una categoría...</option>
                            <option v-for="c in categorias" :key="c.Cod_Categoria" :value="c.Cod_Categoria">{{ c.Nombre_Categoria }}</option>
                        </select>
                        <InputError :message="form.errors.Cod_Categoria" />
                    </div>

                    <!-- Imagen -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Imagen de portada <span class="text-gray-400 normal-case font-bold">(opcional)</span></label>

                        <div v-if="imagePreview" class="relative rounded-2xl overflow-hidden border border-light-border dark:border-dark-border group">
                            <img :src="imagePreview" class="w-full h-48 object-cover" />
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-black/50 transition-opacity">
                                <button @click="removeImage" type="button" class="flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-xs font-black rounded-xl">
                                    <X class="w-4 h-4" /> Cambiar imagen
                                </button>
                            </div>
                        </div>

                        <label v-else for="imagen" class="flex flex-col items-center justify-center gap-3 w-full h-36 border-2 border-dashed border-light-border dark:border-dark-border rounded-2xl cursor-pointer hover:border-brand-500/50 hover:bg-brand-500/5 transition-all group">
                            <input id="imagen" type="file" accept="image/*" @change="handleImage" class="hidden" />
                            <div class="p-3 rounded-xl bg-gray-100 dark:bg-white/5 group-hover:bg-brand-500/10 transition-colors">
                                <ImageIcon class="w-6 h-6 text-gray-400 group-hover:text-brand-500 transition-colors" />
                            </div>
                            <p class="text-[10px] text-gray-400 font-bold">Subir nueva imagen · JPG, PNG · Máx 2MB</p>
                        </label>

                        <p v-if="imageError" class="text-xs font-bold text-rose-500">{{ imageError }}</p>
                        <InputError :message="form.errors.Imagen_Foro" />
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center gap-3 pt-2">
                        <button
                            @click="showDeleteModal = true"
                            type="button"
                            class="flex items-center gap-2 px-5 py-3.5 text-xs font-black tracking-widest uppercase text-rose-600 dark:text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-2xl hover:bg-rose-500/20 transition-all"
                        >
                            <Trash2 class="w-4 h-4" />
                            Eliminar
                        </button>
                        <div class="flex-1 flex items-center gap-3">
                            <Link
                                :href="route('productos.show', foro.ID_Foro)"
                                class="flex-1 flex items-center justify-center px-5 py-3.5 text-xs font-black tracking-widest uppercase text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-white/5 border border-light-border dark:border-dark-border rounded-2xl hover:bg-gray-200 dark:hover:bg-white/10 transition-all"
                            >
                                Cancelar
                            </Link>
                            <button
                                @click.prevent="submit"
                                :disabled="form.processing"
                                class="flex-[2] flex items-center justify-center gap-2 px-5 py-3.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 text-white text-xs font-black tracking-widest uppercase rounded-2xl shadow-lg shadow-brand-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                <Save class="w-4 h-4" />
                                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Eliminar -->
        <Transition name="fade-overlay">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div class="w-full max-w-sm bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-3xl p-8 shadow-2xl">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 mx-auto mb-5">
                        <Trash2 class="w-7 h-7 text-rose-500" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white text-center mb-2">¿Eliminar foro?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Esta acción no se puede deshacer.</p>
                    <div v-if="deleteError" class="mb-4 p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs font-bold text-rose-600 text-center">{{ deleteError }}</div>
                    <div class="flex gap-3">
                        <button @click="showDeleteModal = false; deleteError = ''" class="flex-1 px-4 py-3 text-xs font-black uppercase text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-white/5 border border-light-border dark:border-dark-border rounded-xl hover:bg-gray-200 dark:hover:bg-white/10 transition-all">
                            Cancelar
                        </button>
                        <button @click="confirmDelete" :disabled="deleting" class="flex-1 px-4 py-3 text-xs font-black uppercase text-white bg-rose-600 hover:bg-rose-500 disabled:opacity-60 rounded-xl shadow-lg shadow-rose-500/20 transition-all">
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
