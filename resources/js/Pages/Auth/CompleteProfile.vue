<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import InputError from '@/Components/InputError.vue';
import { User, Mail, Phone, Users, Image as ImageIcon, Camera, GraduationCap, BookOpen, ChevronLeft, ChevronRight, Check, ChevronDown, Upload, X, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    universidades: Array,
});

const currentStep = ref(1);
const selectedUniversidad = ref(null);
const carreras = ref([]);

// Estados para previsualización y carga
const profilePreview = ref(null);
const bannerPreview = ref(null);
const profileLoading = ref(false);
const bannerLoading = ref(false);
const profileProgress = ref(0);
const bannerProgress = ref(0);
const isDraggingProfile = ref(false);
const isDraggingBanner = ref(false);
const profileError = ref('');
const bannerError = ref('');

// Estados para la animación final de envío
const isSubmitting = ref(false);
const submitStatus = ref('Subiendo su información...');
const submitProgress = ref(0);
const showSuccess = ref(false);

const openGenero = ref(false);
const openUniversidad = ref(false);
const openCarrera = ref(false);

const closeAllSelects = () => {
    openGenero.value = false;
    openUniversidad.value = false;
    openCarrera.value = false;
};

const handleGlobalClick = (e) => {
    if (!e.target.closest('.custom-dropdown-container')) {
        closeAllSelects();
    }
};

onMounted(() => {
    document.addEventListener('click', handleGlobalClick);
    if (form.Cod_Universidad) {
        fetchCarreras(form.Cod_Universidad);
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleGlobalClick);
});

const form = useForm({
    user_id: props.user.id,
    name: props.user.name,
    email: props.user.email,
    Apellidos: '',
    Genero: '',
    Telefono: '',
    Foto_de_perfil: null,
    Foto_de_portada: null,
    Cod_Universidad: '',
    Cod_Carrera: '',
    Cod_Rol: '',
});

const isLoadingCarreras = ref(false);

const fetchCarreras = async (universidadId) => {
    const id = Number(universidadId);
    if (id > 0) {
        isLoadingCarreras.value = true;
        try {
            const response = await axios.get('/api/carreras', { params: { universidad_id: id } });
            carreras.value = response.data;
        } catch (error) {
            console.error('Error fetching carreras:', error);
            carreras.value = [];
        } finally {
            isLoadingCarreras.value = false;
        }
    } else {
        carreras.value = [];
    }
};

watch(() => form.Cod_Universidad, (newVal) => {
    form.Cod_Carrera = '';
    fetchCarreras(newVal);
});

const getUniversidadName = (id) => {
    const uni = props.universidades.find(u => Number(u.Cod_Universidad) === id);
    return uni ? uni.Nombre_Universidad : '';
};

const selectUniversidad = (id) => {
    form.Cod_Universidad = id;
    closeAllSelects();
};

const getCarreraName = (id) => {
    const car = carreras.value.find(c => c.Cod_Carrera === id);
    return car ? car.Nombre_Carrera : '';
};

const selectCarrera = (id) => {
    form.Cod_Carrera = id;
    closeAllSelects();
};

const isStep1Valid = computed(() => {
    return form.Apellidos && form.Genero && form.Telefono;
});

const isStep2Valid = computed(() => {
    return true; // Fotos son opcionales
});

const isStep3Valid = computed(() => {
    return form.Cod_Universidad && form.Cod_Carrera;
});

const nextStep = () => {
    closeAllSelects();
    if (currentStep.value === 1 && isStep1Valid.value) {
        currentStep.value = 2;
    } else if (currentStep.value === 2 && isStep2Valid.value) {
        currentStep.value = 3;
    }
};

