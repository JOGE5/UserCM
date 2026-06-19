<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LockKeyhole, ArrowRight, ShieldCheck } from 'lucide-vue-next';

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('force.password.update'));
};
</script>

<template>
    <Head title="Cambiar Contraseña" />

    <div class="min-h-screen bg-black flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Background gradients -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] opacity-20 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-500 blur-[100px] rounded-full mix-blend-screen"></div>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-indigo-500/20 rounded-2xl flex items-center justify-center border border-indigo-500/30 shadow-[0_0_30px_rgba(99,102,241,0.3)]">
                    <ShieldCheck class="w-8 h-8 text-indigo-400" />
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-black text-white tracking-tight">
                Protege tu cuenta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-400 max-w-sm mx-auto">
                Tu cuenta fue creada por un administrador. Por tu seguridad, debes establecer una contraseña personal antes de continuar.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
            <div class="bg-gray-900/60 backdrop-blur-xl py-8 px-4 shadow-2xl sm:rounded-3xl sm:px-10 border border-gray-800">
                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <label for="password" class="block text-xs font-black text-gray-400 uppercase tracking-widest">
                            Nueva contraseña
                        </label>
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <LockKeyhole class="h-5 w-5 text-gray-500" />
                            </div>
                            <input id="password" v-model="form.password" type="password" required
                                class="appearance-none block w-full pl-10 px-3 py-3 border border-gray-700 rounded-xl shadow-sm placeholder-gray-500 bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 sm:text-sm transition-all"
                                placeholder="Mínimo 8 caracteres" />
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-xs text-rose-400">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-black text-gray-400 uppercase tracking-widest">
                            Confirmar contraseña
                        </label>
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <LockKeyhole class="h-5 w-5 text-gray-500" />
                            </div>
                            <input id="password_confirmation" v-model="form.password_confirmation" type="password" required
                                class="appearance-none block w-full pl-10 px-3 py-3 border border-gray-700 rounded-xl shadow-sm placeholder-gray-500 bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 sm:text-sm transition-all"
                                placeholder="Repite la contraseña" />
                        </div>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing"
                            class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-gray-900 transition-all disabled:opacity-50 disabled:cursor-not-allowed group">
                            <span v-if="form.processing">Actualizando...</span>
                            <span v-else class="flex items-center gap-2">
                                Guardar y Continuar
                                <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Pequeño botón de logout por si acaso -->
                <div class="mt-6 text-center">
                    <Link :href="route('logout')" method="post" as="button" class="text-xs text-gray-500 hover:text-white transition-colors">
                        Cerrar sesión en su lugar
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
