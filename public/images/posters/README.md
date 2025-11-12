# Carpeta de Imágenes Poster

Coloca las imágenes de preview de tus videos aquí:

- `poster1.jpg` - Preview para clip1.mp4
- `poster2.jpg` - Preview para clip2.mp4
- `poster3.jpg` - Preview para clip3.mp4
- `poster4.jpg` - Preview para clip4.mp4

## Especificaciones:
- **Formato**: JPG
- **Resolución**: 1920x1080 px (Full HD)
- **Calidad**: 80-85%
- **Tamaño**: 100-300 KB por imagen

## Cómo crear posters desde tus videos:

### Usando FFmpeg:
```bash
# Extraer frame del segundo 2 del video
ffmpeg -i clip1.mp4 -ss 00:00:02 -vframes 1 -q:v 2 poster1.jpg
```

### Usando VLC Media Player:
1. Abre el video en VLC
2. Pausa en el frame que quieras
3. Video → Tomar captura de pantalla
4. Guarda como poster1.jpg

### Online:
- Usa herramientas como **Canva** o **Photopea** para crear diseños personalizados
- O simplemente toma screenshots de tus videos

## Tip:
Las imágenes poster se muestran mientras el video carga, así que elige frames representativos y atractivos de cada video.
