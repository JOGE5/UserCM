<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    active: {
        type: Boolean,
        default: false
    },
    maxParticles: {
        type: Number,
        default: 12
    }
});

const emojis = ['😄', '🎉', '✨', '😎', '📚', '🎓', '🧠', '✏️', '🌎', '🤝', '🎭', '🎶', '🇧🇴'];
const particles = ref([]);
let particleId = 0;
let animationFrameId = null;
let lastTime = 0;

const createParticle = () => {
    // Rango central seguro (10% al 85%) para evitar que salgan de los bordes bruscamente
    const x = Math.random() * 75 + 10;
    const y = Math.random() * 75 + 10;
    
    // Movimiento suave aleatorio
    const dx = (Math.random() - 0.5) * 0.4; 
    const dy = (Math.random() - 1.2) * 0.8; // tendiendo levemente hacia arriba
    
    const scale = Math.random() * 0.6 + 0.8; 
    const rotation = (Math.random() - 0.5) * 60; 
    
    return {
        id: particleId++,
        emoji: emojis[Math.floor(Math.random() * emojis.length)],
        x, y, dx, dy,
        rotation,
        life: 0,
        maxLife: Math.random() * 80 + 120, // duración temporal
        scale,
        opacity: 0,
    };
};

const animate = (timestamp) => {
    if (!lastTime) lastTime = timestamp;
    const delta = timestamp - lastTime;
    
    // Control de FPS a ~60 (por si los Hz de la pantalla son muy altos)
    if (delta > 16) {
        lastTime = timestamp;

        if (props.active) {
            // Spawn de animaciones
            if (particles.value.length < props.maxParticles && Math.random() < 0.1) {
                particles.value.push(createParticle());
            }

            // Actualizar 
            for (let i = particles.value.length - 1; i >= 0; i--) {
                const p = particles.value[i];
                p.life++;
                p.x += p.dx;
                p.y += p.dy;
                p.rotation += p.dx * 0.5;
                
                const fadeInPhase = 30;
                const fadeOutPhase = 30;

                if (p.life < fadeInPhase) {
                    p.opacity = p.life / fadeInPhase;
                } else if (p.life > p.maxLife - fadeOutPhase) {
                    p.opacity = (p.maxLife - p.life) / fadeOutPhase;
                } else {
                    p.opacity = 1;
                }
                
                if (p.life >= p.maxLife) {
                    particles.value.splice(i, 1);
                }
            }
        } else {
            // Si el hover se quita, forzar fade-out rápido sin matar de golpe
            for (let i = particles.value.length - 1; i >= 0; i--) {
                const p = particles.value[i];
                p.opacity -= 0.05;
                p.x += p.dx;
                p.y += p.dy;
                if (p.opacity <= 0) {
                    particles.value.splice(i, 1);
                }
            }
        }
    }
    
    animationFrameId = requestAnimationFrame(animate);
};

onMounted(() => {
    animationFrameId = requestAnimationFrame(animate);
});

onUnmounted(() => {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
});
</script>

<template>
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div
            v-for="p in particles"
            :key="p.id"
            class="absolute pointer-events-none drop-shadow-[0_0_20px_rgba(255,255,255,0.7)] blur-[0.4px]"
            :style="{
                left: `${p.x}%`,
                top: `${p.y}%`,
                opacity: p.opacity,
                transform: `scale(${p.scale}) rotate(${p.rotation}deg)`,
                fontSize: '3rem',
                willChange: 'transform, opacity, top, left'
            }"
        >
            {{ p.emoji }}
        </div>
    </div>
</template>
