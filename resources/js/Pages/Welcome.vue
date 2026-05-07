<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/swiper-bundle.css';
import { ShoppingBag, BookOpen, Users, Shield, ChevronRight, Search, MapPin, UserPlus, Package, Zap } from 'lucide-vue-next';
import EmojiParticles from '@/Components/EmojiParticles.vue';
import { animate, onScroll, stagger, createLayout } from 'animejs';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollTrigger);

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});

const modules        = [Autoplay, EffectFade];
const videoRefs      = ref([]);
const currentSlide   = ref(0);
let   swiperInstance = null;
const showIntro      = ref(true);
const scrollY        = ref(0);
const isScrolledNav  = ref(false);
const ctaBox         = ref(null);
const hoveredSede    = ref(null);
const manifestoRef   = ref(null);
const layoutRef      = ref(null);
let   layoutActive   = false;
let   layoutInstance = null;
let   layoutCounter  = 0;
let   manifestoActive = false;
let   manifestoIdx   = 0;

const manifestoTexts = [
    'Tu conocimiento tiene valor aquí.',
    'Intercambia libros sin complicaciones.',
    'Conecta con estudiantes de Bolivia.',
    'Descubre oportunidades en tu campus.',
    'Aprende de los mejores de tu facultad.',
    'Haz crecer tu red universitaria hoy.',
    'El intercambio empieza en el campus.',
];

const cycleManifesto = () => {
    if (!manifestoActive || !manifestoRef.value) return;
    const el = manifestoRef.value;
    animate(el, {
        opacity: [1, 0], translateY: [0, '-115%'],
        duration: 550, ease: 'in(3)',
        onComplete: () => {
            if (!manifestoActive) return;
            manifestoIdx = (manifestoIdx + 1) % manifestoTexts.length;
            el.textContent = manifestoTexts[manifestoIdx];
            animate(el, {
                opacity: [0, 1], translateY: ['115%', 0],
                duration: 800, ease: 'out(3)',
                onComplete: () => { if (manifestoActive) setTimeout(cycleManifesto, 2800); },
            });
        },
    });
};

const runLayout = () => {
    if (!layoutActive || !layoutInstance) return;
    layoutCounter = (layoutCounter % 4) + 1;
    layoutInstance.update(({ root }) => {
        root.dataset.grid = String(layoutCounter);
    }, {
        duration: 1000,
        ease: 'outElastic(1, 0.65)',
        delay: stagger(140),
        onComplete: () => setTimeout(runLayout, 1200),
    });
};

const stats = [
    { value: '4',     label: 'Ciudades activas' },
    { value: '100%',  label: 'Comunidad universitaria' },
    { value: 'Free',  label: 'Sin costo de registro' },
    { value: '∞',     label: 'Conexiones posibles' },
];

const steps = [
    {
        icon: UserPlus, title: 'Crea tu perfil',
        desc: 'Regístrate con tu correo institucional y verifica tu identidad como estudiante Unifranz. Solo te toma dos minutos.',
        color: 'text-indigo-400', bg: 'bg-indigo-400/10 border-indigo-400/20',
    },
    {
        icon: Package, title: 'Publica o explora',
        desc: 'Sube artículos académicos, libros o servicios estudiantiles. O navega el catálogo de lo que tus compañeros ofrecen.',
        color: 'text-emerald-400', bg: 'bg-emerald-400/10 border-emerald-400/20',
    },
    {
        icon: Zap, title: 'Conecta y negocia',
        desc: 'Chatea directamente con el vendedor, acuerda condiciones y completa el intercambio en campus de forma segura.',
        color: 'text-fuchsia-400', bg: 'bg-fuchsia-400/10 border-fuchsia-400/20',
    },
];

// Scroll storytelling
const featuresStory  = ref(null);
const currentFeature = ref(0);
const progressFill   = ref(null);

const featureItemStyle = (i) => {
    const active = currentFeature.value === i;
    const ty = active ? 0 : i > currentFeature.value ? 40 : -40;
    return {
        opacity: active ? 1 : 0,
        transform: `translateY(${ty}px)`,
        transition: 'opacity 0.45s ease, transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)',
    };
};

const featureTextStyle = (i) => {
    const active = currentFeature.value === i;
    const ty = active ? 0 : i > currentFeature.value ? 24 : -24;
    return {
        opacity: active ? 1 : 0,
        transform: `translateY(${ty}px)`,
        transition: 'opacity 0.45s ease 0.07s, transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.07s',
    };
};

