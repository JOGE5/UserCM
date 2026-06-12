<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { 
    UploadCloud, 
    X, 
    DollarSign,
    Plus as PlusIcon,
    Archive,
    AlertTriangle,
    CheckCircle2,
    Tag,
    MapPin,
    Eye
} from 'lucide-vue-next';

const props = defineProps({
    categorias: Array,
});

const form = useForm({
    Titulo_Publicacion: '',
    Descripcion_Publicacion: '',
    Estado_Publicacion: true,
    Precio_Publicacion: '',
    Imagen_Publicacion: [],
    Cod_Categoria: '',
    ubicacion: '',
    condicion_producto: 'usado',
});

const imagePreview = ref([]);
const fileInput = ref(null);
const isDragging = ref(false);

// Estado de modales
const showCloseConfirm = ref(false);
const showPublishConfirm = ref(false);

const hasData = computed(() => {
    return form.Titulo_Publicacion !== '' || 
           form.Descripcion_Publicacion !== '' || 
           form.Precio_Publicacion !== '' || 
           form.Imagen_Publicacion.length > 0;
});

const categoryName = computed(() => {
    const cat = props.categorias?.find(c => c.Cod_Categoria === form.Cod_Categoria);
    return cat ? cat.Nombre_Categoria : 'Sin categoría';
});

function attemptClose() {
    if (hasData.value) {
        showCloseConfirm.value = true;
    } else {
        router.visit(route('dashboard'));
    }
}

function handlePublish() {
    // Se ejecuta tras pasar la validación HTML5
    showPublishConfirm.value = true;
}

const submit = () => {
    form.post(route('publicaciones.store'), {
        forceFormData: true,
    });
};

function confirmPublish() {
    form.Estado_Publicacion = true;
    submit();
}

function saveDraft() {
    form.Estado_Publicacion = false;
    submit();
}

function discardChanges() {
    router.visit(route('dashboard'));
}

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

const handleDrop = (e) => {
    isDragging.value = false;
    processFiles(e.dataTransfer.files);
};

function removeSelected(index) {
    form.Imagen_Publicacion.splice(index, 1);
    imagePreview.value.splice(index, 1);
}
</script>

