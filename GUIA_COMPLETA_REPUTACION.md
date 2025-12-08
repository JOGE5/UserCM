# 🎯 Sistema Completo: Reputación + Visualización de Publicaciones

## ✅ Lo que se implementó

### 1. **Sistema de Reputación con Cadenas de Márkov**
- ✅ Modelo `ReputacionEntreUsuarios` - Almacena calificaciones entre usuarios
- ✅ Modelo `ReputacionEstado` - Guarda el estado actual (Malo/Regular/Bueno/Excelente)
- ✅ Servicio `MarkovReputationService` - Lógica de transición de estados
- ✅ Controlador API `ReputacionController` - Endpoints REST
- ✅ Relaciones en modelo `User` - Acceso a calificaciones

### 2. **Componentes Vue 3**

#### **StarRating.vue** ⭐
- Selector interactivo de 1-5 estrellas
- Animaciones suaves con efecto brillo
- Envío automático a API
- Estados: hover, seleccionado, enviando

#### **PublicacionModal.vue** 📱
- Card compacta para lista
- Modal ampliado al hacer click
- Diseño responsivo (móvil/tablet/desktop)
- Integrado con `StarRating`
- Botones de acción: Contactar, Favorito, Reportar

#### **PublicacionesList.vue** 📋
- Grid de publicaciones
- Carga desde API `/api/publicaciones`
- Manejo de estado vacío

### 3. **Rutas API**

```
POST   /api/reputacion/{user}      → Calificar usuario
GET    /api/reputacion/{user}      → Obtener reputación
GET    /api/publicaciones          → Publicaciones ordenadas
```

### 4. **Base de Datos**

**Tabla: `reputacion_entre_usuarios`**
```sql
ID_Reputacion (PK)
ID_Usuario_Calificador (FK)
ID_Usuario_Calificado (FK)
Puntuacion (1-5)
Comentario (nullable)
created_at, updated_at
```

**Tabla: `reputacion_estado`**
```sql
id (PK)
user_id (FK, UNIQUE)
estado_actual (ENUM: Malo/Regular/Bueno/Excelente)
p_malo, p_regular, p_bueno, p_excelente (probabilidades)
created_at, updated_at
```

---

## 🚀 Cómo Usar

### 1. Ejecutar Migraciones
```bash
php artisan migrate
```

### 2. Usar en tu Página
```vue
<template>
  <!-- Mostrar publicaciones con rating -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <PublicacionModal
      v-for="pub in publicaciones"
      :key="pub.id"
      :publicacion="pub"
      @favorite="handleFav"
      @report="handleReport"
      @contact="handleContact"
    />
  </div>
</template>

<script setup>
import PublicacionModal from '@/Components/PublicacionModal.vue'
</script>
```

### 3. Estructura de Datos Esperada
```javascript
{
  id: 1,
  Titulo_Publicacion: "iPhone 12",
  Descripcion_Publicacion: "...",
  Precio_Publicacion: 450,
  Imagen_Publicacion: "[\"publicaciones/img1.jpg\"]", // JSON array
  categoria: { Nombre_Categoria: "Electrónica" },
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

---

## 📊 Estados de Reputación

| Estado | Promedio | Color | Ícono |
|--------|----------|-------|-------|
| **Malo** | < 1.5 | 🔴 Rojo | ⚠️ |
| **Regular** | 1.5-2.5 | 🟡 Naranja | ⭐ |
| **Bueno** | 2.5-3.5 | 🟢 Verde | ⭐⭐ |
| **Excelente** | ≥ 3.5 | 🔵 Azul | ⭐⭐⭐ |

---

## 🔄 Matriz de Transición de Márkov

```
           Malo  Regular  Bueno  Excelente