const videos = [
    { src: '/videos/Clip1.mp4',  poster: '/images/posters/poster1.jpg', title: 'Innovación',           description: 'Un ecosistema creado para el entorno universitario moderno.' },
    { src: '/videos/Clip2.mp4',  poster: '/images/posters/poster2.jpg', title: 'Impulsando tu Carrera', description: 'Herramientas, plataformas y bibliografía para potenciar tus estudios.' },
    { src: '/videos/Clip3.mp4',  poster: '/images/posters/poster3.jpg', title: 'Intercambio Seguro',   description: 'Transacciones protegidas con reputación y validación entre estudiantes.' },
    { src: '/videos/Clip4.mp4',  title: 'Conexión Estudiantil',  description: 'Expande tu red y colabora con compañeros de diversas facultades.' },
    { src: '/videos/Clip5.mp4',  title: 'Material Accesible',    description: 'Apuntes, libros y herramientas académicas al alcance de tu bolsillo.' },
    { src: '/videos/Coder.mp4',  title: 'Impulso Tecnológico',   description: 'Desarrolla tus proyectos con el equipo adecuado.' },
    { src: '/videos/Waza21.mp4', title: 'Comunidad Dinámica',    description: 'Descubre oportunidades únicas cada día en tu campus.' },
];

const features = [
    { name: 'Mercado Vital',           description: 'Compra y vende artículos académicos con tu comunidad estudiantil. Publicaciones verificadas, intercambios rápidos sin fricciones.',   icon: ShoppingBag, color: 'text-emerald-400', bg: 'bg-emerald-400/10 border-emerald-400/20' },
    { name: 'Biblioteca Colaborativa', description: 'Accede a conocimiento real intercambiando libros, apuntes y material de estudio entre compañeros de toda la universidad.',           icon: BookOpen,    color: 'text-blue-400',    bg: 'bg-blue-400/10 border-blue-400/20'       },
    { name: 'Red Universitaria',       description: 'Forja alianzas, descubre eventos académicos y conecta con talentos de otras facultades y sedes en toda Bolivia.',                    icon: Users,       color: 'text-fuchsia-400', bg: 'bg-fuchsia-400/10 border-fuchsia-400/20' },
    { name: 'Entorno Verificado',      description: 'Protección e identidad verificada que garantiza transacciones justas dentro del ecosistema universitario.',                           icon: Shield,      color: 'text-amber-400',   bg: 'bg-amber-400/10 border-amber-400/20'     },
];

const sedes = [
    { name: 'Sede El Alto',    img: '/images/posters/SedeElAlto.jpg',     accent: 'text-indigo-300',  bg: 'bg-indigo-500/15',  border: 'border-indigo-500/25',  desc: 'Mayor comunidad del altiplano boliviano.' },
    { name: 'Sede La Paz',     img: '/images/posters/SedeLaPaz.jpg',      accent: 'text-fuchsia-300', bg: 'bg-fuchsia-500/15', border: 'border-fuchsia-500/25', desc: 'Centro tecnológico del conocimiento paceño.' },
    { name: 'Sede Cochabamba', img: '/images/posters/SedeCochabamba.png', accent: 'text-amber-300',   bg: 'bg-amber-500/15',   border: 'border-amber-500/25',   desc: 'El corazón estudiantil de Bolivia.' },
    { name: 'Sede Santa Cruz', img: '/images/posters/SedeSantaCruz.png',  accent: 'text-emerald-300', bg: 'bg-emerald-500/15', border: 'border-emerald-500/25', desc: 'Innovación en el oriente boliviano.' },
];

const dotStyle = (i) => ({
    height:          '2px',
    borderRadius:    '9999px',
    width:           currentFeature.value === i ? '2.5rem' : '1rem',
    backgroundColor: currentFeature.value === i ? '#818cf8' : 'rgba(255,255,255,0.15)',
    transition:      'width .45s ease, background-color .45s ease',
});


// ─── Scroll handler ───────────────────────────────────────────────────────────
const handleScroll = () => {
    scrollY.value       = window.scrollY;
    isScrolledNav.value = window.scrollY > 50;

    if (featuresStory.value) {
        const rect       = featuresStory.value.getBoundingClientRect();
        const scrolled   = -rect.top;
        const scrollable = featuresStory.value.offsetHeight - window.innerHeight;
        if (scrolled >= 0 && scrolled <= scrollable) {
            const next = Math.min(
                features.length - 1,
                Math.floor((scrolled / scrollable) * features.length),
            );
            if (next !== currentFeature.value) currentFeature.value = next;
        }
    }
};

