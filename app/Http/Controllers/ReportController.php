<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Publicaciones;
use App\Models\Foro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\PublicationReported;

class ReportController extends Controller
{
    /**
     * Store a report for a publication.
     */
    public function storePublication(Request $request, Publicaciones $publicacion)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
            'metadata' => 'nullable|array',
        ]);

        $report = Report::create([
            'reportable_type' => Publicaciones::class,
            'reportable_id' => $publicacion->id,
            'reporter_id' => auth()->id(),
            'reason' => $validated['reason'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        // Si el reporte es por imagen explícita, crear una notificación para admins
        $reason = strtolower($validated['reason'] ?? '');
        $ownerUserId = optional($publicacion->vendedor)->user_id ?? null;
        if (strpos($reason, 'imagen') !== false || strpos($reason, 'imagen explícita') !== false || ($validated['metadata']['image_flag'] ?? false)) {
            // Insertar en admin_notificaciones
            try {
                DB::table('admin_notificaciones')->insert([
                    'ID_Usuario' => 1, // admin user id por defecto
                    'Titulo_Notificacion' => "Reporte imagen: Publicación #{$publicacion->id}",
                    'Mensaje_Notificacion' => ($validated['reason'] ?? 'Reporte de imagen') . "\nPublicación: " . route('publicaciones.show', $publicacion->id),
                    'imgen' => null,
                    'Estado_Notificacion' => 'Pendiente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // no bloquear si la inserción falla
            }
        }

        // Emitir evento para el propietario (notificación en tiempo real)
        try {
            $message = 'Tu publicación ha sido reportada. Motivo: ' . ($validated['reason'] ?? 'Sin motivo especificado');
            if ($ownerUserId) event(new PublicationReported($publicacion->id, $ownerUserId, $message));
        } catch (\Throwable $e) {
            // ignore broadcast errors
        }

        return back()->with('success', 'Reporte enviado. Gracias por informarnos.');
    }

    /**
     * Store a report for a foro.
     */
    public function storeForo(Request $request, Foro $foro)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
            'metadata' => 'nullable|array',
        ]);

        $report = Report::create([
            'reportable_type' => Foro::class,
            'reportable_id' => $foro->ID_Foro ?? $foro->id,
            'reporter_id' => auth()->id(),
            'reason' => $validated['reason'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return back()->with('success', 'Reporte enviado. Gracias por informarnos.');
    }
}
