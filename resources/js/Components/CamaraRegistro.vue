<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as faceapi from '@vladmandic/face-api';

const emit = defineEmits(['capturado', 'error']);

const videoElement = ref(null);
const canvasElement = ref(null);
const modelsLoaded = ref(false);
const stream = ref(null);
const status = ref('Cargando modelos de IA...');
const isLoading = ref(true);

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
        emit('error', 'Error al cargar modelos de face-api: ' + err.message);
        isLoading.value = false;
    }
};

const startCamera = async () => {
    try {
        stream.value = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
        if (videoElement.value) {
            videoElement.value.srcObject = stream.value;
        }
        status.value = 'Alinea tu rostro y toma la foto';
        isLoading.value = false;
    } catch (err) {
        status.value = 'Cámara no disponible o denegada.';
        emit('error', 'Error al acceder a la cámara: ' + err.message);
        isLoading.value = false;
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
    }
};

const capturarYConvertir = async () => {
    if (!videoElement.value || !modelsLoaded.value) return;

    isLoading.value = true;
    status.value = 'Procesando rostro...';

    try {
        const canvas = canvasElement.value;
        canvas.width = videoElement.value.videoWidth;
        canvas.height = videoElement.value.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);
        
        const base64 = canvas.toDataURL('image/jpeg', 0.8);

        // Detectar rostro
        const deteccion = await faceapi
            .detectSingleFace(canvas)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!deteccion) {
            throw new Error('No se detectó ningún rostro. Intenta de nuevo con mejor luz.');
        }

        // Generar descriptor
        const descriptor = Array.from(deteccion.descriptor);

        status.value = 'Rostro capturado correctamente';
        emit('capturado', { base64, descriptor });
    } catch (err) {
        status.value = err.message;
        emit('error', err.message);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    loadModels();
});

onBeforeUnmount(() => {
    stopCamera();
});
</script>

<template>
    <div class="flex flex-col items-center w-full p-4 bg-black/40 border border-white/10 rounded-2xl">
        <h3 class="mb-4 text-lg font-semibold text-white">Reconocimiento Facial (Opcional)</h3>
        
        <div class="relative w-full max-w-[320px] aspect-video bg-black/60 rounded-xl overflow-hidden mb-4 border border-white/20">
            <video 
                ref="videoElement" 
                autoplay 
                muted 
                playsinline 
                class="object-cover w-full h-full"
                :class="{'opacity-50': isLoading}"
            ></video>
            
            <div v-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <span class="text-sm font-medium text-indigo-300 animate-pulse">{{ status }}</span>
            </div>

            <!-- Canvas oculto para el procesamiento -->
            <canvas ref="canvasElement" class="hidden"></canvas>
        </div>

        <p class="mb-4 text-sm text-center text-gray-400 min-h-[40px]">{{ status }}</p>

        <button 
            type="button" 
            @click="capturarYConvertir" 
            :disabled="isLoading || !modelsLoaded"
            class="px-6 py-2 text-sm font-medium text-white transition-all bg-indigo-600 rounded-xl hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            Tomar foto y Analizar
        </button>
    </div>
</template>