const prevStep = () => {
    closeAllSelects();
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const simulateUpload = (type) => {
    const progress = type === 'profile' ? profileProgress : bannerProgress;
    const loading = type === 'profile' ? profileLoading : bannerLoading;

    loading.value = true;
    progress.value = 0;

    const interval = setInterval(() => {
        progress.value += Math.random() * 30;
        if (progress.value >= 100) {
            progress.value = 100;
            clearInterval(interval);
            setTimeout(() => {
                loading.value = false;
            }, 500);
        }
    }, 200);
};

const validateImage = (file, type) => {
    return new Promise((resolve) => {
        const allowedProfile = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        const allowedBanner = ['image/jpeg', 'image/jpg', 'image/png'];

        if (type === 'profile' && !allowedProfile.includes(file.type)) {
            resolve('Formato no válido (Usa GIF, JPG o PNG)');
            return;
        }
        if (type === 'banner' && !allowedBanner.includes(file.type)) {
            resolve('Para el banner solo JPG o PNG');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            resolve('La imagen supera los 5MB');
            return;
        }

        if (type === 'banner') {
            const img = new Image();
            img.src = URL.createObjectURL(file);
            img.onload = () => {
                URL.revokeObjectURL(img.src);
                if (img.width < 1500 || img.height < 500) {
                    resolve('Tamaño insuficiente (Min 1500x500px)');
                } else {
                    resolve(null);
                }
            };
        } else {
            resolve(null);
        }
    });
};

const handleFileSelect = async (event, type) => {
    const file = event.target.files?.[0] || event.dataTransfer?.files?.[0];
    if (!file) return;

    const errorRef = type === 'profile' ? profileError : bannerError;
    const previewRef = type === 'profile' ? profilePreview : bannerPreview;
    const formKey = type === 'profile' ? 'Foto_de_perfil' : 'Foto_de_portada';

    errorRef.value = ''; // Limpiar errores previos

    const error = await validateImage(file, type);
    if (error) {
        errorRef.value = error;
        return;
    }

    // Revocar URL anterior para evitar fugas de memoria
    if (previewRef.value) {
        URL.revokeObjectURL(previewRef.value);
    }

    form[formKey] = file;
    previewRef.value = URL.createObjectURL(file);
    simulateUpload(type);
};

const removeImage = (type) => {
    if (type === 'profile') {
        if (profilePreview.value) URL.revokeObjectURL(profilePreview.value);
        form.Foto_de_perfil = null;
        profilePreview.value = null;
        profileProgress.value = 0;
        profileError.value = '';
    } else {
        if (bannerPreview.value) URL.revokeObjectURL(bannerPreview.value);
        form.Foto_de_portada = null;
        bannerPreview.value = null;
        bannerProgress.value = 0;
        bannerError.value = '';
    }
};

const submit = () => {
    if (isStep3Valid.value) {
        form.post(route('profile.complete'), {
            forceFormData: true,
            onStart: () => {
                isSubmitting.value = true;
                submitStatus.value = 'Subiendo su información...';
                submitProgress.value = 10;
            },
            onProgress: (progress) => {
                if (progress.percentage) {
                    // Mapeo de progreso: los primeros 80% son la subida real
                    submitProgress.value = Math.min(progress.percentage * 0.8, 80);
                }
            },
            onSuccess: () => {
                submitStatus.value = 'Cargando datos...';
                submitProgress.value = 90;
                
                setTimeout(() => {
                    submitStatus.value = 'Proceso completado';
                    submitProgress.value = 100;
                    showSuccess.value = true;
                }, 800);
            },
            onFinish: () => {
                // El redireccionamiento ocurrirá automáticamente por Inertia, 
                // pero si queremos asegurar el "check" verde, podemos manejarlo así:
                if (showSuccess.value) {
                    // Mantener la pantalla de éxito 1.5s antes de que Inertia cambie la página
                }
            }
        });
    }
};
</script>

<template>
    <Head title="Completar Perfil | Campus Market" />

    <div class="relative flex flex-col items-center justify-center min-h-screen px-4 py-10 overflow-hidden bg-black sm:px-6 lg:px-8">

        <!-- Video de fondo oscuro -->
        <video autoplay muted loop playsinline class="absolute inset-0 z-0 object-cover w-full h-full opacity-60">
            <source src="/videos/Waza21.mp4" type="video/mp4" />
        </video>

        <!-- Capas de sombras y gradientes para efecto premium -->
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/60 to-black/40"></div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(0,0,0,0.8)_100%)]"></div>

        <!-- Luces de fondo (Aurora) -->
        <div class="absolute z-0 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-600/20 rounded-full blur-[120px] mix-blend-screen opacity-50 pointer-events-none"></div>

        <!-- Tarjeta Glassmorphism -->
        <div class="relative z-20 w-full max-w-2xl p-8 sm:p-12 transition-all duration-500 bg-white/5 shadow-[0_0_50px_rgba(0,0,0,0.5)] rounded-[2.5rem] border border-white/10 backdrop-blur-xl mt-10">

            <div class="flex flex-col items-center mb-10 text-center">
                <div class="flex items-center justify-center w-20 h-20 mb-6 transition-transform duration-500 overflow-hidden rounded-[1.5rem] bg-white/10 shadow-[0_0_20px_rgba(255,255,255,0.05)] hover:scale-110">
                    <img src="/images/posters/logo-team.png" alt="Logo" class="object-cover w-full h-full p-2" />
                </div>
                <h2 class="text-3xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-300 sm:text-4xl">
                    Completa tu Perfil
                </h2>
                <p class="mt-3 text-sm font-medium text-gray-400">Personaliza tu cuenta para una mejor experiencia</p>
            </div>

            <!-- Barra de progreso Premium -->
            <div class="mb-10">
                <div class="flex flex-col items-center justify-between gap-2 mb-4 sm:flex-row">
                    <span class="text-xs font-bold tracking-widest text-gray-400 uppercase">Paso {{ currentStep }} de 3</span>
                    <span class="px-3 py-1 text-xs font-bold text-indigo-300 border rounded-full bg-indigo-500/20 border-indigo-500/30 backdrop-blur-sm">{{ Math.round((currentStep / 3) * 100) }}% completado</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-white/10 overflow-hidden relative shadow-inner">
                    <div
                        class="absolute top-0 left-0 h-full transition-all duration-700 ease-out rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.6)]"
                        :style="{ width: `${(currentStep / 3) * 100}%` }"
                    ></div>
                </div>
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6">

                <!--================ PASO 1: DATOS PERSONALES ================-->
                <Transition name="fade-slide" mode="out-in">
                    <div v-if="currentStep === 1" key="step1" class="relative z-10 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Nombre (Readonly) -->
                            <div class="space-y-2">
                                <label for="name" class="block ml-1 text-sm font-medium text-gray-300">Nombres</label>
                                <div class="relative group/input">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <User class="w-5 h-5 text-gray-500" />
                                    </div>
                                    <input id="name" v-model="form.name" type="text" readonly
                                        class="w-full py-3.5 pl-12 pr-4 text-gray-400 transition-all duration-300 bg-white/5 border border-white/5 rounded-2xl focus:outline-none cursor-not-allowed" />
                                </div>
                            </div>

                            <!-- Apellidos -->
                            <div class="space-y-2">
                                <label for="Apellidos" class="block ml-1 text-sm font-medium text-gray-300">Apellidos</label>
                                <div class="relative group/input">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <Users class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" />
                                    </div>
                                    <input id="Apellidos" v-model="form.Apellidos" type="text" required autofocus
                                        placeholder="Tus apellidos"
                                        class="w-full py-3.5 pl-12 pr-4 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm" />
                                </div>
                                <InputError class="mt-1 text-xs text-red-500" :message="form.errors.Apellidos" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Email (Readonly) -->
                            <div class="space-y-2">
                                <label for="email" class="block ml-1 text-sm font-medium text-gray-300">Email</label>
                                <div class="relative group/input">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <Mail class="w-5 h-5 text-gray-500" />
                                    </div>
                                    <input id="email" v-model="form.email" type="email" readonly
                                        class="w-full py-3.5 pl-12 pr-4 text-gray-400 transition-all duration-300 bg-white/5 border border-white/5 rounded-2xl focus:outline-none cursor-not-allowed" />
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div class="space-y-2">
                                <label for="Telefono" class="block ml-1 text-sm font-medium text-gray-300">Teléfono</label>
                                <div class="relative group/input">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <Phone class="w-5 h-5 text-gray-500 transition-colors group-focus-within/input:text-indigo-400" />
                                    </div>
                                    <input id="Telefono" v-model="form.Telefono" type="tel" required
                                        placeholder="Ej. +591 71234567"
                                        class="w-full py-3.5 pl-12 pr-4 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl placeholder-gray-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 backdrop-blur-sm" />
                                </div>
                                <InputError class="mt-1 text-xs text-red-500" :message="form.errors.Telefono" />
                            </div>
                        </div>

                        <!-- Género (Dropdown Custom) -->
                        <div class="relative space-y-2 custom-dropdown-container" v-if="currentStep === 1">
                            <label class="block ml-1 text-sm font-medium text-gray-300">Género</label>

                            <!-- Fake Input for trigger -->
                            <div class="relative group/input" @click="openGenero = !openGenero; openUniversidad = false; openCarrera = false">
                                <div class="w-full py-3.5 pl-4 pr-10 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl flex items-center justify-between cursor-pointer focus:outline-none ring-1 ring-transparent hover:border-indigo-500 backdrop-blur-sm" :class="{'border-indigo-500 ring-indigo-500 shadow-[0_0_15px_rgba(99,102,241,0.2)]': openGenero}">
                                    <span class="block w-full truncate" :class="{'text-gray-500': !form.Genero}">
                                        {{ form.Genero || 'Selecciona tu género' }}
                                    </span>
                                </div>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <ChevronDown class="w-5 h-5 transition-transform duration-300" :class="openGenero ? 'rotate-180 text-indigo-400' : 'text-gray-500'" />
                                </div>
                            </div>

                            <!-- Custom Dropdown Menu -->
                            <Transition name="dropdown">
                                <div v-if="openGenero" class="absolute z-50 w-full mt-2 overflow-hidden bg-[#0a0a0d] border border-white/10 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] backdrop-blur-xl">
                                    <div class="py-2 overflow-y-auto max-h-60 custom-scrollbar">
                                        <div @click="form.Genero = 'Masculino'; closeAllSelects()" class="flex items-center justify-between px-5 py-3 text-sm text-gray-300 transition-colors cursor-pointer hover:text-white hover:bg-white/10" :class="{'bg-indigo-500/20 text-indigo-300': form.Genero === 'Masculino'}">
                                            <span>Masculino</span>
                                            <Check v-if="form.Genero === 'Masculino'" class="w-4 h-4 text-indigo-400" />
                                        </div>
                                        <div @click="form.Genero = 'Femenino'; closeAllSelects()" class="flex items-center justify-between px-5 py-3 text-sm text-gray-300 transition-colors cursor-pointer hover:text-white hover:bg-white/10" :class="{'bg-indigo-500/20 text-indigo-300': form.Genero === 'Femenino'}">
                                            <span>Femenino</span>
                                            <Check v-if="form.Genero === 'Femenino'" class="w-4 h-4 text-indigo-400" />
                                        </div>
                                        <div @click="form.Genero = 'Otro'; closeAllSelects()" class="flex items-center justify-between px-5 py-3 text-sm text-gray-300 transition-colors cursor-pointer hover:text-white hover:bg-white/10" :class="{'bg-indigo-500/20 text-indigo-300': form.Genero === 'Otro'}">
                                            <span>Otro</span>
                                            <Check v-if="form.Genero === 'Otro'" class="w-4 h-4 text-indigo-400" />
                                        </div>
                                    </div>
                                </div>
                            </Transition>

                            <!-- Hidden native select just for form binding/validation implicitly (optional, handled by v-model custom UI) -->
                            <select v-model="form.Genero" class="hidden" required>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>

                            <InputError class="mt-1 text-xs text-red-500" :message="form.errors.Genero" />
                        </div>
                    </div>
                </Transition>

                <!--================ PASO 2: FOTOS ================-->
                <Transition name="fade-slide" mode="out-in">
                    <div v-if="currentStep === 2" key="step2" class="space-y-10">
                        
                        <!-- Sección Foto de Perfil (Diseño Formal) -->
                        <div class="flex flex-col items-center justify-center space-y-6">
                            <h3 class="text-sm font-black tracking-widest text-indigo-300 uppercase">Avatar de Usuario</h3>
                            
                            <div 
                                @dragover.prevent="isDraggingProfile = true"
                                @dragleave.prevent="isDraggingProfile = false"
                                @drop.prevent="isDraggingProfile = false; handleFileSelect($event, 'profile')"
                                class="relative p-1.5 rounded-full border-2 border-dashed transition-all duration-500 hover:scale-105 active:scale-95"
                                :class="isDraggingProfile ? 'border-indigo-500 bg-indigo-500/10' : 'border-white/10 bg-white/5'"
                            >
                                <label for="Foto_de_perfil" class="relative block w-40 h-40 overflow-hidden rounded-full cursor-pointer group">
                                    <input id="Foto_de_perfil" type="file" @change="handleFileSelect($event, 'profile')" accept="image/*" class="hidden" />
                                    
                                    <!-- Preview Existente -->
                                    <div v-if="profilePreview" class="absolute inset-0 z-10">
                                        <img :src="profilePreview" class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110" />
                                        <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 bg-black/60 backdrop-blur-sm group-hover:opacity-100">
                                            <RefreshCw class="w-8 h-8 text-white animate-spin-slow" />
                                        </div>
                                    </div>

                                    <!-- Loading State -->
                                    <div v-if="profileLoading" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/80 backdrop-blur-md">
                                        <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                                        <span class="mt-2 text-[10px] font-bold text-indigo-400">{{ Math.round(profileProgress) }}%</span>
                                    </div>

                                    <!-- Placeholder Vacío -->
                                    <div v-else-if="!profilePreview" class="flex flex-col items-center justify-center w-full h-full bg-black/40 group-hover:bg-black/60 transition-colors">
                                        <Camera class="w-10 h-10 text-gray-500 transition-colors group-hover:text-indigo-400" />
                                        <span class="mt-2 text-[10px] font-bold text-gray-500 group-hover:text-indigo-300">AÑADIR FOTO</span>
                                    </div>
                                </label>

                                <!-- Botón de eliminar si hay imagen -->
                                <button v-if="profilePreview && !profileLoading" @click.prevent="removeImage('profile')" class="absolute top-0 right-0 z-30 p-2 text-white transition-transform bg-rose-500 rounded-full shadow-lg hover:scale-110 active:scale-90">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="text-xs text-gray-400">Recomendado: Imagen cuadrada de perfil formal</p>
                                <Transition name="fade">
                                    <p v-if="profileError" class="mt-2 text-xs font-bold text-rose-500 flex items-center justify-center gap-1.5 animate-bounce">
                                        <X class="w-3.5 h-3.5" /> {{ profileError }}
                                    </p>
                                </Transition>
                            </div>
                        </div>

                        <div class="w-full h-px bg-white/5"></div>

                        <!-- Sección Banner (Diseño Formal Panorámico) -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-2">
                                <h3 class="text-sm font-black tracking-widest text-purple-300 uppercase">Encabezado de Perfil</h3>
                                <span class="text-[10px] font-black text-gray-500 tracking-wider">RECOMENDADO 1500×500</span>
                            </div>

                            <div 
                                @dragover.prevent="isDraggingBanner = true"
                                @dragleave.prevent="isDraggingBanner = false"
                                @drop.prevent="isDraggingBanner = false; handleFileSelect($event, 'banner')"
                                class="relative group"
                            >
                                <label 
                                    for="Foto_de_portada" 
                                    class="relative block w-full aspect-[3/1] rounded-[2rem] overflow-hidden transition-all duration-500 border-2 border-dashed bg-white/5 backdrop-blur-md cursor-pointer"
                                    :class="[
                                        isDraggingBanner ? 'border-purple-500 bg-purple-500/10' : 'border-white/10 hover:border-purple-500/50',
                                        bannerPreview ? 'border-solid border-white/20' : ''
                                    ]"
                                >
                                    <input id="Foto_de_portada" type="file" @change="handleFileSelect($event, 'banner')" accept="image/*" class="hidden" />

                                    <!-- Preview Existente -->
                                    <div v-if="bannerPreview" class="absolute inset-0 z-10 w-full h-full">
                                        <img :src="bannerPreview" class="object-cover w-full h-full transition-transform duration-1000 group-hover:scale-105" />
                                        <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 bg-black/40 backdrop-blur-sm group-hover:opacity-100">
                                            <div class="flex items-center gap-3 px-6 py-3 bg-white/10 rounded-2xl backdrop-blur-xl border border-white/20 shadow-2xl scale-90 group-hover:scale-100 transition-transform">
                                                <RefreshCw class="w-5 h-5 text-white animate-spin-slow" />
                                                <span class="text-xs font-bold text-white">REEMPLAZAR BANNER</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Loading State -->
                                    <div v-if="bannerLoading" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/80 backdrop-blur-md">
                                        <div class="w-64 h-1.5 bg-white/10 rounded-full overflow-hidden">
                                            <div class="h-full bg-purple-500 transition-all duration-300" :style="{ width: bannerProgress + '%' }"></div>
                                        </div>
                                        <span class="mt-3 text-[10px] font-black tracking-widest text-purple-400">CARGANDO {{ Math.round(bannerProgress) }}%</span>
                                    </div>

                                    <!-- Placeholder Vacío -->
                                    <div v-else-if="!bannerPreview" class="flex flex-col items-center justify-center w-full h-full space-y-3 p-6 text-center">
                                        <div class="p-4 rounded-2xl bg-white/5 group-hover:bg-purple-500/20 transition-all group-hover:scale-110 shadow-xl">
                                            <ImageIcon class="w-8 h-8 text-gray-500 group-hover:text-purple-400" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-300">Arrastra o selecciona el banner</p>
                                            <p class="text-[10px] text-gray-500 font-bold">1500 × 500 px • Máx 5MB</p>
                                        </div>
                                    </div>
                                </label>

                                <!-- Botón de eliminar si hay imagen -->
                                <button v-if="bannerPreview && !bannerLoading" @click.prevent="removeImage('banner')" class="absolute top-4 right-4 z-30 p-2.5 text-white transition-transform bg-rose-500/80 hover:bg-rose-500 rounded-xl shadow-xl hover:scale-110 active:scale-90 backdrop-blur-sm">
                                    <X class="w-5 h-5" />
                                </button>
                            </div>

                            <Transition name="fade">
                                <p v-if="bannerError" class="text-xs font-bold text-rose-500 flex items-center justify-start gap-2 ml-2 px-4 py-2 bg-rose-500/10 border border-rose-500/20 rounded-xl max-w-fit">
                                    <X class="w-4 h-4" /> {{ bannerError }}
                                </p>
                            </Transition>
                        </div>
                    </div>
                </Transition>

                <!--================ PASO 3: UNIVERSIDAD Y CARRERA ================-->
                <Transition name="fade-slide" mode="out-in">
                    <div v-if="currentStep === 3" key="step3" class="relative z-10 space-y-6">
                        <!-- Universidad (Dropdown Custom) -->
                        <div class="relative space-y-2 custom-dropdown-container">
                            <label class="block ml-1 text-sm font-medium text-gray-300">Universidad</label>
                            <div class="relative group/input" @click="openUniversidad = !openUniversidad; openCarrera = false; openGenero = false">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <BookOpen class="w-5 h-5 transition-colors" :class="openUniversidad ? 'text-indigo-400' : 'text-gray-500 group-hover:text-indigo-400'" />
                                </div>
                                <div class="w-full py-3.5 pl-12 pr-10 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl flex items-center cursor-pointer focus:outline-none ring-1 ring-transparent hover:border-indigo-500 backdrop-blur-sm" :class="{'border-indigo-500 ring-indigo-500 shadow-[0_0_15px_rgba(99,102,241,0.2)]': openUniversidad}">
                                    <span class="block w-full truncate" :class="{'text-gray-500': !form.Cod_Universidad}">
                                        {{ getUniversidadName(form.Cod_Universidad) || 'Selecciona tu universidad' }}
                                    </span>
                                </div>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <ChevronDown class="w-5 h-5 transition-transform duration-300" :class="openUniversidad ? 'rotate-180 text-indigo-400' : 'text-gray-500'" />
                                </div>
                            </div>

                            <Transition name="dropdown">
                                <div v-if="openUniversidad" class="absolute z-50 w-full mt-2 overflow-hidden bg-[#0a0a0d] border border-white/10 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] backdrop-blur-xl">
                                    <div class="py-2 overflow-y-auto max-h-60 custom-scrollbar">
                                        <div v-for="universidad in universidades" :key="universidad.Cod_Universidad"
                                            @click="selectUniversidad(Number(universidad.Cod_Universidad))"
                                            class="flex items-center justify-between px-5 py-3 text-sm text-gray-300 transition-colors cursor-pointer hover:text-white hover:bg-white/10"
                                            :class="{'bg-indigo-500/20 text-indigo-300': form.Cod_Universidad === Number(universidad.Cod_Universidad)}">
                                            <span class="pr-4 truncate">{{ universidad.Nombre_Universidad }}</span>
                                            <Check v-if="form.Cod_Universidad === Number(universidad.Cod_Universidad)" class="w-4 h-4 text-indigo-400 shrink-0" />
                                        </div>
                                    </div>
                                </div>
                            </Transition>

                            <select v-model="form.Cod_Universidad" class="hidden" required>
                                <option v-for="universidad in universidades" :key="universidad.Cod_Universidad" :value="Number(universidad.Cod_Universidad)"></option>
                            </select>

                            <InputError class="mt-1 text-xs text-red-500" :message="form.errors.Cod_Universidad" />
                        </div>

                        <!-- Carrera (Dropdown Custom) -->
                        <div class="relative space-y-2 custom-dropdown-container">
                            <label class="block ml-1 text-sm font-medium text-gray-300">Carrera</label>
                            <div class="relative group/input" @click="!form.Cod_Universidad ? null : (openCarrera = !openCarrera, openUniversidad = false)" :class="{'opacity-50 cursor-not-allowed': !form.Cod_Universidad}">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <GraduationCap class="w-5 h-5 transition-colors" :class="openCarrera ? 'text-indigo-400' : 'text-gray-500 group-hover:text-indigo-400'" />
                                </div>
                                <div class="w-full py-3.5 pl-12 pr-10 text-white transition-all duration-300 bg-black/40 border border-white/10 rounded-2xl flex items-center focus:outline-none ring-1 ring-transparent backdrop-blur-sm"
                                     :class="{
                                        'cursor-pointer hover:border-indigo-500': form.Cod_Universidad,
                                        'cursor-not-allowed': !form.Cod_Universidad,
                                        'border-indigo-500 ring-indigo-500 shadow-[0_0_15px_rgba(99,102,241,0.2)]': openCarrera
                                     }">
                                    <span class="block w-full truncate" :class="{'text-gray-500': !form.Cod_Carrera}">
                                        {{ getCarreraName(form.Cod_Carrera) || 'Selecciona tu carrera' }}
                                    </span>
                                </div>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <ChevronDown class="w-5 h-5 transition-transform duration-300" :class="openCarrera ? 'rotate-180 text-indigo-400' : 'text-gray-500'" />
                                </div>
                            </div>

                            <Transition name="dropdown">
                                <div v-if="openCarrera && form.Cod_Universidad" class="absolute z-50 w-full mt-2 overflow-hidden bg-[#0a0a0d] border border-white/10 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] backdrop-blur-xl">
                                    <div class="py-2 overflow-y-auto max-h-60 custom-scrollbar">
                                        <div v-if="isLoadingCarreras" class="flex flex-col items-center justify-center gap-3 px-5 py-6 text-sm text-center text-indigo-400">
                                            <svg class="w-6 h-6 text-indigo-400 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>Cargando carreras...</span>
                                        </div>
                                        <template v-else>
                                            <div v-for="carrera in carreras" :key="carrera.Cod_Carrera"
                                                 @click="selectCarrera(carrera.Cod_Carrera)"
                                                 class="flex items-center justify-between px-5 py-3 text-sm text-gray-300 transition-colors cursor-pointer hover:text-white hover:bg-white/10"
                                                 :class="{'bg-indigo-500/20 text-indigo-300': form.Cod_Carrera === carrera.Cod_Carrera}">
                                                <span class="pr-4 truncate">{{ carrera.Nombre_Carrera }}</span>
                                                <Check v-if="form.Cod_Carrera === carrera.Cod_Carrera" class="w-4 h-4 text-indigo-400 shrink-0" />
                                            </div>
                                            <div v-if="carreras.length === 0" class="px-5 py-4 text-sm text-center text-gray-500">
                                                No se encontraron carreras
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </Transition>

                            <select v-model="form.Cod_Carrera" class="hidden" required :disabled="!form.Cod_Universidad">
                                <option v-for="carrera in carreras" :key="carrera.Cod_Carrera" :value="carrera.Cod_Carrera"></option>
                            </select>

                            <InputError class="mt-1 text-xs text-red-500" :message="form.errors.Cod_Carrera" />
                        </div>

                        <div class="p-6 border bg-indigo-900/20 border-indigo-500/30 rounded-3xl backdrop-blur-md">
                            <div class="flex items-start gap-4">
                                <div class="p-3 rounded-full bg-indigo-500/20 shrink-0">
                                    <GraduationCap class="w-6 h-6 text-indigo-400" />
                                </div>
                                <div>
                                    <h4 class="mb-1 text-sm font-bold text-white">Casi listo para comenzar</h4>
                                    <p class="text-sm leading-relaxed text-indigo-200/70">
                                        Esta información permite personalizar la experiencia en Campus Market X Unifranz, ofreciéndote contenido y artículos sumamente útiles.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Footer de Controles / Navegación -->
                <div class="relative z-0 flex items-center justify-between pt-6 mt-6 border-t border-white/10">
                    <button
                        type="button"
                        v-if="currentStep > 1"
                        @click="prevStep"
                        class="flex items-center gap-2 px-6 py-3.5 text-sm font-bold text-gray-300 transition-all border rounded-2xl border-white/10 bg-white/5 hover:bg-white/10 hover:text-white focus:outline-none"
                    >
                        <ChevronLeft class="w-5 h-5" />
                        Anterior
                    </button>
                    <div v-else></div> <!-- Placeholder para flex-between -->

                    <button
                        type="button"
                        v-if="currentStep < 3"
                        @click="nextStep"
                        :disabled="(currentStep === 1 && !isStep1Valid) || (currentStep === 2 && !isStep2Valid)"
                        class="flex items-center gap-2 px-8 py-3.5 text-sm font-bold text-white transition-all bg-indigo-600 rounded-2xl group hover:bg-indigo-500 hover:scale-[1.02] active:scale-[0.98] shadow-[0_0_20px_rgba(79,70,229,0.3)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:hover:bg-indigo-600"
                    >
                        Siguiente
                        <ChevronRight class="w-5 h-5 transition-transform group-hover:translate-x-1" />
                    </button>

                    <button
                        type="submit"
                        v-else
                        :disabled="!isStep3Valid || form.processing"
                        class="relative flex items-center justify-center gap-2 px-8 py-3.5 text-sm font-bold text-white transition-all bg-emerald-600 rounded-2xl group hover:bg-emerald-500 hover:scale-[1.02] active:scale-[0.98] shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_40px_rgba(16,185,129,0.5)] overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:hover:bg-emerald-600"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            Finalizar y Entrar
                            <Check class="w-5 h-5" />
                        </span>
                        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-white/20 to-transparent"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!--================ OVERLAY DE CARGA / SUBIDA FINAL ================-->
    <Transition name="fade-overlay">
        <div v-if="isSubmitting" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 backdrop-blur-2xl">
            <!-- Partículas de fondo suaves -->
            <div class="absolute inset-0 z-0 overflow-hidden opacity-30">
                <div class="absolute w-[500px] h-[500px] bg-indigo-500/20 rounded-full blur-[120px] top-1/4 -left-1/4 animate-pulse"></div>
                <div class="absolute w-[500px] h-[500px] bg-purple-500/20 rounded-full blur-[120px] bottom-1/4 -right-1/4 animate-pulse" style="animation-delay: 2s"></div>
            </div>

            <div class="relative z-10 w-full max-w-md p-8 text-center">
                <!-- Icono Dinámico / Check de Éxito -->
                <div class="flex justify-center mb-10">
                    <div v-if="!showSuccess" class="relative">
                        <div class="w-24 h-24 border-4 border-indigo-500/20 rounded-full"></div>
                        <div 
                            class="absolute top-0 left-0 w-24 h-24 border-4 border-indigo-500 rounded-full border-t-transparent animate-spin"
                            :style="{ animationDuration: '1s' }"
                        ></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <Upload class="w-8 h-8 text-indigo-400 animate-bounce" />
                        </div>
                    </div>
                    
                    <!-- Check Animado (SVG Path) -->
                    <div v-else class="success-checkmark">
                        <div class="check-icon">
                            <span class="icon-line line-tip"></span>
                            <span class="icon-line line-long"></span>
                            <div class="icon-circle"></div>
                            <div class="icon-fix"></div>
                        </div>
                    </div>
                </div>

                <h3 class="mb-4 text-2xl font-black tracking-tighter text-white uppercase sm:text-3xl">
                    {{ submitStatus }}
                </h3>
                
                <div class="relative w-full h-2 mb-4 overflow-hidden rounded-full bg-white/10 shadow-inner">
                    <div 
                        class="absolute h-full transition-all duration-700 ease-out bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.6)]"
                        :style="{ width: `${submitProgress}%` }"
                    ></div>
                </div>
                
                <p class="text-xs font-bold tracking-widest text-gray-500 uppercase">
                    {{ Math.round(submitProgress) }}% completado
                </p>

                <div v-if="showSuccess" class="mt-8 transition-all duration-500 animate-fade-in">
                    <p class="text-sm font-medium text-indigo-300">¡Bienvenido a Campus Market!</p>
                    <p class="text-[10px] text-gray-500 mt-1">Redirigiendo al Dashboard...</p>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 8s linear infinite;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.fade-overlay-enter-active, .fade-overlay-leave-active {
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-overlay-enter-from, .fade-overlay-leave-to {
    opacity: 0;
    backdrop-filter: blur(0px);
}

/* Success Checkmark Animation Styles */
.success-checkmark {
    width: 80px;
    height: 115px;
    margin: 0 auto;
}
.success-checkmark .check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #10b981;
}
.success-checkmark .check-icon::before {
    top: 3px; left: -2px; width: 30px; transform-origin: 100% 50%; border-radius: 100px 0 0 100px;
}
.success-checkmark .check-icon::after {
    top: 0; left: 30px; width: 60px; transform-origin: 0 50%; border-radius: 0 100px 100px 0; animation: rotate-circle 4.25s ease-in;
}
.success-checkmark .check-icon .icon-line {
    height: 5px; background-color: #10b981; display: block; border-radius: 2px; position: absolute; z-index: 10;
}
.success-checkmark .check-icon .icon-line.line-tip {
    top: 46px; left: 14px; width: 25px; transform: rotate(45deg); animation: icon-line-tip 0.75s;
}
.success-checkmark .check-icon .icon-line.line-long {
    top: 38px; right: 8px; width: 47px; transform: rotate(-45deg); animation: icon-line-long 0.75s;
}
.success-checkmark .check-icon .icon-circle {
    top: -4px; left: -4px; z-index: 10; width: 80px; height: 80px; border-radius: 50%; border: 4px solid rgba(16, 185, 129, 0.2); position: absolute; box-sizing: content-box;
}
.success-checkmark .check-icon .icon-fix {
    top: 8px; width: 5px; left: 26px; z-index: 1; height: 85px; position: absolute; transform: rotate(-45deg); background-color: transparent;
}

@keyframes icon-line-tip {
    0% { width: 0; left: 1px; top: 19px; }
    54% { width: 0; left: 1px; top: 19px; }
    70% { width: 50px; left: -8px; top: 37px; }
    84% { width: 17px; left: 21px; top: 48px; }
    100% { width: 25px; left: 14px; top: 46px; }
}
@keyframes icon-line-long {
    0% { width: 0; right: 46px; top: 54px; }
    65% { width: 0; right: 46px; top: 54px; }
    84% { width: 55px; right: 0px; top: 35px; }
    100% { width: 47px; right: 8px; top: 38px; }
}

@keyframes rotate-circle {
    0% { transform: rotate(-45deg); }
    5% { transform: rotate(-45deg); }
    12% { transform: rotate(-405deg); }
    100% { transform: rotate(-405deg); }
}

.animate-fade-in {
    animation: fade-in 0.8s ease-out forwards;
}
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: top;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: scaleY(0.95) translateY(-10px);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
