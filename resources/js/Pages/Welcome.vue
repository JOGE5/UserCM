<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/swiper-bundle.css';
import { ShoppingBag, BookOpen, Users, Zap, Shield, ChevronRight, Search } from 'lucide-vue-next';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const modules = [Autoplay, EffectFade];
const videoRefs = ref([]);
const currentSlide = ref(0);
let swiperInstance = null;
const showIntro = ref(true);
const scrollY = ref(0);
const isScrolledNav = ref(false);

const handleScroll = () => {
    scrollY.value = window.scrollY;
    isScrolledNav.value = window.scrollY > 50;
};

const videos = [
    {
        src: '/videos/Clip1.mp4',
        poster: '/images/posters/poster1.jpg',
        title: 'Innovación',
        description: 'Encuentra nuevos métodos y herramientas para tus estudios.'
    },
    {
        src: '/videos/Clip2.mp4',
        poster: '/images/posters/poster2.jpg',
        title: 'Comunidad',
        description: 'Conecta con otros estudiantes y fomenta el crecimiento conjunto.'
    },
    {
        src: '/videos/Clip3.mp4',
        poster: '/images/posters/poster3.jpg',
        title: 'Estabilidad',
        description: 'Un entorno de transacciones de confianza y seguridad.'
    },
    {
        src: '/videos/Clip4.mp4',
        poster: '/images/posters/poster4.jpg',
        title: 'Social',
        description: 'Crea conexiones que importan dentro de tu universidad.'
    },
    {
        src: '/videos/Clip5.mp4',
        poster: '/images/posters/poster5.jpg',
        title: 'Experiencia',
        description: 'Calidad garantizada en cada intercambio.'
    }
];

const onSwiper = (swiper) => {
    swiperInstance = swiper;
    playCurrentVideo();
};

const onSlideChange = (swiper) => {
    currentSlide.value = swiper.realIndex;
    playCurrentVideo();
};

const playCurrentVideo = () => {
    const video = videoRefs.value[currentSlide.value];
    if (video) {
        video.currentTime = 0;
        video.play().catch(err => console.log('Autoplay prevented:', err));
    }
};

const handleVideoEnd = (index) => {
    if (index === currentSlide.value && swiperInstance) {
        swiperInstance.slideNext();
    }
};

onMounted(() => {
    videoRefs.value.forEach((video, index) => {
        if (video) {
            video.addEventListener('ended', () => handleVideoEnd(index));
        }
    });

    setTimeout(() => {
        showIntro.value = false;
    }, 3500);

    setTimeout(() => {
        if (videoRefs.value[0]) {
            videoRefs.value[0].play().catch(err => console.log('Autoplay prevented:', err));
        }
    }, 3800);

    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    videoRefs.value.forEach((video, index) => {
        if (video) {
            video.removeEventListener('ended', () => handleVideoEnd(index));
        }
    });
    window.removeEventListener('scroll', handleScroll);
});

const features = [
    { name: 'Mercado Estudiantil', description: 'Compra y vende libros, materiales y más con tus compañeros de campus.', icon: ShoppingBag, color: 'bg-blue-100 text-blue-600' },
    { name: 'Intercambio de Libros', description: 'Encuentra la bibliografía que necesitas a precios accesibles.', icon: BookOpen, color: 'bg-emerald-100 text-emerald-600' },
    { name: 'Red de Contactos', description: 'Conoce a estudiantes de otras facultades y amplía tu red.', icon: Users, color: 'bg-purple-100 text-purple-600' },
    { name: 'Rápido y Seguro', description: 'Transacciones ágiles y verificadas dentro del entorno universitario.', icon: Zap, color: 'bg-amber-100 text-amber-600' },
];
</script>

