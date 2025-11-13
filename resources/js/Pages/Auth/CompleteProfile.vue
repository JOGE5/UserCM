<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
    universidades: Array,
});

const currentStep = ref(1);
const selectedUniversidad = ref(null);
const carreras = ref([]);

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

const fetchCarreras = async (universidadId) => {
    const id = Number(universidadId);
    console.log('Fetching careers for universidad ID:', id); // Debug log
    if (id > 0) {
        try {
            const response = await axios.get('/api/carreras', { params: { universidad_id: id } });
            console.log('Fetched careers data:', response.data); // Debug log
            carreras.value = response.data;
        } catch (error) {
            console.error('Error fetching carreras:', error);
            carreras.value = [];
        }
    } else {
        console.log('No valid universidad ID provided, clearing careers'); // Debug log
        carreras.value = [];
    }
};

watch(() => form.Cod_Universidad, (newVal) => {
    form.Cod_Carrera = '';
    fetchCarreras(newVal);
});

const isStep1Valid = computed(() => {
    return form.Apellidos && form.Genero && form.Telefono;
});

const isStep2Valid = computed(() => {
    return true; // Fotos son opcionales, siempre válido
});

const isStep3Valid = computed(() => {
    return form.Cod_Universidad && form.Cod_Carrera;
});

const nextStep = () => {
    if (currentStep.value === 1 && isStep1Valid.value) {
        currentStep.value = 2;
    } else if (currentStep.value === 2 && isStep2Valid.value) {
        currentStep.value = 3;
    }
};

const prevStep = () => {
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

onMounted(() => {
    if (form.Cod_Universidad) {
        fetchCarreras(form.Cod_Universidad);
    }
});
</script>

<template>
    <Head title="Completar Perfil" />

    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-2xl max-h-screen p-6 overflow-y-auto bg-white rounded-lg shadow-xl">
            <!-- Barra de progreso -->
            <div class="mb-6">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium">Paso {{ currentStep }} de 3</span>
                    <span class="text-sm text-gray-500">{{ Math.round((currentStep / 3) * 100) }}% completado</span>
                </div>
                <div class="w-full h-2 bg-gray-200 rounded-full">
                    <div
                        class="h-2 transition-all duration-300 bg-blue-600 rounded-full"
                        :style="{ width: `${(currentStep / 3) * 100}%` }"
                    ></div>
                </div>
            </div>

            <h2 class="mb-6 text-2xl font-bold text-center">Completa tu Perfil</h2>

            <form @submit.prevent="submit" enctype="multipart/form-data">
                <!-- Paso 1: Datos Personales -->
                <div v-if="currentStep === 1" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">Paso 1: Datos Personales</h3>

                    <!-- Datos heredados (solo lectura) -->
                    <div>
                        <InputLabel for="name" value="Nombre" />
                        <TextInput
                            id="name"
                            name="name"
                            autocomplete="name"
                            v-model="form.name"
                            type="text"
                            class="block w-full mt-1"
                            readonly
                        />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            name="email"
                            autocomplete="email"
                            v-model="form.email"
                            type="email"
                            class="block w-full mt-1"
                            readonly
                        />
                    </div>

                    <!-- Apellidos -->
                    <div>
                        <InputLabel for="Apellidos" value="Apellidos" />
                        <TextInput
                            id="Apellidos"
                            name="Apellidos"
                            autocomplete="family-name"
                            v-model="form.Apellidos"
                            type="text"
                            class="block w-full mt-1"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="form.errors.Apellidos" />
                    </div>

                    <!-- Género -->
                    <div>
                        <InputLabel for="Genero" value="Género" />
                        <select
                            id="Genero"
                            name="Genero"
                            autocomplete="sex"
                            v-model="form.Genero"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="">Selecciona tu género</option>
                            <option value="Hombre">Hombre</option>
                            <option value="Mujer">Mujer</option>
                            <option value="Prefiero no decirlo">Prefiero no decirlo</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.Genero" />
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <InputLabel for="Telefono" value="Teléfono" />
                        <TextInput
                            id="Telefono"
                            name="Telefono"
                            autocomplete="tel"
                            v-model="form.Telefono"
                            type="tel"
                            class="block w-full mt-1"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.Telefono" />
                    </div>
                </div>

                <!-- Paso 2: Fotos -->
                <div v-if="currentStep === 2" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">Paso 2: Fotos de Perfil</h3>

                    <!-- Foto de Perfil -->
                    <div>
                        <InputLabel for="Foto_de_perfil" value="Foto de Perfil (Opcional)" />
                        <input
                            id="Foto_de_perfil"
                            name="Foto_de_perfil"
                            autocomplete="off"
                            type="file"
                            @input="form.Foto_de_perfil = $event.target.files[0]"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            accept="image/*"
                        />
                        <InputError class="mt-2" :message="form.errors.Foto_de_perfil" />
                    </div>

                    <!-- Foto de Portada -->
                    <div>
                        <InputLabel for="Foto_de_portada" value="Foto de Portada (Opcional)" />
                        <input
                            id="Foto_de_portada"
                            name="Foto_de_portada"
                            autocomplete="off"
                            type="file"
                            @input="form.Foto_de_portada = $event.target.files[0]"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            accept="image/*"
                        />
                        <InputError class="mt-2" :message="form.errors.Foto_de_portada" />
                    </div>
                </div>

                <!-- Paso 3: Universidad y Carrera -->
                <div v-if="currentStep === 3" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800">Paso 3: Universidad y Carrera</h3>

                    <!-- Universidad -->
                    <div>
                        <InputLabel for="Cod_Universidad" value="Universidad" />
                        <select
                            id="Cod_Universidad"
                            name="Cod_Universidad"
                            autocomplete="off"
                            v-model="form.Cod_Universidad"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="">Selecciona tu universidad</option>
                            <option
                                v-for="universidad in universidades"
                                :key="universidad.Cod_Universidad"
                                :value="Number(universidad.Cod_Universidad)"
                            >
                                {{ universidad.Nombre_Universidad }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.Cod_Universidad" />
                    </div>

                    <!-- Carrera -->
                    <div>
                        <InputLabel for="Cod_Carrera" value="Carrera" />
                        <select
                            id="Cod_Carrera"
                            name="Cod_Carrera"
                            autocomplete="off"
                            v-model="form.Cod_Carrera"
                            :disabled="!form.Cod_Universidad"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="">Selecciona tu carrera</option>
                            <option
                                v-for="carrera in carreras"
                                :key="carrera.Cod_Carrera"
                                :value="carrera.Cod_Carrera"
                            >
                                {{ carrera.Nombre_Carrera }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.Cod_Carrera" />
                    </div>


                </div>

                <!-- Botones de navegación -->
                <div class="flex justify-between mt-6">
                    <button
                        type="button"
                        @click="prevStep"
                        v-if="currentStep > 1"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Anterior
                    </button>

                    <div class="flex space-x-2">
                        <button
                            type="button"
                            @click="nextStep"
                            v-if="currentStep < 3"
                            :disabled="(currentStep === 1 && !isStep1Valid)"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Siguiente
                        </button>

                        <PrimaryButton
                            v-if="currentStep === 3"
                            :disabled="!isStep3Valid || form.processing"
                        >
                            Completar Perfil
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
