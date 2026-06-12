<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import {
    UploadCloud,
    X,
    ChevronRight,
    Sparkles,
    Package,
    Info,
    DollarSign,
    Trash2,
    Archive,
    CheckCircle2,
    AlertTriangle,
    Settings,
    ShieldAlert,
} from 'lucide-vue-next';

const props = defineProps({
    publicacion: Object,
    categorias: Array,
});

const form = useForm({
    _method: 'PUT',
    Titulo_Publicacion:      props.publicacion.Titulo_Publicacion || '',
    Descripcion_Publicacion: props.publicacion.Descripcion_Publicacion || '',
    Estado_Publicacion:      props.publicacion.Estado_Publicacion ? true : false,
    Precio_Publicacion:      props.publicacion.Precio_Publicacion || '',
    Imagen_Publicacion:      [],
    Cod_Categoria:           props.publicacion.Cod_Categoria || '',
    ubicacion:               props.publicacion.ubicacion || '',
    condicion_producto:      props.publicacion.condicion_producto || 'usado',
});

const imagePreview = ref([]);
const fileInput = ref(null);
const isDragging = ref(false);

const currentImages = computed(() => {
    const data = props.publicacion.Imagen_Publicacion;
    if (!data) return [];
    try {
        const parsed = typeof data === 'string' ? (data.startsWith('[') ? JSON.parse(data) : [data]) : data;
        return Array.isArray(parsed) ? parsed : [parsed];
    } catch (e) { return []; }
});

const submit = () => {
    form.post(route('publicaciones.update', props.publicacion.id), {
        forceFormData: true,
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};

const showDeleteModal = ref(false);
const showDraftModal  = ref(false);

const handleDelete = () => {
    router.delete(route('publicaciones.destroy', props.publicacion.id));
};

const handleDraft = () => {
    router.patch(route('publicaciones.draft', props.publicacion.id));
};

function processFiles(files) {
    const arr = Array.from(files || []);
    if (arr.length === 0) return;
    const validFiles = arr.filter(f => f.type.startsWith('image/'));
    const combined = [...form.Imagen_Publicacion, ...validFiles].slice(0, 3);
    form.Imagen_Publicacion = combined;
    rebuildPreviews();
}

function rebuildPreviews() {
    imagePreview.value = [];
    form.Imagen_Publicacion.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => imagePreview.value.push(e.target.result);
        reader.readAsDataURL(file);
    });
}

const handleImageChange = (event) => {
    processFiles(event.target.files);
    if (fileInput.value) fileInput.value.value = null;
};

const removeSelected = (index) => {
    form.Imagen_Publicacion.splice(index, 1);
    imagePreview.value.splice(index, 1);
};

const getFullUrl = (img) => `/files/publicaciones/${img.split('/').pop()}`;
</script>

