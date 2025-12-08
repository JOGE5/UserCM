# Componentes de Publicaciones y Rating

## 📋 Componentes Creados

### 1. StarRating.vue
Componente interactivo para calificar vendedores con estrellas animadas.

**Props:**
- `userId` (Number, requerido) - ID del usuario a calificar

**Eventos:**
- `rating-submitted` - Se emite cuando se envía la calificación

**Uso:**
```vue
<template>
  <StarRating :user-id="2" @rating-submitted="onRatingSubmitted" />
</template>

<script setup>
const onRatingSubmitted = (data) => {
  console.log('Calificación enviada:', data)
}
</script>
```

**Características:**
- 5 estrellas interactivas con animaciones suaves
- Efecto de brillo y partículas al pasar el ratón
- Validación de usuario autenticado
- Envío automático a `/api/reputacion/{user}` al hacer click en estrella
- Muestra el número de estrellas seleccionadas

---

### 2. PublicacionModal.vue
Componente que muestra una tarjeta de publicación con modal de detalle ampliado.

**Props:**
- `publicacion` (Object, requerido) - Datos de la publicación

**Eventos:**
- `favorite` - Al marcar como favorito
- `report` - Al reportar la publicación
- `contact` - Al contactar al vendedor

**Estructura de Publicacion esperada:**
```javascript
{
  id: 1,
  Titulo_Publicacion: "iPhone 12",
  Descripcion_Publicacion: "Descripción...",
  Precio_Publicacion: 450,
  Imagen_Publicacion: "[\"publicaciones/img1.jpg\"]", // JSON array
  Cod_Categoria: 5,
  categoria: {
    Nombre_Categoria: "Electrónica"
  },
  vendedor: {
    user: {
      id: 2,
      name: "Juan",
      reputacionEstado: {
        estado_actual: "Excelente",
        p_malo: 0.02,
        p_regular: 0.08,
        p_bueno: 0.25,
        p_excelente: 0.65
      }
    }
  }
}
```

**Uso:**
```vue
<template>
  <PublicacionModal
    :publicacion="publicacion"
    @favorite="handleFavorite"
    @report="handleReport"
    @contact="handleContact"
  />
</template>

<script setup>
const handleFavorite = (pub) => console.log('Favorito:', pub)
const handleReport = (pub) => console.log('Reportar:', pub)
const handleContact = (pub) => console.log('Contactar:', pub)
</script>
```

**Características:**
- Card compacta con imagen, título, precio y vendedor
- Modal ampliado al hacer click
- Muestra reputación del vendedor con badge coloreado
- Incluye componente StarRating integrado
- Botones de acción: Contactar, Favorito, Reportar
- Responsive en móvil y escritorio
- Soporte para imágenes JSON array

---

### 3. PublicacionesList.vue
Página que lista todas las publicaciones usando PublicacionModal en una cuadrícula.

**Props:** Ninguno (carga datos desde API)

**Uso:**
```javascript
// En routes/web.php
Route::get('/publicaciones', function() {
  return Inertia::render('PublicacionesList');
});
```

**Características:**
- Grid responsivo: 1 col (móvil), 2 cols (tablet), 3 cols (desktop)
- Carga automática desde `/api/publicaciones`
- Manejo de estado vacío

---

## 🎨 Estilos de Reputación

Los badges de reputación usan estos colores:

```css
.badge-malo      /* Rojo: #dc2626 */
.badge-regular   /* Naranja: #d97706 */
.badge-bueno     /* Verde: #059669 */
.badge-excelente /* Azul: #0284c7 */
```

---

## 📱 Tamaños Responsivos

| Dispositivo | Columnas | Ancho Card |
|------------|----------|-----------|
| Móvil | 1 | 100% |
| Tablet | 2 | ~50% |
| Desktop | 3 | ~33% |
| Modal | Full | Max 1000px |

---

## 🔄 Flujo de Calificación

1. Usuario abre modal de publicación
2. Ve la sección "Calificar al vendedor"
3. Hace click en una estrella (1-5)
4. Se muestra el número de estrellas
5. Puede clickear "Enviar Calificación"
6. Se envía POST a `/api/reputacion/{userId}`
7. Sistema actualiza reputación con Márkov
8. Se muestra confirmación

---

## 🌐 API Endpoints Utilizados

### GET /api/publicaciones
Retorna todas las publicaciones activas ordenadas por reputación del autor.

**Respuesta:**
```json
[
  {
    "id": 1,
    "titulo": "iPhone 12",
    "descripcion": "...",
    "precio": 450,
    "imagen": "[\"publicaciones/img1.jpg\"]",
    "categoria": 5,
    "vendedor": {
      "id": 2,
      "nombre": "Juan",
      "reputacion": {
        "estado": "Excelente",
        "p_malo": 0.02,
        "p_regular": 0.08,
        "p_bueno": 0.25,
        "p_excelente": 0.65
      }
    },
    "estado_ordinal": 4
  }
]
```

### POST /api/reputacion/{user}
Envía una calificación de un usuario.

**Headers requeridos:**
- `Authorization: Bearer TOKEN` (si requiere autenticación)
- `X-CSRF-TOKEN: token`

**Body:**
```json
{
  "Puntuacion": 5,
  "Comentario": "Excelente vendedor"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Calificación registrada exitosamente",
  "data": {
    "id": 1,
    "user_id": 2,
    "estado_actual": "Excelente",
    "p_malo": 0.02,
    "p_regular": 0.08,
    "p_bueno": 0.25,
    "p_excelente": 0.65
  }
}
```

---

## 🎯 Próximos Pasos

1. Integrar en tu página principal de publicaciones
2. Implementar funcionalidad de favoritos
3. Implementar sistema de reportes
4. Agregar chat/contacto con vendedor
5. Mostrar historial de compras y calificaciones