<template>
    <Head title="Bienvenido a Campus Market" />

    <!-- Splash Screen Intro -->
    <div v-if="showIntro" class="fixed inset-0 z-[100] flex flex-col items-center justify-center transition-all ease-in-out bg-indigo-950 duration-1000">
        <div class="flex items-center mb-8 space-x-8 animate-fade-in-stagger">
            <!-- Si no existen las imágenes, se mostrará el texto alternativo bonito -->
            <div class="flex items-center justify-center w-32 h-32 bg-white rounded-2xl shadow-2xl animate-fade-in-logo">
                <ShoppingBag class="w-16 h-16 text-indigo-600" />
            </div>
        </div>
        <h1 class="mb-2 text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-200 via-white to-purple-200 animate-pulse animate-fade-in-text delay-300">
            Campus Market
        </h1>
        <p class="text-lg text-indigo-200 animate-fade-in-text delay-500 font-medium">Conectando tu comunidad</p>
    </div>

    <!-- Navegación Flotante (Glassmorphism) -->
    <nav :class="[
        'fixed top-0 inset-x-0 z-50 transition-all duration-300',
        isScrolledNav ? 'bg-white/80 backdrop-blur-md shadow-sm py-4' : 'bg-transparent py-6'
    ]">
        <div class="container px-6 mx-auto lg:max-w-7xl relative flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-xl shadow-lg shadow-indigo-200">
                    <ShoppingBag class="w-5 h-5 text-white" />
                </div>
                <span :class="['text-xl font-bold tracking-tight transition-colors', isScrolledNav ? 'text-gray-900' : 'text-white']">
                    Campus Market
                </span>
            </div>

            <div v-if="canLogin" class="flex gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    :class="['px-5 py-2.5 text-sm font-semibold transition-all rounded-full', 
                            isScrolledNav ? 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100' : 'bg-white/20 text-white backdrop-blur-md hover:bg-white/30']"
                >
                    Mi Panel 
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        :class="['px-5 py-2.5 text-sm font-semibold transition-all rounded-full hidden sm:block', 
                                isScrolledNav ? 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' : 'text-white/90 hover:text-white hover:bg-white/10 backdrop-blur-sm']"
                    >
                        Iniciar Sesión
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-5 py-2.5 text-sm font-semibold text-white transition-all shadow-lg rounded-full shadow-indigo-500/30 bg-indigo-600 hover:bg-indigo-500 hover:shadow-indigo-500/50 hover:-translate-y-0.5"
                    >
                        Únete Ahora
                    </Link>
                </template>
            </div>
        </div>
    </nav>

    <!-- Sección Hero Principal -->
    <Transition name="smooth-reveal">
        <div v-show="!showIntro" class="relative h-screen min-h-[600px] overflow-hidden bg-gray-900">
            <Swiper
                :modules="modules"
                :slides-per-view="1"
                :space-between="0"
                :loop="true"
                :effect="'fade'"
                :speed="1500"
                :allowTouchMove="false"
                @swiper="onSwiper"
                @slideChange="onSlideChange"
                class="h-full"
            >
                <SwiperSlide v-for="(video, index) in videos" :key="index">
                    <div class="relative h-full">
                        <video
                            :ref="el => videoRefs[index] = el"
                            class="absolute inset-0 object-cover w-full h-full"
                            :style="{ transform: `scale(${1 + scrollY * 0.0003})` }"
                            muted
                            playsinline
                            :poster="video.poster"
                            preload="auto"
                        >
                            <source :src="video.src" type="video/mp4">
                        </video>

                        <!-- Capa superpuesta con degradado -->
                        <div class="absolute inset-0 bg-gradient-to-b from-gray-900/60 via-gray-900/40 to-gray-900/80"></div>

                        <div class="absolute inset-0 flex items-center justify-center p-6">
                            <div class="max-w-3xl text-center" :style="{ transform: `translateY(${scrollY * 0.2}px)` }">
                                <span class="inline-block px-4 py-1.5 mb-6 text-sm font-semibold text-indigo-100 tracking-wide uppercase bg-white/10 rounded-full backdrop-blur-md border border-white/20">
                                    Tu Marketplace Universitario
                                </span>
                                <h1 class="mb-6 text-5xl font-extrabold tracking-tight text-white md:text-7xl drop-shadow-lg">
                                    {{ video.title }}
                                </h1>
                                <p class="mb-10 text-xl font-medium md:text-2xl text-white/90 drop-shadow">
                                    {{ video.description }}
                                </p>
                                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                                    <a href="#explorar" class="w-full sm:w-auto px-8 py-3.5 text-base font-bold text-indigo-600 bg-white rounded-full shadow-xl hover:bg-gray-50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 group">
                                        <Search class="w-5 h-5 text-indigo-500 group-hover:text-indigo-600" />
                                        Explorar Mercado
                                    </a>
                                    <Link
                                        v-if="!$page.props.auth.user"
                                        :href="route('register')"
                                        class="w-full sm:w-auto px-8 py-3.5 text-base font-bold text-white transition-all duration-300 border-2 border-white/30 rounded-full bg-white/10 backdrop-blur-md hover:bg-white/20 hover:border-white/50"
                                    >
                                        Crear mi cuenta
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Indicadores de Slide -->
                        <div class="absolute inset-x-0 bottom-12 flex justify-center gap-3 z-20">
                            <button
                                v-for="(_, idx) in videos"
                                :key="idx"
                                @click="swiperInstance?.slideTo(idx)"
                                :class="[
                                    'h-1.5 rounded-full transition-all duration-300',
                                    currentSlide === idx ? 'w-10 bg-white' : 'w-4 bg-white/40 hover:bg-white/60'
                                ]"
                            ></button>
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <!-- Flecha de Scroll -->
            <div class="absolute z-30 -translate-x-1/2 bottom-6 left-1/2 animate-bounce">
                <a href="#explorar" class="p-2 text-white/70 hover:text-white transition-colors block">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </Transition>

    <!-- Contenido Principal -->
    <div id="explorar" class="bg-gray-50 text-gray-900 relative z-10">
        <!-- Sección de Características -->
        <div class="py-24 sm:py-32">
            <div class="px-6 mx-auto max-w-7xl lg:px-8">
                <div class="max-w-2xl mx-auto text-center">
                    <h2 class="text-base font-semibold leading-7 text-indigo-600 uppercase tracking-widest">¿Por qué Campus Market?</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Todo lo que necesitas, en tu facultad</p>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        Una plataforma diseñada exclusivamente para estudiantes. Encuentra materiales, vende lo que ya no usas y conecta con tu comunidad académica de forma segura.
                    </p>
                </div>

                <div class="max-w-2xl mx-auto mt-16 sm:mt-20 lg:mt-24 lg:max-w-none">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-4">
                        <div v-for="feature in features" :key="feature.name" class="flex flex-col items-center p-6 text-center transition-all bg-white shadow-xl rounded-3xl shadow-gray-200/50 hover:-translate-y-2 hover:shadow-2xl hover:shadow-indigo-100">
                            <dt class="flex flex-col items-center gap-y-4">
                                <div :class="['flex items-center justify-center w-16 h-16 rounded-2xl mb-2', feature.color]">
                                    <component :is="feature.icon" class="w-8 h-8" aria-hidden="true" />
                                </div>
                                <div class="text-xl font-bold text-gray-900">
                                    {{ feature.name }}
                                </div>
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600">
                                {{ feature.description }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Banner Call to Action -->
        <div class="relative overflow-hidden bg-indigo-600 isolate">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.indigo.400),theme(colors.indigo.600))] opacity-40"></div>
            <div class="px-6 py-24 mx-auto max-w-7xl sm:px-6 sm:py-32 lg:px-8">
                <div class="max-w-2xl mx-auto text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Comienza tu experiencia hoy.<br/>Es rápido y gratis.
                    </h2>
                    <p class="max-w-xl mx-auto mt-6 text-lg leading-8 text-indigo-100">
                        Únete a miles de estudiantes que ya están aprovechando Campus Market para comprar, vender y compartir recursos útiles.
                    </p>
                    <div class="flex items-center justify-center mt-10 gap-x-6">
                        <Link :href="route('register')" class="px-6 py-3.5 text-base font-semibold text-indigo-600 bg-white rounded-full shadow-sm hover:bg-gray-50 hover:scale-105 transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            Empezar ahora
                        </Link>
                        <Link :href="route('login')" class="text-base font-semibold leading-6 text-white group flex items-center gap-1">
                            Iniciar sesión <ChevronRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
                        </Link>
                    </div>
                </div>
            </div>
            <!-- Decoraciones de fondo -->
            <svg viewBox="0 0 1024 1024" class="absolute left-1/2 top-1/2 -z-10 h-[64rem] w-[64rem] -translate-x-1/2 [mask-image:radial-gradient(closest-side,white,transparent)]" aria-hidden="true">
                <circle cx="512" cy="512" r="512" fill="url(#gradient)" fill-opacity="0.7" />
                <defs>
                    <radialGradient id="gradient">
                        <stop stop-color="#818cf8" />
                        <stop offset="1" stop-color="#4f46e5" />
                    </radialGradient>
                </defs>
            </svg>
        </div>

        <!-- Footer estilizado -->
        <footer class="bg-white border-t border-gray-100">
            <div class="px-6 py-12 mx-auto max-w-7xl md:flex md:items-center md:justify-between lg:px-8">
                <div class="flex justify-center flex-1 space-x-6 md:justify-start">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 bg-indigo-100 rounded-lg">
                            <ShoppingBag class="w-4 h-4 text-indigo-600" />
                        </div>
                        <span class="text-lg font-bold text-gray-900">Campus Market</span>
                    </div>
                </div>
                <div class="mt-8 md:mt-0 flex flex-col items-center md:items-end">
                    <p class="text-sm font-medium leading-5 text-gray-500">
                        &copy; {{ new Date().getFullYear() }} Universidad. Todos los derechos reservados.
                    </p>
                    <div class="flex gap-4 mt-2 text-xs text-gray-400">
                        <span>Laravel v{{ laravelVersion }}</span>
                        <span class="w-1 h-1 mt-1.5 bg-gray-300 rounded-full"></span>
                        <span>PHP v{{ phpVersion }}</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
/* Estilos globales limpios y modernos */
html {
    scroll-behavior: smooth;
}

body {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Transición suave para revelar el contenido tras el splash screen */
.smooth-reveal-enter-active, .smooth-reveal-leave-active {
    transition: filter 1s ease-in-out, opacity 1s ease-in-out;
}
.smooth-reveal-enter-from, .smooth-reveal-leave-to {
    opacity: 0;
    filter: brightness(0.5);
}
.smooth-reveal-enter-to, .smooth-reveal-leave-from {
    opacity: 1;
    filter: brightness(1);
}

/* Animaciones personalizadas para el Intro */
@keyframes fade-in-logo {
    0% { opacity: 0; transform: scale(0.5) translateY(20px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}

@keyframes fade-in-text {
    0% { opacity: 0; transform: translateY(15px); }
    100% { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-logo {
    animation: fade-in-logo 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-fade-in-text {
    animation: fade-in-text 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.delay-300 { animation-delay: 0.3s; }
.delay-500 { animation-delay: 0.5s; }
</style>

