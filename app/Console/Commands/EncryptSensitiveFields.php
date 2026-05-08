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
        $this->encryptDescriptorFacial();

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

    private function encryptDescriptorFacial(): void
    {
        $rows = DB::table('users')
            ->whereNotNull('descriptor_facial')
            ->select('id', 'descriptor_facial')
            ->get();

        $updated = 0;
        foreach ($rows as $row) {
            $raw = $row->descriptor_facial;
            if ($this->isAlreadyEncrypted($raw)) {
                continue;
            }
            // Garantiza que sea JSON string antes de encriptar
            $jsonStr = is_string($raw) ? $raw : json_encode($raw);
            DB::table('users')
                ->where('id', $row->id)
                ->update(['descriptor_facial' => Crypt::encryptString($jsonStr)]);
            $updated++;
        }

        $this->line("  descriptor_facial: {$updated}/{$rows->count()} registros encriptados.");
    }
}
