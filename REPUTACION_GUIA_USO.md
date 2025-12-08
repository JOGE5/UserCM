# Sistema de Reputación - Guía de Uso

## 🚀 Quick Start

### 1. Ejecutar Migraciones

Asegúrate de que la tabla `reputacion_entre_usuarios` esté creada. Si ya existe, la migración la saltará automáticamente.

```bash
php artisan migrate
```

### 2. Verificar Migraciones

Ahora deberías tener dos tablas:

```sql
-- Tabla de calificaciones
describe reputacion_entre_usuarios;

-- Tabla de estados
describe reputacion_estado;
```

## 📝 Estructura de Datos

### Tabla: reputacion_entre_usuarios
```
ID_Reputacion          (PK, bigint unsigned auto_increment)
ID_Usuario_Calificador (FK → users.id)
ID_Usuario_Calificado  (FK → users.id)
Puntuacion             (tinyint unsigned, rango 1-5)
Comentario             (text, nullable)
created_at             (timestamp)
updated_at             (timestamp)
```

### Tabla: reputacion_estado
```
id                     (PK)
user_id                (FK → users.id, UNIQUE)
estado_actual          (ENUM: Malo, Regular, Bueno, Excelente)
p_malo                 (decimal 8,6)
p_regular              (decimal 8,6)
p_bueno                (decimal 8,6)
p_excelente            (decimal 8,6)
created_at             (timestamp)
updated_at             (timestamp)
```

## 🔗 Rutas API

### POST /api/reputacion/{user}
Calificar a un usuario

**Requiere**: Autenticación (auth:sanctum)

**Parámetros**:
```json
{
  "Puntuacion": 5,
  "Comentario": "Excelente vendedor, muy recomendado"
}
```

**Ejemplo cURL**:
```bash
curl -X POST http://localhost/api/reputacion/2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "Puntuacion": 5,
    "Comentario": "Excelente"
  }'
```

**Respuesta**:
```json
{
  "success": true,
  "message": "Calificación registrada exitosamente",
  "data": {
    "id": 1,
    "user_id": 2,
    "estado_actual": "Excelente",
    "p_malo": 0.020000,
    "p_regular": 0.080000,
    "p_bueno": 0.250000,
    "p_excelente": 0.650000
  }
}
```

### GET /api/reputacion/{user}
Obtener reputación de un usuario

**Requiere**: Autenticación (auth:sanctum)

**Ejemplo cURL**:
```bash
curl -X GET http://localhost/api/reputacion/2 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Respuesta**:
```json
{
  "user_id": 2,
  "nombre": "Juan Pérez",
  "reputacion": {
    "id": 1,
    "user_id": 2,
    "estado_actual": "Excelente",
    "p_malo": 0.020000,
    "p_regular": 0.080000,
    "p_bueno": 0.250000,
    "p_excelente": 0.650000,
    "created_at": "2025-12-08T10:00:00.000000Z",
    "updated_at": "2025-12-08T10:00:00.000000Z"
  },
  "promedio_puntuacion": 4.8,
  "total_calificaciones": 12
}
```

### GET /api/publicaciones
Obtener publicaciones ordenadas por reputación del autor

**Requiere**: Sin autenticación (público)

**Ejemplo cURL**:
```bash
curl -X GET http://localhost/api/publicaciones
```

**Respuesta**:
```json
[
  {
    "id": 1,
    "titulo": "iPhone 12 en perfecto estado",
    "descripcion": "Vendo iPhone 12 con caja y accesorios originales...",
    "precio": 450,
    "imagen": "[\"publicaciones/img1.jpg\"]",
    "categoria": 5,
    "vendedor": {
      "id": 2,
      "nombre": "Juan Pérez",
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

## 🎯 Estados de Reputación

| Estado | Promedio | Probabilidades |
|--------|----------|----------------|
| **Malo** | < 1.5 | 40% Malo, 40% Regular, 15% Bueno, 5% Excelente |
| **Regular** | 1.5 - 2.5 | 10% Malo, 50% Regular, 30% Bueno, 10% Excelente |
| **Bueno** | 2.5 - 3.5 | 5% Malo, 15% Regular, 60% Bueno, 20% Excelente |
| **Excelente** | ≥ 3.5 | 2% Malo, 8% Regular, 25% Bueno, 65% Excelente |

## 🎨 Componentes Vue 3

### RatingComponent
Selector de calificación interactivo con 1-5 estrellas.

```vue
<template>
  <RatingComponent v-model="rating" />
</template>

<script setup>
import { ref } from 'vue'
import RatingComponent from '@/Components/RatingComponent.vue'

const rating = ref(0)
</script>
```

### ReputationBadge
Insignia visual del estado de reputación.

```vue
<template>
  <ReputationBadge estado-actual="Excelente" />
</template>

<script setup>
import ReputationBadge from '@/Components/ReputationBadge.vue'
</script>
```

### ReputationPage
Página de ejemplo con todo integrado.

```vue
<template>
  <ReputationPage />
</template>

<script setup>
import ReputationPage from '@/Pages/ReputationPage.vue'
</script>
```

## 🔧 Uso en Controladores

```php
<?php

use App\Services\MarkovReputationService;
use App\Models\ReputacionEntreUsuarios;
use App\Models\User;

// Crear calificación
ReputacionEntreUsuarios::create([
    'ID_Usuario_Calificador' => auth()->id(),
    'ID_Usuario_Calificado' => $user->id,
    'Puntuacion' => 5,
    'Comentario' => 'Muy buena atención',
]);

// Actualizar estado de reputación
$markovService = new MarkovReputationService();
$markovService->updateUserReputation($user);

// Obtener reputación actual
$reputacion = $markovService->getUserReputation($user);

// Obtener promedio de puntuaciones
$promedio = $markovService->getUserAverageScore($user);

// Obtener ordinal del estado para ordenamiento
$ordinal = $markovService->getStateOrdinal($reputacion->estado_actual);
// Retorna: 1 (Malo), 2 (Regular), 3 (Bueno), 4 (Excelente)
```

## 📊 Relaciones del Modelo

```php
// Usuario
$usuario->reputacionesRecibidas()    // Calificaciones que recibe
$usuario->reputacionesEmitidas()     // Calificaciones que emite
$usuario->reputacionEstado()         // Estado actual en Márkov

// ReputacionEntreUsuarios
$calificacion->usuarioCalificador()  // Usuario que califica
$calificacion->usuarioCalificado()   // Usuario calificado
```

## 🧪 Ejecutar Tests

```bash
php artisan test tests/Feature/ReputacionTest.php
```

## ⚠️ Notas Importantes

1. **Puntuaciones válidas**: Solo se aceptan valores entre 1 y 5
2. **Auto-creación de estados**: La tabla `reputacion_estado` se auto-completa al primera calificación
3. **Orden de publicaciones**: Automáticamente ordena por Excelente > Bueno > Regular > Malo
4. **Matriz fija**: La matriz de transición es constante y favorece mantener buenos estados

## 🐛 Troubleshooting

### Error: Table reputacion_entre_usuarios not found
```bash
php artisan migrate
```

### Error: Unauthorized para POST /api/reputacion/{user}
Asegúrate de incluir un token Bearer válido en el header Authorization:
```bash
Authorization: Bearer YOUR_API_TOKEN
```

### Las calificaciones no actualizan el estado
Verifica que el usuario tenga al menos una calificación en `reputacion_entre_usuarios`.

