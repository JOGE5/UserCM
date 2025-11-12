# 🎬 Guía Rápida: Convertir Videos .MOV a .MP4

## ⚠️ PROBLEMA DETECTADO
Tus videos están en formato `.mov` que no es compatible con todos los navegadores web. Necesitas convertirlos a `.mp4`.

---

## ✅ SOLUCIÓN 1: Convertidor Online (MÁS FÁCIL - 5 minutos)

### Paso 1: Ir al Convertidor
Abre en tu navegador: **https://www.freeconvert.com/mov-to-mp4**

### Paso 2: Subir Videos
1. Click en "Choose Files"
2. Selecciona tus 4 archivos .mov de `C:\laragon\www\Repo\UserCM\public\videos\`
3. Espera a que se suban

### Paso 3: Configurar Conversión
1. Click en el ícono de configuración (⚙️) junto a cada video
2. Configuración recomendada:
   - **Codec**: H.264
   - **Resolution**: 1280x720 (o mantener original si es menor)
   - **Quality**: High
   - **Audio Codec**: AAC

### Paso 4: Convertir
1. Click en "Convert to MP4"
2. Espera a que termine la conversión

### Paso 5: Descargar
1. Click en "Download All" o descarga uno por uno
2. Guarda los archivos

### Paso 6: Renombrar y Reemplazar
1. Renombra los archivos descargados a:
   - `clip1.mp4`
   - `clip2.mp4`
   - `clip3.mp4`
   - `clip4.mp4`

2. Reemplaza los archivos .mov en:
   ```
   C:\laragon\www\Repo\UserCM\public\videos\
   ```

3. Puedes eliminar los archivos .mov antiguos

---

## ✅ SOLUCIÓN 2: VLC Media Player (Si ya lo tienes instalado)

### Paso 1: Abrir VLC
Si no lo tienes, descarga de: https://www.videolan.org/vlc/

### Paso 2: Convertir cada video
1. Abre VLC
2. Click en **Media** → **Convert / Save** (o Ctrl+R)
3. Click en **Add** y selecciona `clip1.mov`
4. Click en **Convert / Save** (botón inferior)
5. En Profile, selecciona: **Video - H.264 + MP3 (MP4)**
6. Click en el botón **Browse** para elegir destino
7. Guarda como: `clip1.mp4` en `C:\laragon\www\Repo\UserCM\public\videos\`
8. Click en **Start**
9. Repite para clip2.mov, clip3.mov, clip4.mov

---

## ✅ SOLUCIÓN 3: HandBrake (Mejor calidad)

### Paso 1: Descargar HandBrake
https://handbrake.fr/downloads.php

### Paso 2: Instalar y Abrir

### Paso 3: Convertir cada video
1. Click en **Open Source** → Selecciona `clip1.mov`
2. En **Presets** (derecha), selecciona: **Fast 720p30**
3. En **Save As**, nombra: `clip1.mp4`
4. Guarda en: `C:\laragon\www\Repo\UserCM\public\videos\`
5. Click en **Start Encode** (botón verde arriba)
6. Repite para los otros 3 videos

---

## ✅ SOLUCIÓN 4: Cloudconvert (Alternativa Online)

1. Ve a: https://cloudconvert.com/mov-to-mp4
2. Sube tus videos .mov
3. Selecciona MP4 como formato de salida
4. Click en "Convert"
5. Descarga los archivos convertidos
6. Renombra y reemplaza en `public/videos/`

---

## 📋 Checklist Final

Después de convertir, verifica:

- [ ] Tienes 4 archivos .mp4 en `public/videos/`:
  - [ ] clip1.mp4
  - [ ] clip2.mp4
  - [ ] clip3.mp4
  - [ ] clip4.mp4

- [ ] Los archivos .mov antiguos fueron eliminados (opcional)

- [ ] Ejecuta: `npm run build`

- [ ] Refresca el navegador en http://127.0.0.1:8000

- [ ] Los videos se reproducen automáticamente ✅

---

## 🎯 Especificaciones Recomendadas para MP4

Para mejor rendimiento web:

- **Formato**: MP4 (H.264)
- **Resolución**: 1280x720 (HD) o 1920x1080 (Full HD)
- **Frame Rate**: 30 fps
- **Bitrate Video**: 2-4 Mbps
- **Audio**: AAC, 128 kbps
- **Tamaño**: 2-5 MB por video de 15 segundos

---

## ❓ Preguntas Frecuentes

**P: ¿Por qué .mov no funciona en el navegador?**
R: El formato .mov (QuickTime) no es compatible con todos los navegadores web. MP4 es el estándar universal.

**P: ¿Perderé calidad al convertir?**
R: Si usas la configuración correcta (H.264, calidad alta), la pérdida será mínima e imperceptible.

**P: ¿Cuánto tiempo toma la conversión?**
R: Depende del tamaño, pero generalmente 1-3 minutos por video de 15 segundos.

**P: ¿Puedo usar otro formato?**
R: MP4 es el más compatible. WebM también funciona pero MP4 es mejor para compatibilidad universal.

---

## 🚀 Después de Convertir

1. Asegúrate de tener los 4 archivos .mp4 en `public/videos/`
2. Ejecuta: `npm run build`
3. Refresca el navegador (Ctrl+F5)
4. ¡Los videos deberían reproducirse automáticamente!

Si sigues teniendo problemas después de convertir, avísame y revisaremos el código.
