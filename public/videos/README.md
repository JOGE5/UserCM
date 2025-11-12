# Carpeta de Videos

Coloca tus 4 videos aquí con los siguientes nombres:

- `clip1.mp4` - Video 1 (Innovación)
- `clip2.mp4` - Video 2 (Tecnología)
- `clip3.mp4` - Video 3 (Experiencia)
- `clip4.mp4` - Video 4 (Resultados)

## Recomendaciones de Optimización

### Usando FFmpeg (Recomendado):
```bash
ffmpeg -i input.mp4 -c:v libx264 -crf 28 -preset slow -vf scale=1280:720 -c:a aac -b:a 96k output.mp4
```

### Especificaciones:
- **Resolución**: 1280x720 (HD)
- **Duración**: 14-15 segundos cada uno
- **Formato**: MP4 (H.264)
- **Tamaño objetivo**: 2-3 MB por video
- **Bitrate de video**: ~3-5 Mbps
- **Bitrate de audio**: 96k

### Herramientas de Compresión:
- **HandBrake** (Gratuito, interfaz gráfica)
- **FFmpeg** (Línea de comandos, más control)
- **CloudConvert** (Online, fácil de usar)

## Imágenes Poster

También necesitas crear imágenes poster (preview) para cada video en:
`public/images/posters/`

- `poster1.jpg` - Imagen de preview para clip1
- `poster2.jpg` - Imagen de preview para clip2
- `poster3.jpg` - Imagen de preview para clip3
- `poster4.jpg` - Imagen de preview para clip4

**Tamaño recomendado**: 1920x1080 px, formato JPG, calidad 80%
