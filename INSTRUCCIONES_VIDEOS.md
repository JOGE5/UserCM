# 🎥 Guía Completa: Carrusel de Videos en Welcome

## ✅ Estado Actual
- ✅ Swiper.js instalado
- ✅ Componente Welcome.vue configurado
- ✅ Carpetas creadas (public/videos y public/images/posters)
- ✅ Assets compilados

## 📁 Estructura de Archivos

```
public/
├── videos/
│   ├── clip1.mp4  ← Coloca tu video 1 aquí
│   ├── clip2.mp4  ← Coloca tu video 2 aquí
│   ├── clip3.mp4  ← Coloca tu video 3 aquí
│   └── clip4.mp4  ← Coloca tu video 4 aquí
└── images/
    └── posters/
        ├── poster1.jpg  ← Imagen preview video 1
        ├── poster2.jpg  ← Imagen preview video 2
        ├── poster3.jpg  ← Imagen preview video 3
        └── poster4.jpg  ← Imagen preview video 4
```

## 🎬 Paso 1: Optimizar tus Videos

### Opción A: Usando FFmpeg (Recomendado)
```bash
# Instalar FFmpeg desde: https://ffmpeg.org/download.html

# Optimizar cada video:
ffmpeg -i tu_video_original.mov -c:v libx264 -crf 28 -preset slow -vf scale=1280:720 -c:a aac -b:a 96k clip1.mp4
```

### Opción B: Usando HandBrake (Interfaz Gráfica)
1. Descargar HandBrake: https://handbrake.fr/
2. Abrir tu video
3. Configurar:
   - Preset: "Fast 720p30"
   - Video Codec: H.264
   - Quality: RF 28
   - Audio: AAC, 96 kbps
4. Guardar como clip1.mp4

### Opción C: Online (Más fácil)
1. Ir a: https://www.freeconvert.com/video-compressor
2. Subir tu video
3. Configurar:
   - Resolución: 1280x720
   - Codec: H.264
   - Calidad: Media-Alta
4. Descargar como clip1.mp4

### Especificaciones Objetivo:
- **Formato**: MP4 (H.264)
- **Resolución**: 1280x720 (HD)
- **Duración**: 14-15 segundos
- **Tamaño**: 2-3 MB por video
- **FPS**: 30 fps
- **Audio**: AAC, 96 kbps

## 🖼️ Paso 2: Crear Imágenes Poster

### Opción A: Extraer frame con FFmpeg
```bash
# Extraer frame del segundo 2 de cada video
ffmpeg -i clip1.mp4 -ss 00:00:02 -vframes 1 -q:v 2 poster1.jpg
ffmpeg -i clip2.mp4 -ss 00:00:02 -vframes 1 -q:v 2 poster2.jpg
ffmpeg -i clip3.mp4 -ss 00:00:02 -vframes 1 -q:v 2 poster3.jpg
ffmpeg -i clip4.mp4 -ss 00:00:02 -vframes 1 -q:v 2 poster4.jpg
```

### Opción B: Screenshot con VLC
1. Abrir video en VLC
2. Pausar en el frame deseado
3. Video → Tomar captura de pantalla
4. Guardar como poster1.jpg

### Especificaciones:
- **Formato**: JPG
- **Resolución**: 1920x1080 px
- **Calidad**: 80-85%
- **Tamaño**: 100-300 KB

## 📝 Paso 3: Personalizar Contenido

Edita `resources/js/Pages/Welcome.vue` líneas 28-51:

```javascript
const videos = [
    {
        src: '/videos/clip1.mp4',
        poster: '/images/posters/poster1.jpg',
        title: 'Tu Título 1',           // ← Cambia esto
        description: 'Tu descripción 1'  // ← Cambia esto
    },
    {
        src: '/videos/clip2.mp4',
        poster: '/images/posters/poster2.jpg',
        title: 'Tu Título 2',
        description: 'Tu descripción 2'
    },
    // ... etc
];
```

## 🚀 Paso 4: Compilar y Ver

```bash
# 1. Compilar assets
npm run build

# 2. Iniciar servidor
php artisan serve

# 3. Abrir en navegador
http://127.0.0.1:8000
```

## 🎨 Características del Carrusel

- ✅ **Autoplay**: Cambia automáticamente cada 15 segundos
- ✅ **Loop infinito**: Vuelve al inicio automáticamente
- ✅ **Pausa al hover**: Se pausa cuando pasas el mouse
- ✅ **Indicadores**: Barras en la parte inferior para navegar manualmente
- ✅ **Transición fade**: Efecto suave entre videos
- ✅ **Responsive**: Se adapta a móviles y tablets
- ✅ **Optimizado**: Solo reproduce el video activo

## ⚙️ Configuraciones Avanzadas

### Cambiar duración del autoplay:
En `Welcome.vue`, línea 106:
```javascript
:autoplay="{ 
    delay: 15000,  // ← Cambia a milisegundos (15000 = 15 seg)
    disableOnInteraction: false,
    pauseOnMouseEnter: true 
}"
```

### Cambiar velocidad de transición:
Línea 109:
```javascript
:speed="1000"  // ← Milisegundos (1000 = 1 segundo)
```

### Deshabilitar loop:
Línea 108:
```javascript
:loop="false"  // ← Cambia a false
```

## 🐛 Solución de Problemas

### Los videos no se ven:
1. Verifica que los archivos estén en `public/videos/`
2. Verifica que los nombres coincidan exactamente (clip1.mp4, clip2.mp4, etc.)
3. Ejecuta `npm run build` de nuevo
4. Refresca el navegador con Ctrl+F5

### Los videos se ven pixelados:
- Aumenta la resolución a 1920x1080
- Reduce el CRF a 23 en FFmpeg
- Usa un bitrate más alto

### Los videos tardan en cargar:
- Reduce el tamaño de archivo (aumenta CRF a 30)
- Reduce la resolución a 1280x720
- Comprime más el audio (64 kbps)

### El carrusel no funciona:
1. Abre la consola del navegador (F12)
2. Busca errores en rojo
3. Ejecuta: `npm run build`
4. Limpia caché del navegador

## 📊 Checklist Final

- [ ] 4 videos optimizados en `public/videos/`
- [ ] 4 imágenes poster en `public/images/posters/`
- [ ] Títulos y descripciones personalizados
- [ ] `npm run build` ejecutado sin errores
- [ ] Servidor Laravel corriendo
- [ ] Página carga correctamente en http://127.0.0.1:8000
- [ ] Videos se reproducen automáticamente
- [ ] Transiciones funcionan suavemente
- [ ] Indicadores permiten navegación manual

## 💡 Tips Adicionales

1. **Nombres de archivo**: Usa nombres simples sin espacios ni caracteres especiales
2. **Formato consistente**: Todos los videos deben tener la misma resolución
3. **Audio**: Puedes remover el audio completamente para reducir tamaño
4. **Testing**: Prueba en diferentes navegadores (Chrome, Firefox, Safari)
5. **Mobile**: Verifica que se vea bien en móviles

## 🎯 Resultado Esperado

Al abrir http://127.0.0.1:8000 deberías ver:
- Pantalla completa con tu primer video reproduciéndose
- Overlay oscuro con título y descripción
- Header con logo y botones de Login/Register
- Indicadores en la parte inferior
- Cambio automático cada 15 segundos
- Scroll suave hacia el contenido de Laravel

¡Listo! Tu carrusel de videos profesional está configurado. 🎉