<template>
    <AppLayout title="Publicar Artículo">
        <!-- Overlay Principal de Pantalla Completa (Modal Backdrop) -->
        <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xl flex items-center justify-center p-4 sm:p-8 overflow-y-auto" @click.self="attemptClose">
            
            <!-- Contenedor del Formulario (Modal Principal) -->
            <div class="relative w-full max-w-4xl glass-panel rounded-[2.5rem] p-6 md:p-10 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] border border-white/20 dark:border-white/10 my-auto animate-in fade-in zoom-in-95 duration-500">
                
                <!-- Botón de Cerrar Esquina Superior -->
                <button @click="attemptClose" class="absolute top-6 right-6 p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-full transition-colors z-20">
                    <X class="w-6 h-6" />
                </button>

                <!-- Luces Internas -->
                <div class="absolute -top-32 -right-32 w-64 h-64 bg-brand-500/20 blur-[80px] rounded-full pointer-events-none"></div>
                <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-purple-500/20 blur-[80px] rounded-full pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="mb-8 text-center md:text-left">
                        <h2 class="text-3xl sm:text-4xl font-black tracking-tighter text-gray-900 dark:text-white">Nueva <span class="bg-clip-text text-transparent bg-gradient-to-r from-brand-500 to-purple-500">Publicación</span></h2>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-2">Marketplace Ecosistema Uni</p>
                    </div>

                    <form @submit.prevent="handlePublish" class="space-y-6">
                        <!-- Título -->
                        <div>
                            <InputLabel for="titulo" value="Título del Producto" />
                            <TextInput
                                id="titulo"
                                v-model="form.Titulo_Publicacion"
                                type="text"
                                class="mt-1 block w-full bg-white/50 dark:bg-black/30 border-white/20 dark:border-white/10 focus:ring-brand-500/50"
                                placeholder="Ej: Audífonos Sony WH-1000XM4"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.Titulo_Publicacion" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Precio -->
                            <div>
                                <InputLabel for="precio" value="Precio (Bs)" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                                        <DollarSign class="w-4 h-4 text-brand-500" />
                                    </div>
                                    <TextInput
                                        id="precio"
                                        v-model="form.Precio_Publicacion"
                                        type="number"
                                        step="0.01"
                                        min="1"
                                        max="1000000"
                                        class="block w-full pl-12 bg-white/50 dark:bg-black/30 border-white/20 dark:border-white/10"
                                        placeholder="0.00"
                                        required
                                    />
                                </div>
                                <InputError :message="form.errors.Precio_Publicacion" class="mt-2" />
                            </div>

                            <!-- Categoría -->
                            <div>
                                <InputLabel for="categoria" value="Categoría" />
                                <select
                                    id="categoria"
                                    v-model="form.Cod_Categoria"
                                    class="mt-1 block w-full px-5 py-3.5 bg-white/50 dark:bg-black/30 backdrop-blur-md border border-white/20 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-all font-medium text-sm shadow-inner appearance-none cursor-pointer"
                                    required
                                >
                                    <option value="" disabled>Selecciona una categoría</option>
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
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                                        <MapPin class="w-4 h-4 text-gray-400" />
                                    </div>
                                    <TextInput
                                        id="ubicacion"
                                        v-model="form.ubicacion"
                                        type="text"
                                        class="block w-full pl-12 bg-white/50 dark:bg-black/30 border-white/20 dark:border-white/10"
                                        placeholder="Ej: Campus Central..."
                                    />
                                </div>
                                <InputError :message="form.errors.ubicacion" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="condicion" value="Condición del producto" />
                                <select
                                    id="condicion"
                                    v-model="form.condicion_producto"
                                    class="mt-1 block w-full px-5 py-3.5 bg-white/50 dark:bg-black/30 backdrop-blur-md border border-white/20 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-all font-medium text-sm shadow-inner appearance-none cursor-pointer"
                                >
                                    <option value="usado">Usado</option>
                                    <option value="nuevo">Nuevo</option>
                                </select>
                                <InputError :message="form.errors.condicion_producto" class="mt-2" />
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <InputLabel for="descripcion" value="Descripción Detallada" />
                            <textarea
                                id="descripcion"
                                v-model="form.Descripcion_Publicacion"
                                rows="3"
                                class="mt-1 block w-full px-5 py-4 bg-white/50 dark:bg-black/30 backdrop-blur-md border border-white/20 dark:border-white/10 text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-all font-medium text-sm resize-none shadow-inner"
                                placeholder="Describe el estado, marca y detalles..."
                                required
                            ></textarea>
                            <InputError :message="form.errors.Descripcion_Publicacion" class="mt-2" />
                        </div>

                        <!-- Imágenes Dropzone -->
                        <div>
                            <InputLabel value="Fotografías (Máx. 3)" />
                            <div
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                :class="[
                                    'group relative mt-2 flex flex-col items-center justify-center p-8 border-2 border-dashed rounded-3xl transition-all duration-300',
                                    isDragging ? 'border-brand-500 bg-brand-500/10 scale-[1.01]' : 'border-white/20 dark:border-white/10 bg-white/10 dark:bg-black/20 hover:border-brand-500/40 hover:bg-white/20 dark:hover:bg-black/40'
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
                                
                                <div class="flex flex-col items-center text-center">
                                    <div class="mb-3 p-3 rounded-2xl bg-white/50 dark:bg-black/50 shadow-inner group-hover:scale-110 transition-transform">
                                        <UploadCloud class="w-8 h-8 text-brand-500" />
                                    </div>
                                    <p class="text-xs font-black text-gray-900 dark:text-white">Haz clic o arrastra fotos</p>
                                    <p class="text-[9px] font-bold text-gray-500 mt-1 uppercase tracking-widest">PNG, JPG • Máx 2MB</p>
                                </div>
                            </div>

                            <!-- Previews Grid -->
                            <TransitionGroup name="list" tag="div" v-if="imagePreview.length > 0" class="mt-4 grid grid-cols-3 gap-3">
                                <div v-for="(src, idx) in imagePreview" :key="idx" class="relative group aspect-square rounded-2xl overflow-hidden border border-white/20 dark:border-white/10 shadow-md">
                                    <img :src="src" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                        <button type="button" @click="removeSelected(idx)" class="p-2 bg-rose-500/80 hover:bg-rose-500 text-white rounded-full shadow-lg hover:scale-110 transition-all">
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </TransitionGroup>
                            <InputError :message="form.errors.Imagen_Publicacion" class="mt-2" />
                        </div>

                        <!-- Botones Inferiores -->
                        <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-white/10 dark:border-white/5">
                            <button type="button" @click="attemptClose" class="w-full sm:w-auto px-6 py-3 text-xs font-black tracking-widest uppercase text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Cancelar
                            </button>
                            <SecondaryButton type="button" @click="saveDraft" class="w-full sm:w-auto justify-center px-6 py-3 border-white/20 bg-white/5 hover:bg-white/10 dark:bg-white/5 dark:hover:bg-white/10" :disabled="form.processing">
                                <Archive class="w-4 h-4 mr-2" /> Borrador
                            </SecondaryButton>
                            <PrimaryButton type="submit" class="w-full sm:w-auto justify-center px-8 py-3 bg-brand-600 hover:bg-brand-500" :disabled="form.processing">
                                <Eye class="w-4 h-4 mr-2" /> Revisar y Publicar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN DE CIERRE -->
        <div v-if="showCloseConfirm" class="fixed inset-0 z-[60] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="glass-panel p-8 rounded-[2rem] max-w-sm w-full border border-white/10 text-center animate-in zoom-in-95 duration-300">
                <div class="mx-auto w-16 h-16 bg-rose-500/20 text-rose-500 flex items-center justify-center rounded-full mb-4">
                    <AlertTriangle class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-black text-white mb-2">¿Descartar cambios?</h3>
                <p class="text-gray-400 text-sm mb-6">Tienes datos ingresados. Si cierras ahora perderás tu progreso. ¿Qué deseas hacer?</p>
                <div class="flex flex-col gap-2">
                    <button @click="saveDraft" class="px-4 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl text-sm font-bold transition-all">Guardar en Borradores</button>
                    <button @click="discardChanges" class="px-4 py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-sm font-bold transition-all">Descartar todo</button>
                    <button @click="showCloseConfirm = false" class="px-4 py-3 text-gray-400 hover:text-white text-sm font-bold transition-all mt-2">Seguir editando</button>
                </div>
            </div>
        </div>

        <!-- MODAL DE RESUMEN ANTES DE PUBLICAR -->
        <div v-if="showPublishConfirm" class="fixed inset-0 z-[60] bg-black/80 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
            <div class="glass-panel p-6 sm:p-8 rounded-[2.5rem] max-w-lg w-full border border-white/20 shadow-2xl shadow-brand-500/20 animate-in zoom-in-95 duration-300">
                <div class="text-center mb-6">
                    <div class="mx-auto w-16 h-16 bg-brand-500/20 text-brand-400 flex items-center justify-center rounded-full mb-4 shadow-inner">
                        <CheckCircle2 class="w-8 h-8" />
                    </div>
                    <h3 class="text-2xl font-black text-white">Revisión Final</h3>
                    <p class="text-brand-400/80 text-xs font-bold uppercase tracking-widest mt-1">¿Todo listo para publicar?</p>
                </div>

                <div class="bg-black/30 rounded-2xl p-5 mb-6 space-y-4 border border-white/5">
                    <div>
                        <p class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1">Título</p>
                        <p class="text-white font-medium">{{ form.Titulo_Publicacion }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1">Precio</p>
                            <p class="text-brand-400 font-bold text-lg">Bs. {{ form.Precio_Publicacion }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1">Categoría</p>
                            <p class="text-white font-medium">{{ categoryName }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1">Imágenes adjuntas</p>
                        <p class="text-white font-medium">{{ form.Imagen_Publicacion.length }} archivo(s)</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="showPublishConfirm = false" class="flex-1 px-4 py-3.5 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-xl text-sm font-bold transition-all">
                        Volver a editar
                    </button>
                    <button @click="confirmPublish" :disabled="form.processing" class="flex-1 px-4 py-3.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg flex items-center justify-center gap-2">
                        <Sparkles class="w-4 h-4" />
                        <span v-if="form.processing">Publicando...</span>
                        <span v-else>¡Sí, Publicar ahora!</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(10px);
}
</style>
