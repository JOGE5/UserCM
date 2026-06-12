<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EncryptSensitiveFields extends Command
{
    protected $signature   = 'app:encrypt-sensitive-fields';
    protected $description = 'Encripta Telefono y descriptor_facial que aún no estén cifrados. Ejecutar UNA VEZ antes de activar los casts encrypted en los modelos.';

    public function handle(): int
    {
        $this->info('Encriptando campos sensibles...');

        $this->encryptTelefono();

        $this->info('Proceso completado.');
        return Command::SUCCESS;
    }

    private function isAlreadyEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException) {
            return false;
        }
    }

    private function encryptTelefono(): void
    {
        $rows = DB::table('usuarios_campus_markets')
            ->whereNotNull('Telefono')
            ->select('id', 'Telefono')
            ->get();

        $updated = 0;
        foreach ($rows as $row) {
            if ($this->isAlreadyEncrypted($row->Telefono)) {
                continue;
            }
            DB::table('usuarios_campus_markets')
                ->where('id', $row->id)
                ->update(['Telefono' => Crypt::encryptString($row->Telefono)]);
            $updated++;
        }

        $this->line("  Telefono: {$updated}/{$rows->count()} registros encriptados.");
    }
}
