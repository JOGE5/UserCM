<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, Lock, ShieldAlert, ServerCrash } from 'lucide-vue-next';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: 'Servicio no disponible',
        500: 'Error del servidor',
        404: 'Página no encontrada',
        403: 'Acceso Denegado',
        429: 'Demasiadas Peticiones',
    }[props.status] || 'Ha ocurrido un error';
});

const description = computed(() => {
    return {
        503: 'Estamos realizando tareas de mantenimiento. Vuelve en unos minutos.',
        500: 'Vaya, algo salió mal en nuestros servidores. Ya estamos trabajando en ello.',
        404: 'Lo sentimos, la página que estás buscando no existe o ha sido movida.',
        403: 'No tienes los permisos necesarios para acceder a esta sección.',
        429: 'Has realizado demasiadas acciones en muy poco tiempo. Por favor, espera un momento antes de volver a intentarlo.',
    }[props.status] || 'Ha ocurrido un error inesperado. Por favor, intenta de nuevo más tarde.';
});

const IconComponent = computed(() => {
    return {
        503: ServerCrash,
        500: ServerCrash,
        404: AlertTriangle,
        403: Lock,
        429: ShieldAlert,
    }[props.status] || AlertTriangle;
});

const gradient = computed(() => {
    return {
        503: 'from-orange-500 to-amber-500',
        500: 'from-red-500 to-rose-500',
        404: 'from-brand-500 to-purple-500',
        403: 'from-rose-500 to-pink-500',
        429: 'from-amber-500 to-red-500',
    }[props.status] || 'from-brand-500 to-purple-500';
});
</script>

<template>
    <div class="min-h-screen bg-dynamic font-sans text-light-text dark:text-dark-text flex items-center justify-center p-6 relative overflow-hidden">
        <Head :title="title" />

        <!-- Decoración de fondo -->
        <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
            <div :class="`absolute top-[10%] left-[10%] w-[40vw] h-[40vw] rounded-full bg-gradient-to-br ${gradient} opacity-[0.15] blur-[100px] mix-blend-screen animate-blob`"></div>
            <div :class="`absolute bottom-[10%] right-[10%] w-[35vw] h-[35vw] rounded-full bg-gradient-to-tr ${gradient} opacity-[0.1] blur-[120px] mix-blend-screen animate-blob animation-delay-2000`"></div>
        </div>

        <div class="relative z-10 w-full max-w-lg">
            <div class="glass-panel p-10 md:p-14 rounded-[3rem] shadow-2xl border border-white/20 dark:border-white/5 flex flex-col items-center text-center float-3d transition-all duration-700">
                
                <div class="relative mb-8 group">
                    <div :class="`absolute inset-0 bg-gradient-to-r ${gradient} blur-2xl opacity-50 group-hover:opacity-80 transition-opacity duration-500 rounded-full`"></div>
                    <div class="relative bg-white dark:bg-dark-surface p-6 rounded-full border border-light-border dark:border-dark-border shadow-xl">
                        <component :is="IconComponent" class="w-16 h-16 opacity-90 text-gray-800 dark:text-white" />
                    </div>
                </div>

                <h1 class="text-6xl md:text-8xl font-black tracking-tighter mb-4">
                    <span :class="`text-transparent bg-clip-text bg-gradient-to-r ${gradient} drop-shadow-sm`">
                        {{ status }}
                    </span>
                </h1>

                <h2 class="text-2xl md:text-3xl font-black tracking-tight text-gray-900 dark:text-white mb-4">
                    {{ title }}
                </h2>

                <p class="text-base text-gray-600 dark:text-gray-400 font-medium leading-relaxed mb-10 max-w-sm">
                    {{ description }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 w-full">
                    <button 
                        @click="$inertia.visit('/')"
                        class="flex-1 inline-flex justify-center items-center px-6 py-3.5 rounded-2xl text-sm font-bold text-gray-700 dark:text-gray-200 bg-white/50 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 border border-gray-200 dark:border-white/10 transition-all duration-300 backdrop-blur-md"
                    >
                        Volver atrás
                    </button>
                    <Link 
                        href="/" 
                        :class="`flex-1 inline-flex justify-center items-center px-6 py-3.5 rounded-2xl text-sm font-black text-white bg-gradient-to-r ${gradient} hover:scale-105 active:scale-95 transition-all duration-300 shadow-[0_8px_20px_-6px_rgba(0,0,0,0.3)]`"
                    >
                        Ir al inicio
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
  animation: blob 10s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
}
.animation-delay-2000 {
  animation-delay: 2s;
}
</style>
