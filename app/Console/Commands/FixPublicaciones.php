<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class FixPublicaciones extends Command
{
    protected $signature = 'fix:publicaciones';
    protected $description = 'Agrega las columnas estado y deleted_at a publicaciones para arreglar el error del dashboard sin borrar la base de datos';

    public function handle()
    {
        $this->info('Verificando tabla publicaciones...');

        if (Schema::hasTable('publicaciones')) {
            Schema::table('publicaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('publicaciones', 'estado')) {
                    $table->string('estado')->default('activa')->after('Estado_Publicacion');
                    $this->info('Columna "estado" agregada con éxito.');
                } else {
                    $this->info('La columna "estado" ya existe.');
                }

                if (!Schema::hasColumn('publicaciones', 'deleted_at')) {
                    $table->softDeletes();
                    $this->info('Columna "deleted_at" agregada con éxito.');
                } else {
                    $this->info('La columna "deleted_at" ya existe.');
                }
            });

            // Y para evitar que artisan migrate siga intentando crear universidades
            $this->info('Marcando migraciones antiguas como completadas para evitar choques...');
            $migraciones = [
                '2025_10_05_000000_create_universidades_table',
                '2025_10_06_000000_create_carreras_table',
                '2025_10_15_000000_create_roles_table',
                '2025_11_12_171935_add_two_factor_columns_to_users_table',
                '2025_11_12_172013_create_personal_access_tokens_table',
                '2025_11_14_000000_create_chats_table',
                '2025_11_14_000000_create_usuarios_campus_markets_table',
                '2025_11_14_000001_create_chat_users_table',
                '2025_11_14_000002_create_messages_table',
                '2025_11_19_000000_create_publicaciones_table',
                '2025_11_24_230901_add_status_and_soft_delete_to_publicaciones_table'
            ];

            $batch = DB::table('migrations')->max('batch') ?? 0;
            $batch++;

            foreach ($migraciones as $mig) {
                DB::table('migrations')->updateOrInsert(
                    ['migration' => $mig],
                    ['batch' => $batch]
                );
            }
            $this->info('¡Listo! Ya puedes recargar el Dashboard.');
        } else {
            $this->error('La tabla publicaciones no existe.');
        }
    }
}
