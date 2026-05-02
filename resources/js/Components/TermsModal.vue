<script setup>
import { X, ShieldCheck, AlertTriangle, BookOpen, UserCheck } from 'lucide-vue-next';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
</script>

<template>
    <Transition
        enter-active-class="duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <Transition
                enter-active-class="duration-300 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="show" class="relative w-full max-w-2xl overflow-hidden bg-black/90 border border-white/10 rounded-[2.5rem] shadow-2xl backdrop-blur-xl max-h-[90vh] flex flex-col">
                    <!-- Decoración de fondo -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600/10 rounded-full blur-[80px] -mr-32 -mt-32 pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-600/10 rounded-full blur-[80px] -ml-32 -mb-32 pointer-events-none"></div>

                    <!-- Cabecera -->
                    <div class="relative flex items-center justify-between p-6 border-b border-white/10">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-indigo-500/20 rounded-xl">
                                <ShieldCheck class="w-6 h-6 text-indigo-400" />
                            </div>
                            <h3 class="text-xl font-bold text-white">Términos y Condiciones</h3>
                        </div>
                        <button @click="close" class="p-2 transition-colors rounded-full text-gray-400 hover:bg-white/10 hover:text-white">
                            <X class="w-6 h-6" />
                        </button>
                    </div>

                    <!-- Contenido Scrollable -->
                    <div class="relative flex-1 p-8 overflow-y-auto space-y-8 custom-scrollbar">
                        <section class="space-y-4">
                            <div class="flex items-center space-x-2 text-indigo-400">
                                <BookOpen class="w-5 h-5" />
                                <h4 class="font-bold uppercase tracking-wider text-xs">Propósito de la Plataforma</h4>
                            </div>
                            <p class="text-gray-300 leading-relaxed">
                                Campus Market es un espacio exclusivo para la comunidad universitaria, diseñado para facilitar la compra y venta de materiales académicos, libros, apuntes y recursos relacionados con la educación superior.
                            </p>
                        </section>

                        <section class="space-y-4">
                            <div class="flex items-center space-x-2 text-emerald-400">
                                <UserCheck class="w-5 h-5" />
                                <h4 class="font-bold uppercase tracking-wider text-xs">Uso de Datos Personales</h4>
                            </div>
                            <p class="text-gray-300 leading-relaxed">
                                La información personal recolectada durante el registro (nombre, correo electrónico y datos biométricos si se proporcionan) será utilizada estrictamente para:
                            </p>
                            <ul class="list-disc list-inside text-gray-400 space-y-2 ml-2">
                                <li>Verificar la identidad de los usuarios.</li>
                                <li>Facilitar la comunicación entre compradores y vendedores.</li>
                                <li>Enviar notificaciones relevantes sobre transacciones o actualizaciones del sistema.</li>
                                <li>Garantizar la seguridad dentro de la plataforma.</li>
                            </ul>
                        </section>

                        <section class="space-y-4">
                            <div class="flex items-center space-x-2 text-red-400">
                                <AlertTriangle class="w-5 h-5" />
                                <h4 class="font-bold uppercase tracking-wider text-xs">Normas y Prohibiciones</h4>
                            </div>
                            <div class="p-4 bg-red-500/5 border border-red-500/20 rounded-2xl space-y-3">
                                <p class="text-gray-300 leading-relaxed">
                                    Está terminantemente prohibido subir, publicar o compartir:
                                </p>
                                <ul class="list-disc list-inside text-gray-400 space-y-1 ml-2 text-sm">
                                    <li>Contenido ofensivo, discriminatorio o violento.</li>
                                    <li>Material que infrinja derechos de autor.</li>
                                    <li>Artículos ilegales o no relacionados con el ámbito universitario.</li>
                                    <li>Publicidad engañosa o spam.</li>
                                </ul>
                                <p class="text-red-400 font-medium text-sm pt-2">
                                    IMPORTANTE: El incumplimiento de estas normas resultará en el BLOQUEO PERMANENTE de su cuenta sin previo aviso.
                                </p>
                            </div>
                        </section>
                    </div>

                    <!-- Footer -->
                    <div class="relative p-6 border-t border-white/10 bg-white/5 backdrop-blur-md">
                        <button 
                            @click="close"
                            class="w-full py-3 font-bold text-white transition-all bg-indigo-600 rounded-2xl hover:bg-indigo-500 hover:shadow-[0_0_20px_rgba(79,70,229,0.4)]"
                        >
                            Entendido y Acepto
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
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
