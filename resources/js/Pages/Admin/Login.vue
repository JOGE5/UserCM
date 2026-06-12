<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { ShieldCheck, Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('admin.login.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceso Admin | Campus Market" />

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-black px-4 py-12">
        <!-- Video de fondo (mismo que el login de estudiantes) -->
        <video autoplay muted loop playsinline class="absolute inset-0 z-0 h-full w-full object-cover opacity-60">
            <source src="/videos/Waza21.mp4" type="video/mp4" />
        </video>

        <!-- Capas de gradiente y viñeta para profundidad -->
        <div class="absolute inset-0 z-0 bg-gradient-to-t from-black via-black/60 to-black/40"></div>
        <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(0,0,0,0.8)_100%)]"></div>

        <!-- Aurora indigo -->
        <div class="pointer-events-none absolute left-1/2 top-1/2 z-0 h-[500px] w-[500px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-600/20 opacity-50 mix-blend-screen blur-[100px]"></div>

        <div class="relative z-20 w-full max-w-md">
            <!-- Encabezado -->
            <div class="mb-8 flex flex-col items-center text-center">
                <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-indigo-500/30 bg-indigo-500/10 shadow-[0_0_30px_rgba(99,102,241,0.25)]">
                    <ShieldCheck class="h-8 w-8 text-indigo-400" />
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">Panel de Administración</h1>
                <p class="mt-2 text-sm text-gray-500">Acceso exclusivo para administradores</p>
            </div>

            <!-- Tarjeta -->
            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-8 shadow-2xl backdrop-blur-xl">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Correo -->
                    <div class="space-y-2">
                        <label for="email" class="ml-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Correo</label>
                        <div class="relative">
                            <Mail class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500" />
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full rounded-2xl border border-white/10 bg-black/40 py-3.5 pl-12 pr-4 text-white placeholder-gray-600 transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                placeholder="admin@campusmarket.test"
                            />
                        </div>
                        <InputError class="mt-1 text-xs text-red-400" :message="form.errors.email" />
                    </div>

                    <!-- Contraseña -->
                    <div class="space-y-2">
                        <label for="password" class="ml-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Contraseña</label>
                        <div class="relative">
                            <Lock class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500" />
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="w-full rounded-2xl border border-white/10 bg-black/40 py-3.5 pl-12 pr-12 text-white placeholder-gray-600 transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                placeholder="••••••••"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 transition hover:text-gray-300">
                                <Eye v-if="!showPassword" class="h-5 w-5" />
                                <EyeOff v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <InputError class="mt-1 text-xs text-red-400" :message="form.errors.password" />
                    </div>

                    <!-- Recordarme -->
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="checkbox" v-model="form.remember" class="h-4 w-4 rounded border-white/20 bg-black/40 text-indigo-500 focus:ring-indigo-500" />
                        <span class="text-sm text-gray-400">Mantener sesión iniciada</span>
                    </label>

                    <!-- Botón -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex h-14 w-full items-center justify-center rounded-2xl bg-indigo-600 font-bold text-white shadow-[0_0_25px_rgba(79,70,229,0.35)] transition hover:bg-indigo-500 hover:shadow-[0_0_40px_rgba(79,70,229,0.5)] disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="!form.processing">Entrar al panel</span>
                        <span v-else>Verificando…</span>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-gray-600">
                ¿No eres administrador?
                <a href="/login" class="text-gray-400 underline transition hover:text-gray-300">Ir al acceso de usuarios</a>
            </p>
        </div>
    </div>
</template>

<style scoped>
</style>
