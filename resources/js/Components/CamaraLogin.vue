<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as faceapi from 'face-api.js';
import axios from 'axios';

const props = defineProps({
    email: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['success', 'error', 'cancel']);

const videoElement = ref(null);
const canvasElement = ref(null);
const modelsLoaded = ref(false);
const stream = ref(null);
const status = ref('Cargando modelos de IA...');
const isLoading = ref(true);
const isAnalyzing = ref(false);
const frameTimeout = ref(null);

const loadModels = async () => {
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models')
        ]);
        modelsLoaded.value = true;
        status.value = 'Iniciando cámara...';
        await startCamera();
    } catch (err) {
        status.value = 'Error al cargar los modelos.';
        emit('error', 'Error al cargar modelos: ' + err.message);
        isLoading.value = false;
    }
};

const startCamera = async () => {
    try {
        stream.value = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
        if (videoElement.value) {
            videoElement.value.srcObject = stream.value;
        }
        status.value = 'Esperando a detectar rostro...';
        isLoading.value = false;
        
        // Empieza la rutina espaciada después de que el video empiece
        videoElement.value.onplaying = () => {
            scheduleNextFrame();
        };
    } catch (err) {
        status.value = 'Cámara no disponible.';
        emit('error', 'Cámara denegada: ' + err.message);
        isLoading.value = false;
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
    }
    if (frameTimeout.value) {
        clearTimeout(frameTimeout.value);
    }
};

const scheduleNextFrame = () => {
    if (!stream.value || !stream.value.active) return;
    frameTimeout.value = setTimeout(() => {
        analizarFrame();
    }, 600); // Escanear cada 600ms en vez de atascar el CPU
};

const verificarIdentidad = async (descriptorActual) => {
    try {
        status.value = 'Verificando rostro de forma segura...';
        
        // Enviamos el descriptor calculado para que PHP haga la matemática
        const loginRes = await axios.post(route('facial.verifyAndLogin'), { 
            email: props.email,
            descriptor_actual: descriptorActual
        });
        
        status.value = '¡Rostro reconocido! Redirigiendo...';
        stopCamera();
        
        emit('success');
        
        // El login passwordless o el sello 2FA ya fueron aplicados, ir al dashboard
        window.location.href = loginRes.data.redirect || '/dashboard';
    } catch (err) {
        // Mostrar el error real de PHP (ej. Distancia muy larga, no existe, etc)
        status.value = err.response?.data?.error || 'Error de conexión. Intenta de nuevo.';
        isAnalyzing.value = false;
        scheduleNextFrame();
    }
};

const analizarFrame = async () => {
    if (isLoading.value || !modelsLoaded.value || !videoElement.value || isAnalyzing.value) {
        if (!isAnalyzing.value && !isLoading.value) scheduleNextFrame();
        return;
    }

    isAnalyzing.value = true;

    try {
        const canvas = canvasElement.value;
        if(!canvas) {
            isAnalyzing.value = false;
            scheduleNextFrame();
            return;
        }
        canvas.width = videoElement.value.videoWidth;
        canvas.height = videoElement.value.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);
        
        // Agregar SsdMobilenetv1Options para subir al 80% la rigurosidad mínima
        // y evitar detectar sombras/backgrounds como multiples caras falsas.
        const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.8 });
        const detecciones = await faceapi
            .detectAllFaces(canvas, options)
            .withFaceLandmarks()
            .withFaceDescriptors();

        if (detecciones.length === 1) {
            // Se pausará el bucle logico mientras verificamos la identidad
            isLoading.value = true;
            status.value = 'Rostro detectado... evaluando similitud...';
            await verificarIdentidad(Array.from(detecciones[0].descriptor));
        } else if (detecciones.length > 1) {
            status.value = 'Múltiples rostros/siluetas detectadas. Por favor póngase solo bajo buena luz.';
            isAnalyzing.value = false;
            scheduleNextFrame();
        } else {
            status.value = 'Buscando tu rostro frontal...';
            isAnalyzing.value = false;
            scheduleNextFrame();
        }
    } catch (e) {
        // Ignorar errores esporadicos del canvas
        isAnalyzing.value = false;
        scheduleNextFrame();
    }
};

onMounted(() => {
    if(!props.email) {
        status.value = 'Por favor ingresa tu email primero.';
        return;
    }
    loadModels();
});

onBeforeUnmount(() => {
    stopCamera();
});
</script>

<template>
    <div class="flex flex-col items-center w-full p-6 mt-4 transition-all duration-500 bg-black/40 border border-indigo-500/30 rounded-2xl shadow-[0_0_15px_rgba(79,70,229,0.2)]">
        <div class="relative flex items-center justify-between w-full mb-4">
            <h3 class="text-lg font-semibold text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 to-white">
                Face ID Login
            </h3>
            <button @click="emit('cancel')" type="button" class="text-gray-400 transition-colors hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <div class="relative w-full max-w-[320px] aspect-square bg-black/60 rounded-full overflow-hidden mb-4 border-2 border-indigo-500/50">
            <video 
                ref="videoElement" 
                autoplay 
                muted 
                playsinline 
                class="object-cover w-full h-full"
                :class="{'opacity-75': isLoading}"
            ></video>
            
            <div v-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <!-- Scanner effect visual -->
                <div class="absolute inset-x-0 top-0 h-1 bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,1)] animate-[scan_2s_ease-in-out_infinite_alternate]"></div>
                <span class="text-xs font-medium text-indigo-300 max-w-[80%] text-center px-2 py-1 bg-black/60 rounded-md border border-indigo-500/20">{{ status }}</span>
            </div>

            <!-- Canvas oculto para procesar -->
            <canvas ref="canvasElement" class="hidden"></canvas>
        </div>

        <p class="text-sm text-center text-gray-400">{{ !isLoading ? status : '' }}</p>
    </div>
</template>

<style>
@keyframes scan {
  from { top: 0; }
  to { top: 100%; }
}
</style>
