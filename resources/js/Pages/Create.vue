<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import FormSection from '@/Components/FormSection.vue';
import { 
    UploadCloud, 
    X, 
    ChevronRight, 
    Sparkles, 
    Package, 
    Info, 
    DollarSign,
    ImageIcon,
    CheckCircle2,
    Plus as PlusIcon
} from 'lucide-vue-next';

defineProps({
    categorias: Array,
});

const form = useForm({
    Titulo_Publicacion: '',
    Descripcion_Publicacion: '',
    Estado_Publicacion: true,
    Precio_Publicacion: '',
    Imagen_Publicacion: [],
    Cod_Categoria: '',
});

const imagePreview = ref([]);
const fileInput = ref(null);
const isDragging = ref(false);

const submit = () => {
    form.post(route('publicaciones.store'), {
        forceFormData: true,
        onSuccess: () => {
            // Éxito
        },
    });
};

function processFiles(files) {
    const arr = Array.from(files || []);
    if (arr.length === 0) return;
    
    // Validar archivos
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
    if (fileInput.value) fileInput.value.value = null; // Reset input
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
        <template #header>
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">
                    Nueva <span class="text-brand-600 dark:text-brand-400">Publicación</span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">Marketplace Ecosistema Uni</p>
            </div>
        </template>

        <div class="max-w-5xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-20">
                <!-- Formulario Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] p-8 shadow-sm">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Título -->
                            <div>
                                <InputLabel for="titulo" value="Título del Producto" />
                                <TextInput
                                    id="titulo"
                                    v-model="form.Titulo_Publicacion"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="¿Qué estás vendiendo?"
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
                                            class="block w-full pl-12"
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
                                        class="mt-1 block w-full px-5 py-3.5 bg-gray-50/50 dark:bg-white/5 border border-light-border dark:border-dark-border text-gray-900 dark:text-white rounded-2xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all duration-300 font-bold text-sm outline-none"
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

                            <!-- Descripción -->
                            <div>
                                <InputLabel for="descripcion" value="Descripción Detallada" />
                                <textarea
                                    id="descripcion"
                                    v-model="form.Descripcion_Publicacion"
                                    rows="5"
                                    class="mt-1 block w-full px-5 py-4 bg-gray-50/50 dark:bg-white/5 border border-light-border dark:border-dark-border text-gray-900 dark:text-white rounded-[2rem] focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all duration-300 font-medium text-sm outline-none resize-none"
                                    placeholder="Describe el estado, marca y detalles de entrega del producto..."
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
                                        'group relative mt-2 flex flex-col items-center justify-center p-12 border-2 border-dashed rounded-[2.5rem] transition-all duration-500',
                                        isDragging ? 'border-brand-500 bg-brand-500/10 scale-[1.02]' : 'border-light-border dark:border-dark-border bg-gray-50/30 dark:bg-black/10 hover:border-brand-500/50 hover:bg-brand-500/5'
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
                                        <div class="mb-4 p-4 rounded-3xl bg-white dark:bg-dark-surface shadow-xl shadow-brand-500/10 transition-transform duration-500 group-hover:scale-110">
                                            <UploadCloud class="w-10 h-10 text-brand-600" />
                                        </div>
                                        <p class="text-sm font-black text-gray-900 dark:text-white">Haz clic o arrastra tus fotos</p>
                                        <p class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-widest">PNG, JPG o JPEG • Max 2MB</p>
                                    </div>
                                </div>

                                <!-- Previews Grid -->
                                <TransitionGroup 
                                    name="list" 
                                    tag="div" 
                                    v-if="imagePreview.length > 0" 
                                    class="mt-8 grid grid-cols-2 sm:grid-cols-3 gap-4"
                                >
                                    <div 
                                        v-for="(src, idx) in imagePreview" 
                                        :key="idx" 
                                        class="relative group aspect-square rounded-[2rem] overflow-hidden border-2 border-light-border dark:border-dark-border shadow-md"
                                    >
                                        <img :src="src" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <button 
                                                type="button"
                                                @click="removeSelected(idx)"
                                                class="p-2 bg-rose-500 text-white rounded-xl shadow-lg hover:scale-110 active:scale-95 transition-all"
                                            >
                                                <X class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>
                                </TransitionGroup>
                                <InputError :message="form.errors.Imagen_Publicacion" class="mt-2" />
                            </div>

                            <!-- Botones -->
                            <div class="flex items-center justify-end gap-4 pt-6">
                                <Link 
                                    :href="route('dashboard')" 
                                    class="px-6 py-3 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors"
                                >
                                    Cancelar
                                </Link>
                                <PrimaryButton 
                                    type="submit"
                                    class="px-8 py-3.5 shadow-xl shadow-brand-500/30"
                                    :class="{ 'opacity-25': form.processing }" 
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">Publicando...</span>
                                    <div v-else class="flex items-center gap-2">
                                        <PlusIcon class="w-5 h-5" />
                                        <span>Crear Publicación</span>
                                    </div>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Barra Lateral de Consejos -->
                <div class="space-y-6">
                    <div class="bg-brand-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-brand-500/20 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-125 transition-transform duration-700">
                             <Sparkles class="w-32 h-32" />
                        </div>
                        
                        <div class="relative z-10">
                            <h3 class="text-xl font-black mb-4">Consejos Pro</h3>
                            <ul class="space-y-4">
                                <li class="flex gap-3">
                                    <div class="shrink-0"><CheckCircle2 class="w-5 h-5 text-brand-300" /></div>
                                    <p class="text-sm font-medium leading-relaxed">Usa fotos reales con buena iluminación natural.</p>
                                </li>
                                <li class="flex gap-3">
                                    <div class="shrink-0"><CheckCircle2 class="w-5 h-5 text-brand-300" /></div>
                                    <p class="text-sm font-medium leading-relaxed">Sé honesto con los detalles y el estado del producto.</p>
                                </li>
                                <li class="flex gap-3">
                                    <div class="shrink-0"><CheckCircle2 class="w-5 h-5 text-brand-300" /></div>
                                    <p class="text-sm font-medium leading-relaxed">Publicar en la categoría correcta ayuda a vender más rápido.</p>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-brand-500/10 rounded-xl">
                                <Info class="w-5 h-5 text-brand-600" />
                            </div>
                            <h3 class="font-black text-gray-900 dark:text-white">Normativa</h3>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed mb-4">
                            Al publicar, aceptas los términos de convivencia de Campus Market. Se prohíbe la venta de artículos no autorizados.
                        </p>
                        <p class="text-[10px] font-black text-brand-600 uppercase tracking-widest">Campus Market Safe</p>
                    </div>
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