const onSwiper        = (s) => { swiperInstance = s; playCurrentVideo(); };
const onSlideChange   = (s) => { currentSlide.value = s.realIndex; playCurrentVideo(); };
const playCurrentVideo = () => {
    const v = videoRefs.value[currentSlide.value];
    if (v) { v.currentTime = 0; v.play().catch(() => {}); }
};
const handleVideoEnd  = (index) => {
    if (index === currentSlide.value && swiperInstance) swiperInstance.slideNext();
};

onMounted(() => {
    videoRefs.value.forEach((v, i) => { if (v) v.addEventListener('ended', () => handleVideoEnd(i)); });
    setTimeout(() => { showIntro.value = false; }, 3000);
    setTimeout(() => { if (videoRefs.value[0]) videoRefs.value[0].play().catch(() => {}); }, 3300);
    window.addEventListener('scroll', handleScroll, { passive: true });

    // animejs onScroll + sync: .25 → barra de progreso sigue al scroll con lag suave
    if (featuresStory.value && progressFill.value) {
        animate(progressFill.value, {
            width: ['0%', '100%'],
            ease: 'linear',
            autoplay: onScroll({
                target: featuresStory.value,
                enter: 'top top',
                leave: 'bottom bottom',
                sync: .25,
            }),
        });
    }

    // Hero entry (GSAP, synced con intro)
    gsap.timeline({ defaults: { ease: 'power3.out' }, delay: 3.4 })
        .from('.hero-eyebrow', { y: 16, opacity: 0, duration: 0.5 })
        .from('.hero-title',   { y: 48, opacity: 0, duration: 0.8 }, '-=0.3')
        .from('.hero-desc',    { y: 24, opacity: 0, duration: 0.6 }, '-=0.4')
        .from('.hero-ctas',    { y: 16, opacity: 0, duration: 0.5 }, '-=0.3')
        .from('.hero-dots',    { opacity: 0, duration: 0.3 },        '-=0.2');

    // Phrase rotator — full sentences cycling with clip reveal
    if (manifestoRef.value) {
        manifestoActive = true;
        setTimeout(cycleManifesto, 2500);
    }

    // Scroll reveals — individual headings
    gsap.utils.toArray('.sr-heading').forEach(el => {
        gsap.fromTo(el,
            { y: 44, opacity: 0 },
            {
                y: 0, opacity: 1, duration: 0.85, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 89%', toggleActions: 'play none none none' },
            }
        );
    });

    // Scroll reveals — card groups (staggered batch)
    ScrollTrigger.batch('.sr-card', {
        start: 'top 88%',
        onEnter: batch => gsap.fromTo(batch,
            { y: 60, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.75, ease: 'power3.out', stagger: 0.11 }
        ),
    });

    // createLayout animated grid
    if (layoutRef.value) {
        layoutInstance = createLayout(layoutRef.value);
        layoutActive = true;
        setTimeout(runLayout, 1800);
    }

    // CTA (GSAP ScrollTrigger)
    if (ctaBox.value) {
        const h2   = ctaBox.value.querySelector('h2');
        const p    = ctaBox.value.querySelector('p');
        const btns = ctaBox.value.querySelector('.cta-btns');
        gsap.timeline({
            defaults: { ease: 'power3.out' },
            scrollTrigger: { trigger: ctaBox.value, start: 'top 85%', toggleActions: 'play none none reverse' },
        })
        .from(ctaBox.value, { y: 50, opacity: 0, duration: 0.8 })
        .from(h2,           { y: 24, opacity: 0, duration: 0.6 }, '-=0.45')
        .from(p,            { y: 16, opacity: 0, duration: 0.5 }, '-=0.35')
        .from(btns,         { y: 12, opacity: 0, duration: 0.4 }, '-=0.25');
    }
});

onUnmounted(() => {
    videoRefs.value.forEach((v, i) => { if (v) v.removeEventListener('ended', () => handleVideoEnd(i)); });
    window.removeEventListener('scroll', handleScroll);
    ScrollTrigger.getAll().forEach(t => t.kill());
    layoutActive   = false;
    manifestoActive = false;
});
</script>

