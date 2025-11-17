<?php

return [
    // Puedes mapear por nombre de carrera (string) o por Cod_Carrera (int => Cod_Categoria)
    // Mapeo por nombre (como antes): nombre de carrera => Nombre_Categoria
    'Ingenieria de Sistemas' => 'Electrónica',
    'Ingeniería de Sistemas' => 'Electrónica',
    'Ingeniería (Sistemas)' => 'Electrónica',
    'Derecho' => 'Libros',
    'Administración' => 'Libros',

    // Mapeo por ID de carrera (Cod_Carrera) => Cod_Categoria (ID en categorias_articulos)
    // Esto permite asignar por el ID real de la tabla `carreras`.
    1 => 1, // Cod_Carrera 1 (Sistemas) => Cod_Categoria 1 (Electrónica)
    // Añade más mapeos por ID según tu DB de pruebas.
];
