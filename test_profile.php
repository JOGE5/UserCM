<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \App\Models\UsuarioCampusMarket::create([
        'user_id' => 3, 
        'Cod_Carrera' => 1, 
        'Cod_Universidad' => 1, 
        'Cod_Rol' => 3
    ]); 
    echo "EXITO\n"; 
} catch (\Exception $e) { 
    echo "ERROR: " . $e->getMessage() . "\n"; 
}
