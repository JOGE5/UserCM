<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import { Hash, Image as ImageIcon, ArrowLeft, ChevronRight, X, Upload, Send } from 'lucide-vue-next';

const props = defineProps({ categorias: Array, carreras: Array });

const form = useForm({
    Titulo_Foro: '',
    Descripcion_Foro: '',
    Cod_Categoria: null,
    Imagen_Foro: null,
    tipo_acceso: 'abierto',
    carrera_destino: null,
});

const imagePreview = ref(null);
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
    form.post(route('productos.store'));
}
</script>

<template>
    <AppLayout title="Nueva Discusión">
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('productos')" class="flex items-center gap-1.5 text-[10px] font-black tracking-widest uppercase text-gray-400 hover:text-brand-500 transition-colors">
                    <ArrowLeft class="w-3.5 h-3.5" />
                    Foros
                </Link>
                <ChevronRight class="w-3 h-3 text-gray-300 dark:text-gray-600" />
                <span class="text-[10px] font-black tracking-widest uppercase text-brand-500">Nueva Discusión</span>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-20">
            <div class="bg-white dark:bg-dark-surface border border-light-border dark:border-dark-border rounded-[2.5rem] overflow-hidden shadow-xl shadow-black/5">

                <!-- Banner superior -->
                <div class="relative h-24 bg-gradient-to-br from-brand-600/80 via-brand-500/60 to-indigo-600/80 overflow-hidden">
                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                    <div class="absolute inset-0 flex items-center px-8 gap-3">
                        <div class="p-2.5 rounded-xl bg-white/20 border border-white/30 backdrop-blur-md">
                            <Hash class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg font-black text-white leading-none">Nueva Discusión</h1>
                            <p class="text-[10px] text-white/70 font-bold mt-0.5">Comparte tu tema con la comunidad universitaria</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 lg:p-10 space-y-7">

                    <!-- Título -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="titulo" class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Título del tema</label>
                            <span class="text-[10px] font-bold" :class="titleCount > 45 ? 'text-rose-500' : 'text-gray-400'">{{ titleCount }}/50</span>
                        </div>
                        <input
                            id="titulo"
                            v-model="form.Titulo_Foro"
                            type="text"
                            maxlength="50"
                            placeholder="¿Sobre qué quieres hablar?"
                            class="w-full px-5 py-3.5 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-black/30 border border-light-border dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all placeholder-gray-400 outline-none font-medium"
                        />
                        <InputError :message="form.errors.Titulo_Foro" />
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="descripcion" class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Descripción</label>
                            <span class="text-[10px] font-bold" :class="descCount > 90 ? 'text-rose-500' : 'text-gray-400'">{{ descCount }}/100</span>
                        </div>
                        <textarea
                            id="descripcion"
                            v-model="form.Descripcion_Foro"
                            rows="4"
                            maxlength="100"
                            placeholder="Explica tu tema con detalle. Cuanta más información compartas, mejor podrán ayudarte..."
                            class="w-full px-5 py-3.5 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-black/30 border border-light-border dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all placeholder-gray-400 outline-none resize-none leading-relaxed"
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

                    <!-- Tipo de Acceso (Abierto / Exclusivo) -->
                    <div class="space-y-4 p-5 bg-brand-500/5 border border-brand-500/20 rounded-[2rem]">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest">Privacidad de la Sala</label>
                            <div class="flex gap-3">
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" v-model="form.tipo_acceso" value="abierto" class="peer sr-only" />
                                    <div class="p-4 bg-white dark:bg-black/40 border border-light-border dark:border-dark-border rounded-2xl peer-checked:border-brand-500 peer-checked:ring-2 peer-checked:ring-brand-500/20 transition-all text-center">
                                        <span class="block text-sm font-black text-gray-900 dark:text-white mb-1">🌍 Foro Abierto</span>
                                        <span class="block text-[10px] text-gray-500">Cualquier estudiante puede entrar</span>
                                    </div>
                                </label>
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" v-model="form.tipo_acceso" value="exclusivo" class="peer sr-only" />
                                    <div class="p-4 bg-white dark:bg-black/40 border border-light-border dark:border-dark-border rounded-2xl peer-checked:border-brand-500 peer-checked:ring-2 peer-checked:ring-brand-500/20 transition-all text-center">
                                        <span class="block text-sm font-black text-gray-900 dark:text-white mb-1">🔒 Exclusivo</span>
                                        <span class="block text-[10px] text-gray-500">Solo para una carrera específica</span>
                                    </div>
                                </label>
                            </div>
                            <InputError :message="form.errors.tipo_acceso" />
                        </div>

                        <!-- Selector de Carrera (Solo si es exclusivo) -->
                        <div v-if="form.tipo_acceso === 'exclusivo'" class="space-y-2 pt-2 animate-fade-in-up">
                            <label for="carrera_destino" class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">¿Para qué carrera es?</label>
                            <select
                                id="carrera_destino"
                                v-model="form.carrera_destino"
                                class="w-full px-5 py-3.5 text-sm text-gray-900 dark:text-white bg-white dark:bg-black/30 border border-light-border dark:border-dark-border rounded-2xl focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all outline-none font-medium appearance-none cursor-pointer"
                            >
                                <option :value="null" disabled>Selecciona la carrera permitida...</option>
                                <option v-for="c in carreras" :key="c.Cod_Carrera" :value="c.Cod_Carrera">{{ c.Nombre_Carrera }}</option>
                            </select>
                            <InputError :message="form.errors.carrera_destino" />
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">Imagen de portada <span class="text-gray-400 normal-case font-bold">(opcional, máx 2MB)</span></label>

                        <!-- Preview -->
                        <div v-if="imagePreview" class="relative rounded-2xl overflow-hidden border border-light-border dark:border-dark-border group">
                            <img :src="imagePreview" class="w-full h-48 object-cover" />
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-black/50 transition-opacity">
                                <button @click="removeImage" type="button" class="flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-xs font-black rounded-xl shadow-lg">
                                    <X class="w-4 h-4" /> Quitar imagen
                                </button>
                            </div>
                        </div>

                        <!-- Upload area -->
                        <label v-else for="imagen" class="flex flex-col items-center justify-center gap-3 w-full h-36 border-2 border-dashed border-light-border dark:border-dark-border rounded-2xl cursor-pointer hover:border-brand-500/50 hover:bg-brand-500/5 transition-all group">
                            <input id="imagen" type="file" accept="image/*" @change="handleImage" class="hidden" />
                            <div class="p-3 rounded-xl bg-gray-100 dark:bg-white/5 group-hover:bg-brand-500/10 transition-colors">
                                <ImageIcon class="w-6 h-6 text-gray-400 group-hover:text-brand-500 transition-colors" />
                            </div>
                            <div class="text-center">
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400">Arrastra o haz clic para subir</p>
                                <p class="text-[10px] text-gray-400">JPG, PNG, GIF · Máx 2MB</p>
                            </div>
                        </label>

                        <p v-if="imageError" class="text-xs font-bold text-rose-500">{{ imageError }}</p>
                        <InputError :message="form.errors.Imagen_Foro" />
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center gap-3 pt-2">
                        <Link
                            :href="route('productos')"
                            class="flex-1 flex items-center justify-center px-6 py-3.5 text-xs font-black tracking-widest uppercase text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-white/5 border border-light-border dark:border-dark-border rounded-2xl hover:bg-gray-200 dark:hover:bg-white/10 transition-all"
                        >
                            Cancelar
                        </Link>
                        <button
                            @click.prevent="submit"
                            :disabled="form.processing"
                            class="flex-[2] flex items-center justify-center gap-2 px-6 py-3.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-black tracking-widest uppercase rounded-2xl shadow-lg shadow-brand-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <Send class="w-4 h-4" />
                            {{ form.processing ? 'Publicando...' : 'Publicar Discusión' }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
