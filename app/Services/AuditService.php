<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Registra una acción en el audit log
     */
    public static function log(
        string $action,
        string $model,
        int $modelId = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Sistema',
                'action' => $action,
                'model' => $model,
                'model_id' => $modelId,
                'old_values' => !empty($oldValues) ? $oldValues : null,
                'new_values' => !empty($newValues) ? $newValues : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            /*\Log::error('Error al registrar audit log: ' . $e->getMessage());*/
        }
    }

    /**
     * Registra la creación de un modelo
     */
    public static function logCreated(string $model, int $modelId, array $values): void
    {
        self::log('created', $model, $modelId, [], $values);
    }

    /**
     * Registra la actualización de un modelo
     */
    public static function logUpdated(string $model, int $modelId, array $oldValues, array $newValues): void
    {
        self::log('updated', $model, $modelId, $oldValues, $newValues);
    }

    /**
     * Registra la eliminación de un modelo
     */
    public static function logDeleted(string $model, int $modelId, array $values): void
    {
        self::log('deleted', $model, $modelId, $values, []);
    }
}
