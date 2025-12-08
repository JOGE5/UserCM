# Sistema de Reputación con Cadenas de Márkov

## Descripción General

Sistema completo de reputación de usuarios basado en cadenas de Márkov que permite:
- Calificar a otros usuarios (1-5 estrellas)
- Seguimiento automático del estado de reputación
- Ordenamiento de publicaciones por reputación del autor
- Probabilidades de transición entre estados

## Componentes Implementados

### 1. Migraciones
- `2025_12_08_000001_create_reputacion_entre_usuarios_table.php` - Tabla de calificaciones
- `2025_12_08_000002_create_reputacion_estado_table.php` - Tabla de estados y probabilidades

### 2. Modelos
- `ReputacionEntreUsuarios.php` - Relaciones entre calificador y calificado
- `ReputacionEstado.php` - Estado actual en cadena de Márkov
- `User.php` (actualizado) - Relaciones de reputación

### 3. Servicio
- `MarkovReputationService.php` - Lógica central:
  - Matriz de transición 4×4 fija
  - Cálculo de promedio de puntuaciones
  - Determinación de estado más probable
  - Actualización de probabilidades

### 4. Controladores
- `PublicacionesController.php` (actualizado) - Métodos `rateUser()` y `publicacionesOrdenadas()`
- `Api/ReputacionController.php` (nuevo) - API REST

### 5. Rutas API
```
POST   /api/reputacion/{user}      - Calificar usuario
GET    /api/reputacion/{user}      - Ver reputación
GET    /api/publicaciones          - Publicaciones por reputación
```

### 6. Componentes Vue 3
- `RatingComponent.vue` - Selector interactivo de 1-5 estrellas
- `ReputationBadge.vue` - Insignia visual del estado
- `ReputationPage.vue` - Página de ejemplo

## Flujo de Funcionamiento

1. **Usuario califica**: POST `/api/reputacion/{user}` con puntuación y comentario
2. **Sistema procesa**:
   - Crea registro en `reputacion_entre_usuarios`
   - Calcula promedio de puntuaciones del usuario
   - Determina estado según promedio (Malo/Regular/Bueno/Excelente)
   - Aplica matriz de transición
   - Actualiza `reputacion_estado`

3. **Estados de Reputación**:
   - **Malo**: Promedio < 1.5
   - **Regular**: 1.5 ≤ Promedio < 2.5
   - **Bueno**: 2.5 ≤ Promedio < 3.5
   - **Excelente**: Promedio ≥ 3.5

4. **Matriz de Transición** (Probabilidades):
   - Desde Malo: 40% Malo, 40% Regular, 15% Bueno, 5% Excelente
   - Desde Regular: 10% Malo, 50% Regular, 30% Bueno, 10% Excelente
   - Desde Bueno: 5% Malo, 15% Regular, 60% Bueno, 20% Excelente
   - Desde Excelente: 2% Malo, 8% Regular, 25% Bueno, 65% Excelente

## Ejemplo de Uso

### Crear Calificación
```javascript
fetch('/api/reputacion/2', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': token,
  },
  body: JSON.stringify({
    Puntuacion: 5,
    Comentario: 'Excelente vendedor, muy recomendado'
  })
})
```

### Obtener Reputación
```javascript
fetch('/api/reputacion/2').then(r => r.json())
// Retorna:
// {
//   user_id: 2,
//   nombre: "Juan",
//   reputacion: {
//     estado_actual: "Excelente",
//     p_malo: 0.02,
//     p_regular: 0.08,
//     p_bueno: 0.25,
//     p_excelente: 0.65
//   },
//   promedio_puntuacion: 4.8,
//   total_calificaciones: 12
// }
```

### Obtener Publicaciones Ordenadas
```javascript
fetch('/api/publicaciones').then(r => r.json())
// Retorna array ordenado por estado (Excelente > Bueno > Regular > Malo)
```

## Nombres de Columnas Exactos Usados

### Tabla reputacion_entre_usuarios
- `ID_Reputacion` (PK)
- `ID_Usuario_Calificador` (FK → users.id)
- `ID_Usuario_Calificado` (FK → users.id)
- `Puntuacion` (1-5)
- `Comentario` (nullable)

### Tabla reputacion_estado
- `id` (PK)
- `user_id` (FK → users.id, UNIQUE)
- `estado_actual` (ENUM)
- `p_malo`, `p_regular`, `p_bueno`, `p_excelente` (decimal 8,6)

## Instalación

1. Ejecutar migraciones:
```bash
php artisan migrate
```

2. Usar componentes Vue en tus páginas Inertia

3. Acceder a las rutas API con autenticación (excepto GET /api/publicaciones)

## Relaciones Modelo

```
User
├── reputacionesRecibidas() → ReputacionEntreUsuarios
├── reputacionesEmitidas() → ReputacionEntreUsuarios
└── reputacionEstado() → ReputacionEstado

ReputacionEntreUsuarios
├── usuarioCalificador() → User
└── usuarioCalificado() → User

ReputacionEstado
└── user() → User
```

