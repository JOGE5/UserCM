<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El :attribute debe ser un correo válido.',
    'unique' => 'El :attribute ya ha sido registrado.',
    'confirmed' => 'La confirmación de la :attribute no coincide.',
    
    'password' => [
        'letters' => 'La contraseña debe contener al menos una letra.',
        'mixed' => 'La contraseña debe tener al menos una mayúscula y una minúscula.',
        'numbers' => 'La contraseña debe contener al menos un número.',
        'symbols' => 'La contraseña debe contener al menos un símbolo (ej. @, #, $, etc).',
        'uncompromised' => 'Esta contraseña ha aparecido en filtraciones públicas. Por favor elige otra más segura.',
    ],

    'attributes' => [
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'name' => 'nombre',
        'terms' => 'términos',
    ],
];
