<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import InputError from '@/Components/InputError.vue';
import { User, Mail, Phone, Users, Image as ImageIcon, Camera, GraduationCap, BookOpen, ChevronLeft, ChevronRight, Check, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    universidades: Array,
});

const currentStep = ref(1);
const selectedUniversidad = ref(null);
const carreras = ref([]);

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

const submit = () => {
    if (isStep3Valid.value) {
        form.post(route('profile.complete'), {
            forceFormData: true,
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
                    <div v-if="currentStep === 2" key="step2" class="space-y-6">
                        <!-- Foto de Perfil -->
                        <div class="space-y-2">
                            <label class="block ml-1 text-sm font-medium text-gray-300">Foto de Perfil (Opcional)</label>
                            <label for="Foto_de_perfil" class="flex flex-col items-center justify-center w-full min-h-[160px] cursor-pointer transition-all duration-300 bg-black/40 border-2 border-dashed border-white/20 rounded-3xl hover:border-indigo-400 hover:bg-black/60 group backdrop-blur-sm p-6 overflow-hidden relative">
                                <input id="Foto_de_perfil" name="Foto_de_perfil" type="file" @change="form.Foto_de_perfil = $event.target.files[0]" accept="image/*" class="absolute inset-0 z-10 w-full h-full opacity-0 cursor-pointer" />
                                <div v-if="!form.Foto_de_perfil" class="flex flex-col items-center justify-center space-y-3 text-center">
                                    <div class="p-4 transition-transform rounded-full bg-white/5 group-hover:scale-110 group-hover:bg-indigo-500/20">
                                        <Camera class="w-8 h-8 text-gray-400 group-hover:text-indigo-400" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-300 group-hover:text-indigo-300">Haz clic o arrastra una imagen</p>
                                    <p class="text-xs text-gray-500">PNG, JPG hasta 5MB</p>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center text-center">
                                    <div class="p-3 text-emerald-400 bg-emerald-400/10 rounded-full mb-3 shadow-[0_0_20px_rgba(52,211,153,0.2)]">
                                        <Check class="w-8 h-8" />
                                    </div>
                                    <p class="text-sm font-bold text-emerald-300">{{ form.Foto_de_perfil.name }}</p>
                                    <p class="mt-1 text-xs text-gray-400">Haz clic para cambiar</p>
                                </div>
                            </label>
                            <InputError class="mt-1 text-xs text-red-500" :message="form.errors.Foto_de_perfil" />
                        </div>

                        <!-- Foto de Portada -->
                        <div class="space-y-2">
                            <label class="block ml-1 text-sm font-medium text-gray-300">Foto de Portada (Opcional)</label>
                            <label for="Foto_de_portada" class="flex flex-col items-center justify-center w-full min-h-[160px] cursor-pointer transition-all duration-300 bg-black/40 border-2 border-dashed border-white/20 rounded-3xl hover:border-indigo-400 hover:bg-black/60 group backdrop-blur-sm p-6 overflow-hidden relative">
                                <input id="Foto_de_portada" name="Foto_de_portada" type="file" @change="form.Foto_de_portada = $event.target.files[0]" accept="image/*" class="absolute inset-0 z-10 w-full h-full opacity-0 cursor-pointer" />
                                <div v-if="!form.Foto_de_portada" class="flex flex-col items-center justify-center space-y-3 text-center">
                                    <div class="p-4 transition-transform rounded-full bg-white/5 group-hover:scale-110 group-hover:bg-indigo-500/20">
                                        <ImageIcon class="w-8 h-8 text-gray-400 group-hover:text-indigo-400" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-300 group-hover:text-indigo-300">Haz clic o arrastra una imagen</p>
                                    <p class="text-xs text-gray-500">Panorámica recomendada, JPG/PNG</p>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center text-center">
                                    <div class="p-3 text-emerald-400 bg-emerald-400/10 rounded-full mb-3 shadow-[0_0_20px_rgba(52,211,153,0.2)]">
                                        <Check class="w-8 h-8" />
                                    </div>
                                    <p class="text-sm font-bold text-emerald-300">{{ form.Foto_de_portada.name }}</p>
                                    <p class="mt-1 text-xs text-gray-400">Haz clic para cambiar</p>
                                </div>
                            </label>
                            <InputError class="mt-1 text-xs text-red-500" :message="form.errors.Foto_de_portada" />
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
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.4s ease;
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(30px);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-30px);
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