<template>
    <Head title="Bienvenido | Campus Market" />

    <!-- Intro -->
    <Transition name="intro-fade">
        <div v-if="showIntro" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-zinc-950 overflow-hidden">
            <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.6)_0%,transparent_60%)] opacity-0 intro-light-overlay"></div>
            <div class="relative z-10 flex flex-col items-center">
                <div class="flex items-center justify-center gap-8 mb-10">
                    <div class="flex items-center justify-center w-36 h-36 rounded-2xl bg-white/5 border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] animate-pulse-logo overflow-hidden">
                        <img src="/images/posters/logo-team.png" alt="Logo" class="object-cover w-full h-full p-2" />
                    </div>
                    <div class="flex items-center justify-center w-36 h-36 rounded-2xl bg-white/5 border border-white/10 shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] animate-pulse-logo overflow-hidden" style="animation-delay:0.5s">
                        <img src="/images/posters/university-logo.png" alt="Unifranz" class="object-cover w-full h-full scale-110" />
                    </div>
                </div>
                <h1 class="mb-2 text-4xl font-black tracking-tighter text-white opacity-0 animate-slide-up" style="animation-fill-mode:forwards;animation-delay:0.2s">
                    Campus Market × Unifranz
                </h1>
                <p class="text-base font-light opacity-0 text-zinc-400 animate-slide-up" style="animation-fill-mode:forwards;animation-delay:0.5s">
                    Tu ecosistema universitario.
                </p>
            </div>
        </div>
    </Transition>

    <!-- Nav -->
    <nav :class="[
        'fixed top-0 inset-x-0 z-50 transition-all duration-500 border-b',
        isScrolledNav
            ? 'bg-zinc-950/80 backdrop-blur-xl border-white/[0.08] py-3'
            : 'bg-transparent border-transparent py-5'
    ]">
        <div class="flex items-center justify-between w-full px-4 lg:px-6">
            <div class="flex items-center gap-2.5">
                <div class="flex items-center justify-center w-9 h-9 overflow-hidden rounded-lg bg-white/10 shrink-0">
                    <img src="/images/posters/logo-team.png" alt="Logo" class="object-cover w-full h-full p-0.5" />
                </div>
                <span class="text-lg font-bold tracking-tight text-white">
                    Campus<span class="text-indigo-400">Market</span>
                    <span class="mx-1 text-zinc-500">×</span>
                    <span class="text-orange-300">Unifranz</span>
                </span>
            </div>
            <div v-if="canLogin" class="flex items-center gap-3">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                    class="px-5 py-2 text-sm font-medium text-white rounded-lg bg-white/10 border border-white/10 hover:bg-white/20 transition-colors active:scale-[0.98]">
                    Mi Panel
                </Link>
                <template v-else>
                    <Link :href="route('login')"
                        class="hidden px-4 py-2 text-sm font-medium transition-colors text-zinc-300 hover:text-white sm:block">
                        Ingresar
                    </Link>
                    <Link v-if="canRegister" :href="route('register')"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 text-sm font-semibold text-white rounded-lg bg-indigo-600 hover:bg-indigo-500 transition-all active:scale-[0.98] shadow-[inset_0_1px_0_rgba(255,255,255,0.15)]">
                        Crear Cuenta <ChevronRight class="w-4 h-4" />
                    </Link>
                </template>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <Transition name="smooth-reveal">
        <div v-show="!showIntro" class="relative h-[100dvh] overflow-hidden bg-zinc-950">
            <Swiper :modules="modules" :slides-per-view="1" :space-between="0"
                :loop="true" :effect="'fade'" :speed="2000" :allowTouchMove="false"
                @swiper="onSwiper" @slideChange="onSlideChange"
                class="absolute inset-0 w-full h-full">
                <SwiperSlide v-for="(video, index) in videos" :key="index">
                    <div class="relative w-full h-full">
                        <video :ref="el => videoRefs[index] = el"
                            class="absolute inset-0 object-cover w-full h-full"
                            :style="{ transform: `scale(${1 + scrollY * 0.0003})` }"
                            muted playsinline :poster="video.poster" preload="auto">
                            <source :src="video.src" type="video/mp4">
                        </video>
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/55 to-zinc-950/10"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-zinc-950/60 via-transparent to-transparent"></div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <div class="absolute bottom-0 left-0 z-20 max-w-2xl px-8 pb-28 lg:px-16 lg:pb-32">
                <div class="hero-eyebrow inline-flex items-center gap-2 px-3 py-1 mb-6 text-xs font-semibold tracking-widest text-zinc-300 uppercase rounded-md border border-white/10 bg-white/5 backdrop-blur-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
                    <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></span>
                    Campus Market — Unifranz
                </div>
                <h2 class="hero-title mb-4 text-5xl font-black tracking-tighter text-white leading-[0.95] md:text-7xl">
                    {{ videos[currentSlide].title }}
                </h2>
                <p class="max-w-md mb-8 text-lg font-light leading-relaxed hero-desc text-zinc-300">
                    {{ videos[currentSlide].description }}
                </p>
                <div class="flex flex-wrap gap-3 hero-ctas">
                    <a href="#explorar"
                        class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-zinc-950 bg-white rounded-lg hover:bg-zinc-100 transition-all active:scale-[0.98]">
                        <Search class="w-4 h-4" /> Explorar
                    </a>
                    <Link v-if="!$page.props.auth.user" :href="route('register')"
                        class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white rounded-lg border border-white/20 bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all active:scale-[0.98] shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]">
                        Crear cuenta <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>
            </div>

            <div class="absolute z-20 flex flex-col items-end gap-2 hero-dots bottom-10 right-8 lg:right-16">
                <span class="font-mono text-xs text-zinc-500">
                    {{ String(currentSlide + 1).padStart(2,'0') }} / {{ String(videos.length).padStart(2,'0') }}
                </span>
                <div class="flex gap-2">
                    <button v-for="(_, idx) in videos" :key="idx"
                        @click="swiperInstance?.slideTo(idx)"
                        :class="['h-0.5 rounded-full transition-all duration-500',
                            currentSlide === idx ? 'w-10 bg-indigo-400' : 'w-4 bg-white/20 hover:bg-white/40']">
                    </button>
                </div>
            </div>
        </div>
    </Transition>

    <div class="relative z-10 bg-zinc-950">

        <!-- ════════════════════════════════════════════════════
             FEATURES — SCROLL STORYTELLING
             500vh outer → scroll distance.
             Inner sticky panel.
             currentFeature actualizado por scroll event.
             animejs dispara transición en cada cambio.
             onScroll(sync:.25) → barra de progreso suave.
        ════════════════════════════════════════════════════ -->
        <section id="explorar" ref="featuresStory" style="height: 500vh">
            <div class="sticky top-0 h-[100dvh] bg-zinc-950 flex flex-col justify-center overflow-hidden">

                <!-- Grid texture -->
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTYwIDBIMFY2MEg2MHoiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjAzKSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9zdmc+')] pointer-events-none"></div>

                <!-- Label -->
                <p class="absolute top-24 left-6 lg:left-16 z-10 text-xs font-semibold tracking-widest text-indigo-400 uppercase">
                    Plataforma completa
                </p>

                <!-- Two-column grid -->
                <div class="relative z-10 w-full max-w-7xl mx-auto px-6 lg:px-16 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-20 items-center">

                    <!-- LEFT: icon + watermark numérico -->
                    <div class="relative flex items-center justify-center h-56 lg:h-[420px]">
                        <div v-for="(f, i) in features" :key="'vis-' + i"
                             :style="featureItemStyle(i)"
                             class="absolute inset-0 flex items-center justify-center">
                            <span class="absolute inset-0 flex items-center justify-center font-black text-white/[0.04] leading-none select-none pointer-events-none"
                                  style="font-size: clamp(8rem, 20vw, 18rem)">
                                {{ String(i + 1).padStart(2, '0') }}
                            </span>
                            <div :class="['relative z-10 w-28 h-28 lg:w-44 lg:h-44 rounded-3xl border flex items-center justify-center shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]', f.bg]">
                                <component :is="f.icon" :class="['w-12 h-12 lg:w-20 lg:h-20', f.color]" />
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: texto -->
                    <div class="relative h-44 lg:h-64">
                        <div v-for="(f, i) in features" :key="'txt-' + i"
                             :style="featureTextStyle(i)"
                             class="absolute inset-0 flex flex-col justify-center">
                            <span class="font-mono text-xs tracking-[0.15em] text-zinc-600 mb-4">
                                {{ String(i + 1).padStart(2,'0') }} / {{ String(features.length).padStart(2,'0') }}
                            </span>
                            <h3 class="text-3xl lg:text-5xl xl:text-6xl font-black tracking-tighter text-white mb-5 leading-none">
                                {{ f.name }}
                            </h3>
                            <p class="text-zinc-400 text-base lg:text-lg leading-relaxed max-w-[44ch]">
                                {{ f.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Indicador inferior:
                     · dots bindeados a currentFeature (CSS transition)
                     · barra de progreso animada con animejs onScroll sync:.25 -->
                <div class="absolute bottom-10 left-6 lg:left-16 z-10 flex flex-col gap-3">
                    <div class="flex gap-2 items-center">
                        <div v-for="(_, i) in features" :key="'dot-' + i" :style="dotStyle(i)"></div>
                        <span class="font-mono text-xs text-zinc-700 ml-2">Scroll para descubrir</span>
                    </div>
                    <!-- Barra de progreso — animejs onScroll sync:.25 -->
                    <div class="w-48 h-[1px] bg-white/5 rounded-full overflow-hidden">
                        <div ref="progressFill" class="h-full bg-indigo-400/60 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════════════
             POR QUÉ + STATS + CYCLING WORDS
        ════════════════════════════════════════════════════ -->
        <section class="border-t border-white/5 py-24 lg:py-32 px-6 lg:px-16 bg-zinc-950">
            <div class="max-w-7xl mx-auto">

                <!-- Two-column: statement + description -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start mb-20">
                    <div>
                        <p class="sr-heading mb-5 text-xs font-semibold tracking-[0.2em] text-indigo-400 uppercase">Diseñado para vos</p>
                        <h2 class="sr-heading text-5xl lg:text-6xl xl:text-7xl font-black tracking-tighter text-white leading-[0.9]">
                            El mercado universitario de <em class="not-italic text-indigo-400">Bolivia.</em>
                        </h2>
                    </div>
                    <div class="flex flex-col justify-end gap-5 lg:pt-16 text-zinc-400">
                        <p class="sr-heading text-lg leading-relaxed">
                            Campus Market nació dentro de los pasillos de Unifranz para resolver un problema real: la dificultad de intercambiar material académico entre compañeros.
                        </p>
                        <p class="sr-heading text-base leading-relaxed">
                            Hoy somos el punto de encuentro de estudiantes de cuatro ciudades, conectando conocimiento y oportunidades en un solo lugar, sin costo y sin complicaciones.
                        </p>
                    </div>
                </div>

                <!-- Stats grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-20">
                    <div v-for="stat in stats" :key="stat.label"
                         class="sr-card group rounded-xl border border-white/[0.07] bg-white/[0.03] p-7 flex flex-col gap-1 hover:border-indigo-500/30 hover:bg-indigo-500/[0.04] transition-all duration-400 cursor-default">
                        <span class="text-4xl lg:text-5xl font-black tracking-tighter text-white group-hover:text-indigo-300 transition-colors duration-300">{{ stat.value }}</span>
                        <span class="text-sm text-zinc-500">{{ stat.label }}</span>
                    </div>
                </div>

                <!-- Phrase rotator — full sentences cycle with clip reveal -->
                <div class="pt-16 border-t border-white/5">
                    <p class="mb-7 text-xs font-semibold tracking-[0.2em] text-zinc-600 uppercase">Lo que creemos</p>
                    <div class="overflow-hidden">
                        <p ref="manifestoRef"
                           class="font-black tracking-tighter text-white leading-tight select-none"
                           style="font-size: clamp(2.2rem, 6.5vw, 7rem)">
                            Tu conocimiento tiene valor aquí.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════════════
             SEDES
        ════════════════════════════════════════════════════ -->
        <div class="border-t border-white/5 bg-zinc-950 py-32 px-6 lg:px-16">
            <div class="max-w-7xl mx-auto">
                <p class="sr-heading mb-3 text-xs font-semibold tracking-[0.2em] text-indigo-400 uppercase">Cobertura nacional</p>
                <h2 class="sr-heading text-4xl font-black leading-none tracking-tighter text-white mb-3 sm:text-5xl">Nuestra Presencia</h2>
                <p class="sr-heading text-lg text-zinc-400 mb-16">Entregas e intercambios cerca de ti en cuatro ciudades.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div v-for="sede in sedes" :key="sede.name"
                        class="sr-card group relative overflow-hidden rounded-2xl border border-white/10 aspect-[4/3] bg-zinc-900"
                        @mouseenter="hoveredSede = sede.name"
                        @mouseleave="hoveredSede = null">
                        <img :src="sede.img" :alt="sede.name"
                            class="absolute inset-0 object-cover w-full h-full transition-transform duration-700 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/40 to-transparent"></div>
                        <EmojiParticles :active="hoveredSede === sede.name" :maxParticles="10" />
                        <div class="absolute inset-x-0 bottom-0 z-[10] p-8">
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 mb-3 text-xs font-semibold border rounded-md backdrop-blur-sm shadow-[inset_0_1px_0_rgba(255,255,255,0.08)]', sede.accent, sede.bg, sede.border]">
                                <MapPin class="w-3.5 h-3.5" /> Disponible
                            </span>
                            <h3 class="text-2xl font-bold text-white">{{ sede.name }}</h3>
                            <p class="mt-1.5 text-sm text-zinc-400">{{ sede.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════
             CÓMO FUNCIONA
        ════════════════════════════════════════════════════ -->
        <section class="border-t border-white/5 py-24 lg:py-32 px-6 lg:px-16 bg-zinc-950">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-16">
                    <div>
                        <p class="sr-heading mb-3 text-xs font-semibold tracking-[0.2em] text-indigo-400 uppercase">Proceso simple</p>
                        <h2 class="sr-heading text-4xl sm:text-5xl font-black tracking-tighter text-white leading-none">
                            Tres pasos para empezar.
                        </h2>
                    </div>
                    <p class="sr-heading text-zinc-400 max-w-sm lg:text-right text-base">
                        Sin tutoriales complicados. Empieza a comprar, vender y conectar en minutos desde cualquier sede.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div v-for="(step, i) in steps" :key="step.title"
                         class="sr-card group relative rounded-2xl border border-white/[0.07] bg-white/[0.02] p-8 overflow-hidden hover:border-white/[0.14] hover:bg-white/[0.04] transition-all duration-300">
                        <span class="absolute -right-3 -top-3 text-[8rem] font-black text-white/[0.03] leading-none select-none pointer-events-none group-hover:text-white/[0.05] transition-all duration-500">
                            {{ i + 1 }}
                        </span>
                        <div :class="['w-12 h-12 rounded-xl border flex items-center justify-center mb-8 transition-transform duration-300 group-hover:scale-110', step.bg]">
                            <component :is="step.icon" :class="['w-6 h-6', step.color]" />
                        </div>
                        <span class="font-mono text-xs tracking-[0.15em] text-zinc-700 mb-3 block">
                            {{ String(i + 1).padStart(2, '0') }} / 03
                        </span>
                        <h3 class="text-xl font-bold text-white mb-3">{{ step.title }}</h3>
                        <p class="text-zinc-500 text-sm leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════════════
             LAYOUT ANIMADO — createLayout + stagger
        ════════════════════════════════════════════════════ -->
        <section class="border-t border-white/5 py-24 lg:py-32 px-6 lg:px-16 bg-zinc-950 overflow-hidden">
            <div class="max-w-7xl mx-auto">

                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-14">
                    <div>
                        <p class="sr-heading mb-3 text-xs font-semibold tracking-[0.2em] text-indigo-400 uppercase">Todo en un lugar</p>
                        <h2 class="sr-heading text-4xl sm:text-5xl font-black tracking-tighter text-white leading-none">
                            Categorías que se adaptan.
                        </h2>
                    </div>
                    <p class="sr-heading text-zinc-400 max-w-sm lg:text-right text-base leading-relaxed">
                        El mercado se reorganiza para mostrarte lo que necesitas. Siempre dinámico, siempre relevante.
                    </p>
                </div>

                <!-- Layout container — createLayout target, data-grid drives columns -->
                <div ref="layoutRef" class="cm-layout" data-grid="1">
                    <div v-for="f in features" :key="f.name" class="cm-item">
                        <div :class="['rounded-2xl border p-6 lg:p-8 flex flex-col gap-5 h-full shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] transition-all duration-300 hover:shadow-[inset_0_1px_0_rgba(255,255,255,0.12)]', f.bg]">
                            <div :class="['w-12 h-12 rounded-xl border flex items-center justify-center', f.bg]">
                                <component :is="f.icon" :class="['w-6 h-6', f.color]" />
                            </div>
                            <div>
                                <p class="font-bold text-white text-lg leading-snug mb-1">{{ f.name }}</p>
                                <p class="text-zinc-500 text-sm leading-relaxed">{{ f.description.slice(0, 80) }}…</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex items-center justify-center gap-3">
                    <div class="h-px flex-1 bg-white/5"></div>
                    <span class="text-xs text-zinc-700 font-mono tracking-widest uppercase">anime.js · createLayout · stagger</span>
                    <div class="h-px flex-1 bg-white/5"></div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <div class="relative py-24 overflow-hidden border-t sm:py-32 border-white/5">
            <div class="absolute inset-0 bg-zinc-950 -z-10"></div>
            <div class="absolute -z-10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] bg-indigo-900/20 rounded-full blur-[100px]"></div>
            <div class="px-6 mx-auto max-w-7xl lg:px-16">
                <div ref="ctaBox" class="max-w-3xl mx-auto text-center bg-zinc-900/50 backdrop-blur-xl border border-white/10 rounded-2xl p-12 lg:p-20 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
                    <h2 class="text-4xl font-black tracking-tighter text-white sm:text-5xl lg:text-6xl">
                        Es hora de conectar.
                    </h2>
                    <p class="mt-6 text-lg text-zinc-400">
                        Súmate al mercado estudiantil definitivo. Rápido, seguro y construido para Unifranz.
                    </p>
                    <div class="flex flex-col items-center justify-center gap-4 mt-10 cta-btns sm:flex-row">
                        <Link :href="route('register')"
                            class="w-full sm:w-auto px-8 py-3.5 text-base font-bold text-zinc-950 bg-white rounded-lg hover:bg-zinc-100 transition-all active:scale-[0.98]">
                            Crear Cuenta Gratis
                        </Link>
                        <Link :href="route('login')"
                            class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-8 py-3.5 text-base font-semibold text-white rounded-lg border border-white/15 bg-white/5 hover:bg-white/10 transition-all active:scale-[0.98] shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
                            Iniciar Sesión <ChevronRight class="w-5 h-5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-white/5 bg-zinc-950">
            <div class="px-6 py-12 mx-auto max-w-7xl md:flex md:items-center md:justify-between lg:px-16">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-8 h-8 overflow-hidden rounded-lg bg-white/10">
                        <img src="/images/posters/logo-team.png" alt="Logo" class="object-cover w-full h-full p-0.5" />
                    </div>
                    <span class="text-sm font-semibold tracking-tight text-zinc-300">CampusMarket</span>
                </div>
                <div class="flex flex-col items-center mt-6 md:items-end md:mt-0">
                    <p class="text-sm text-zinc-600">
                        &copy; {{ new Date().getFullYear() }} Proyectos Universitarios. Todos los derechos reservados.
                    </p>
                    <div class="flex items-center gap-3 mt-2 font-mono text-xs text-zinc-700">
                        <span>Laravel v{{ laravelVersion }}</span>
                        <span class="w-1 h-1 rounded-full bg-zinc-700"></span>
                        <span>PHP v{{ phpVersion }}</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap');

html { scroll-behavior: smooth; background-color: #09090b; }
body {
    font-family: 'Outfit', system-ui, sans-serif;
    -webkit-font-smoothing: antialiased;
    background-color: #09090b;
    color: #fff;
}

.intro-fade-leave-active { transition: opacity 1.5s ease-in-out, filter 1.5s ease-in-out; }
.intro-fade-leave-active .intro-light-overlay { transition: opacity 1.5s ease-in-out; opacity: 1; }
.intro-fade-leave-to { opacity: 0; filter: brightness(3) drop-shadow(0 0 80px white); }

.smooth-reveal-enter-active,
.smooth-reveal-leave-active { transition: opacity 1s ease-in-out, filter 1s ease-in-out; }
.smooth-reveal-enter-from,
.smooth-reveal-leave-to     { opacity: 0; filter: brightness(0); }
.smooth-reveal-enter-to,
.smooth-reveal-leave-from   { opacity: 1; filter: brightness(1); }

@keyframes pulse-logo {
    0%, 100% { transform: scale(1); opacity: 1; }
    50%       { transform: scale(1.04); opacity: 0.85; }
}
@keyframes slide-up {
    from { transform: translateY(28px); opacity: 0; }
    to   { transform: translateY(0); opacity: 1; }
}
.animate-pulse-logo { animation: pulse-logo 3s ease-in-out infinite; }
.animate-slide-up   { animation: slide-up 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

/* createLayout animated grid */
.cm-layout {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    position: relative;
}
.cm-layout .cm-item {
    box-sizing: border-box;
    flex-shrink: 0;
}
.cm-layout[data-grid="1"] .cm-item { width: 100%; }
.cm-layout[data-grid="2"] .cm-item { width: calc(50% - 0.5rem); }
.cm-layout[data-grid="3"] .cm-item { width: calc(33.333% - 0.667rem); }
.cm-layout[data-grid="4"] .cm-item { width: calc(25% - 0.75rem); }
</style>
