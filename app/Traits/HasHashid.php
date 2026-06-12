<?php

namespace App\Traits;

use Hashids\Hashids;

trait HasHashid
{
    /**
     * Get the Hashids instance.
     */
    protected function getHashidsInstance(): Hashids
    {
        // Usamos la clave de la aplicación como salt para asegurar que los hashes sean únicos
        $salt = config('app.key', 'campus-market-salt');
        return new Hashids($salt, 8); // 8 caracteres mínimo
    }

    /**
     * Get the hashid attribute for the model.
     *
     * @return string
     */
    public function getHashidAttribute(): string
    {
        return $this->getHashidsInstance()->encode($this->getKey());
    }

    /**
     * Scope a query to find by hashid.
     */
    public function scopeFindByHashid($query, $hashid)
    {
        $decoded = $this->getHashidsInstance()->decode($hashid);
        
        if (empty($decoded)) {
            return $query->where($this->getKeyName(), -1); // Falla de forma segura
        }

        return $query->where($this->getKeyName(), $decoded[0]);
    }

    /**
     * Sobrescribir el método para generar URLs automáticamente usando el hashid
     * al llamar a route('algo', $modelo)
     */
    public function getRouteKey()
    {
        return $this->hashid;
    }

    /**
     * Sobrescribir la resolución automática de rutas de Laravel.
     * Permitirá seguir pasando el ID numérico si la ruta no está ofuscada todavía,
     * pero priorizará decodificar si llega una cadena (HashId).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            // Soporte para IDs numéricos (por ej. en el panel de admin si no se ha actualizado)
            return $this->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
        }

        $decoded = $this->getHashidsInstance()->decode($value);
        if (empty($decoded)) {
            abort(404);
        }

        return $this->where($field ?? $this->getRouteKeyName(), $decoded[0])->firstOrFail();
    }
}