Malo     [ 0.40   0.40    0.15    0.05 ]
Regular  [ 0.10   0.50    0.30    0.10 ]
Bueno    [ 0.05   0.15    0.60    0.20 ]
Excelente[ 0.02   0.08    0.25    0.65 ]
```

**Interpretación:**
- De "Excelente": 65% probabilidad de mantenerse, 25% a "Bueno"
- De "Bueno": 60% probabilidad de mantenerse, 30% a "Regular"
- De "Regular": 50% probabilidad de mantenerse
- De "Malo": 40% probabilidad de mejorar a Regular

---

## 📱 Diseño Responsivo

**Tarjeta Compacta:**
- Móvil: 1 columna ancho completo
- Tablet: 2 columnas 50% ancho
- Desktop: 3 columnas 33% ancho

**Modal:**
- Móvil: Grid 1 columna, imagen pequeña
- Desktop: Grid 2 columnas, imagen grande 1:1

**Tamaños de imagen:**
- Card: 4:3 (ancho x alto)
- Modal: 1:1 cuadrada

---

## 🎨 Componentes Visuales

### StarRating
- 5 estrellas clickeables
- Hover: Escala 1.2x + brillo dorado
- Click: Se marca + animación pulse
- Efecto partículas al pasar mouse

### PublicacionModal
- Card: Imagen + Título + Precio + Vendedor
- Modal: Imagen grande + Info completa + Rating + Botones
- Animaciones de entrada/salida

### Badges de Reputación
- Inline o compacto según contexto
- 4 colores diferenciados
- Texto en mayúscula

---

## 📂 Archivos Creados/Modificados

### Migraciones
- `2025_12_08_000001_create_reputacion_entre_usuarios_table.php`
- `2025_12_08_000002_create_reputacion_estado_table.php`

### Modelos
- `app/Models/ReputacionEntreUsuarios.php` ✨ NEW
- `app/Models/ReputacionEstado.php` ✨ NEW
- `app/Models/User.php` (actualizado)

### Servicios
- `app/Services/MarkovReputationService.php` ✨ NEW

### Controladores
- `app/Http/Controllers/Api/ReputacionController.php` ✨ NEW
- `app/Http/Controllers/PublicacionesController.php` (actualizado)

### Componentes Vue
- `resources/js/Components/StarRating.vue` ✨ NEW
- `resources/js/Components/PublicacionModal.vue` ✨ NEW (completamente reescrito)
- `resources/js/Components/RatingComponent.vue` (original, se puede usar)

### Páginas Vue
- `resources/js/Pages/PublicacionesList.vue` ✨ NEW
- `resources/js/Pages/DashboardConPublicaciones.vue` ✨ NEW
- `resources/js/Pages/ReputationPage.vue` ✨ NEW

### Rutas
- `routes/api.php` (actualizado)

### Tests
- `tests/Feature/ReputacionTest.php` ✨ NEW

### Documentación
- `SISTEMA_REPUTACION.md` - Guía técnica
- `REPUTACION_GUIA_USO.md` - Ejemplos de uso
- `COMPONENTES_PUBLICACIONES.md` - Doc de componentes

---

## 🧪 Testing

```bash
# Ejecutar tests del sistema de reputación
php artisan test tests/Feature/ReputacionTest.php

# Test específico
php artisan test tests/Feature/ReputacionTest.php --filter test_crear_calificacion
```

---

## 🔐 Seguridad

- ✅ CSRF token en formularios
- ✅ Validación de puntuación (1-5)
- ✅ Autenticación en endpoints POST
- ✅ Autorización de usuario autenticado
- ✅ Sanitización de entrada

---

## 🐛 Troubleshooting

**Error: "Table not found"**
```bash
php artisan migrate
```

**Error: "Unauthorized"**
- Verificar token Bearer en headers
- Verificar X-CSRF-TOKEN en headers POST

**Componente no aparece**
```vue
import PublicacionModal from '@/Components/PublicacionModal.vue'
```

**API retorna null**
- Verificar que publicación tenga `vendedor.user.reputacionEstado`
- Ejecutar: `$user->reputacionEstado()->firstOrCreate(...)`

---

## 📝 Próximas Mejoras Sugeridas

1. [ ] Agregar paginación a `/api/publicaciones`
2. [ ] Filtrar por rango de precio
3. [ ] Filtrar por estado de reputación
4. [ ] Ordenar por fecha, precio, reputación
5. [ ] Mostrar número de calificaciones del vendedor
6. [ ] Sistema de comentarios en calificaciones
7. [ ] Historial de compras del usuario
8. [ ] Bloquear usuarios con reputación "Malo"
9. [ ] Enviar email de nueva calificación
10. [ ] Mostrar gráfica de evolución de reputación

---

## 📞 Soporte

Cualquier pregunta sobre:
- Sistema de Márkov → Ver `MarkovReputationService.php`
- Componentes Vue → Ver archivos en `resources/js/Components/`
- API → Ver `ReputacionController.php`
- BD → Ver migraciones en `database/migrations/`

