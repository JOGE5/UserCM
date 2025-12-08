# 🔧 PASOS PARA DEBUGGEAR

## 1. Abre la Consola del Navegador
- **Firefox**: `F12` → pestaña "Consola"
- **Chrome**: `F12` → pestaña "Console"
- **Edge**: `F12` → pestaña "Console"

## 2. Ve a tu página de publicaciones
```
http://localhost/publicaciones
```

## 3. Mira si aparecen estos mensajes en la consola:
```
Cargando publicaciones...
Respuesta recibida: 200 OK
Datos recibidos: [...]
Publicaciones cargadas: X
```

## 4. Si ves error, copia el error aquí

### Posibles problemas y soluciones:

**❌ Error: 404 Not Found**
- La ruta `/api/publicaciones` no existe
- Solución: Ejecuta `php artisan route:list | grep publicaciones`

**❌ Error: 500 Internal Server Error**
- Hay un error en el controlador
- Solución: Mira los logs: `tail -f storage/logs/laravel.log`

**❌ CORS Error**
- El navegador no permite el acceso
- Solución: Verifica la configuración de CORS

**❌ Datos vacíos []**
- No hay publicaciones en la BD
- Solución: Crea una publicación con estado='activa'

---

## 5. Prueba directo el API sin Vue

Abre en navegador:
```
http://localhost/api/publicaciones
```

Deberías ver JSON como:
```json
[
  {
    "id": 10,
    "Titulo_Publicacion": "PRUEBA CLASE",
    "Precio_Publicacion": 123.00,
    "vendedor": {
      "user": {
        "id": 4,
        "name": "fer"
      }
    }
  }
]
```

---

## 6. Si todo funciona en el API pero no en Vue

El problema es el componente. Verifícalo:

1. Abre DevTools (F12)
2. Ve a la pestaña "Elements"
3. Busca las tarjetas: `<div class="card"`
4. Si no ves tarjetas, el componente no se renderiza

---

**Dime qué ves en la consola y dónde se queda (paso 1, 2, 3?)**