<template>
    <AppLayout title="Editar Publicación">
        <template #header>
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                    Editar <span class="text-brand-600 dark:text-brand-400">Publicación</span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">ID: #{{ publicacion.id }}</p>
            </div>
        </template>

        <div class="max-w-4xl mx-auto flex items-center justify-center min-h-[80vh] py-10">
            <!-- Modal 3D Flotante -->
            <div class="w-full glass-panel rounded-[3rem] p-8 md:p-12 shadow-2xl relative overflow-hidden float-3d border border-white/30 dark:border-white/10">
                <!-- Luces Internas -->
                <div class="absolute -top-32 -right-32 w-64 h-64 bg-brand-500/30 blur-[80px] rounded-full pointer-events-none"></div>
                <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-purple-500/30 blur-[80px] rounded-full pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div>
                            <h2 class="text-3xl font-black tracking-tighter bg-clip-text text-transparent bg-gradient-to-r from-brand-600 to-purple-600">Editar Publicación</h2>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-2">ID: #{{ publicacion.id }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                             <SecondaryButton @click="showDraftModal = true" class="!px-4 py-2 border-amber-500/20 hover:border-amber-500/50 hover:bg-amber-500/10 hover:text-amber-600 transition-all">
                                <Archive class="w-4 h-4 mr-2" /> Borrador
                             </SecondaryButton>
                             <button @click="showDeleteModal = true" class="p-2 border border-rose-500/20 hover:border-rose-500/50 bg-rose-500/5 hover:bg-rose-500/10 text-rose-500 rounded-xl transition-all" title="Eliminar">
                                <Trash2 class="w-5 h-5" />
                             </button>
                        </div>
                    </div>
                    
                    <form @submit.prevent="submit" class="space-y-8">
                            <!-- Título -->
                            <div>
                                <InputLabel for="titulo" value="Título" />
                                <TextInput id="titulo" v-model="form.Titulo_Publicacion" type="text" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.Titulo_Publicacion" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Precio -->
                                <div>
                                    <InputLabel for="precio" value="Precio (Bs)" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-brand-500">
                                            <DollarSign class="w-4 h-4" />
                                        </div>
                                        <TextInput id="precio" v-model="form.Precio_Publicacion" type="number" step="0.01" min="1" max="1000000" class="block w-full pl-12" required />
                                    </div>
                                    <InputError :message="form.errors.Precio_Publicacion" class="mt-2" />
                                </div>

                                <!-- Categoría -->
                                <div>
                                    <InputLabel for="categoria" value="Categoría" />
                                    <select
                                        id="categoria"
                                        v-model="form.Cod_Categoria"
                                        class="mt-1 block w-full px-5 py-3.5 bg-white/40 dark:bg-black/40 backdrop-blur-md border border-white/50 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-bold text-sm outline-none shadow-inner"
                                        required
                                    >
                                        <option v-for="cat in categorias" :key="cat.Cod_Categoria" :value="cat.Cod_Categoria">
                                            {{ cat.Nombre_Categoria }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.Cod_Categoria" class="mt-2" />
                                </div>
                            </div>

                            <!-- Ubicación y Condición -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="ubicacion" value="Ubicación (opcional)" />
                                    <TextInput
                                        id="ubicacion"
                                        v-model="form.ubicacion"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Ej: La Paz, Zona Sur..."
                                    />
                                    <InputError :message="form.errors.ubicacion" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="condicion" value="Condición del producto" />
                                    <select
                                        id="condicion"
                                        v-model="form.condicion_producto"
                                        class="mt-1 block w-full px-5 py-3.5 bg-white/40 dark:bg-black/40 backdrop-blur-md border border-white/50 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-bold text-sm outline-none shadow-inner"
                                    >
                                        <option value="usado">Usado</option>
                                        <option value="nuevo">Nuevo</option>
                                    </select>
                                    <InputError :message="form.errors.condicion_producto" class="mt-2" />
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <InputLabel for="descripcion" value="Descripción" />
                                <textarea
                                    id="descripcion"
                                    v-model="form.Descripcion_Publicacion"
                                    rows="5"
                                    class="mt-1 block w-full px-5 py-4 bg-white/40 dark:bg-black/40 backdrop-blur-md border border-white/50 dark:border-white/10 text-gray-900 dark:text-white rounded-[2rem] focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium text-sm outline-none resize-none shadow-inner"
                                    required
                                ></textarea>
                                <InputError :message="form.errors.Descripcion_Publicacion" class="mt-2" />
                            </div>

                            <!-- Imágenes -->
                            <div>
                                <InputLabel value="Imágenes del Producto" />
                                
                                <!-- Fotos Actuales en el Servidor -->
                                <div v-if="currentImages.length > 0 && imagePreview.length === 0" class="mt-4 flex flex-wrap gap-4 mb-8 p-4 bg-gray-50/50 dark:bg-black/10 rounded-[2rem] border border-light-border/50 dark:border-dark-border/50">
                                   <div v-for="(img, idx) in currentImages" :key="idx" class="relative group w-24 h-24 rounded-2xl overflow-hidden border border-light-border dark:border-dark-border opacity-70 hover:opacity-100 transition-all duration-300 shadow-sm">
                                       <img :src="getFullUrl(img)" class="w-full h-full object-cover grayscale-[0.2] group-hover:grayscale-0 transition-all" />
                                       <div class="absolute bottom-0 left-0 right-0 py-1 bg-black/60 text-[8px] font-black text-white text-center uppercase tracking-tighter">Actual {{ idx + 1 }}</div>
                                   </div>
                                   <div class="flex flex-col justify-center text-gray-400">
                                       <div class="flex items-center gap-2 mb-1">
                                           <Info class="w-4 h-4" />
                                           <span class="text-xs font-black uppercase tracking-tighter">Imágenes Guardadas</span>
                                       </div>
                                       <p class="text-[10px] font-medium leading-none">Sube nuevas para reemplazar todas las anteriores.</p>
                                   </div>
                                </div>

                                <div
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="isDragging = false; processFiles($event.dataTransfer.files)"
                                    :class="[
                                        'group relative mt-2 flex flex-col items-center justify-center p-10 border-2 border-dashed rounded-[2.5rem] transition-all duration-500 backdrop-blur-sm',
                                        isDragging ? 'border-brand-500 bg-brand-500/20' : 'border-brand-500/30 bg-white/20 dark:bg-black/20 hover:border-brand-500/60 hover:bg-white/40 dark:hover:bg-black/40'
                                    ]"
                                >
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        @change="handleImageChange"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    />
                                    <UploadCloud class="w-8 h-8 text-brand-500 mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-widest">Reemplazar Imágenes</p>
                                    <p class="text-[10px] font-bold text-gray-400 mt-1">Nuevas fotos descartarán las anteriores</p>
                                </div>

                                <!-- Previews Nuevas -->
                                <div v-if="imagePreview.length > 0" class="mt-6 flex flex-wrap gap-4 animate-fade-in">
                                    <div v-for="(src, idx) in imagePreview" :key="idx" class="relative group w-32 h-32 rounded-3xl overflow-hidden border-2 border-brand-500 shadow-lg">
                                        <img :src="src" class="w-full h-full object-cover" />
                                        <button @click.stop="removeSelected(idx)" class="absolute top-2 right-2 p-1.5 bg-rose-500 text-white rounded-xl shadow-lg hover:scale-110 transition-transform">
                                            <X class="w-3 h-3" />
                                        </button>
                                    </div>
                                </div>
                                <InputError :message="form.errors.Imagen_Publicacion" class="mt-2" />
                                <InputError :message="form.errors['Imagen_Publicacion.0']" class="mt-1" />
                                <InputError :message="form.errors['Imagen_Publicacion.1']" class="mt-1" />
                                <InputError :message="form.errors['Imagen_Publicacion.2']" class="mt-1" />
                            </div>

                            <div class="flex items-center justify-end gap-4 pt-4">
                                <Link :href="route('dashboard')" class="text-sm font-bold text-gray-400 hover:text-gray-900 transition-colors">
                                    Descartar cambios
                                </Link>
                                <PrimaryButton class="px-8 py-3.5 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 shadow-[0_8px_20px_-6px_rgba(124,58,237,0.6)]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Actualizar Publicación
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <!-- Modal Eliminar -->
    <Transition name="modal-fade">
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="w-full max-w-sm bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-3xl p-8 shadow-2xl">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 mx-auto mb-5">
                    <Trash2 class="w-7 h-7 text-rose-500" />
                </div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white text-center mb-2">¿Eliminar publicación?</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
                    Se eliminará <span class="font-black text-gray-700 dark:text-white">«{{ props.publicacion.Titulo_Publicacion }}»</span> permanentemente.
                </p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 px-4 py-3 text-xs font-black tracking-widest uppercase text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-white/5 border border-light-border dark:border-dark-border rounded-xl hover:bg-gray-200 dark:hover:bg-white/10 transition-all">Cancelar</button>
                    <button @click="handleDelete" class="flex-1 px-4 py-3 text-xs font-black tracking-widest uppercase text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-lg shadow-rose-500/20 transition-all">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Modal Borrador -->
    <Transition name="modal-fade">
        <div v-if="showDraftModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="w-full max-w-sm bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-3xl p-8 shadow-2xl">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 mx-auto mb-5">
                    <Archive class="w-7 h-7 text-amber-500" />
                </div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white text-center mb-2">¿Pasar a borrador?</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">La publicación se ocultará del marketplace. Solo tú podrás verla y podrás reactivarla después.</p>
                <div class="flex gap-3">
                    <button @click="showDraftModal = false" class="flex-1 px-4 py-3 text-xs font-black tracking-widest uppercase text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-white/5 border border-light-border dark:border-dark-border rounded-xl hover:bg-gray-200 dark:hover:bg-white/10 transition-all">Cancelar</button>
                    <button @click="handleDraft" class="flex-1 px-4 py-3 text-xs font-black tracking-widest uppercase text-white bg-amber-600 hover:bg-amber-500 rounded-xl shadow-lg shadow-amber-500/20 transition-all">Sí, convertir</button>
                </div>
            </div>
        </div>
    </Transition>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.modal-fade-enter-active, .modal-fade-leave-active { transition: all 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
