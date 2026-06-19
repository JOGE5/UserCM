<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Mail\RetentionMail;
use Illuminate\Support\Facades\Mail;
use App\Services\RecommendationService;
use Carbon\Carbon;

class SendRetentionEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campusmarket:retention-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía emails de retención a usuarios inactivos con buena reputación';

    /**
     * Execute the console command.
     */
    public function handle(RecommendationService $recommendationService)
    {
        $this->info('Iniciando envío de correos de retención...');

        // Criterio heurístico:
        // 1. Inactivo por más de 15 días.
        // 2. Estado de reputación "Excelente" o "Bueno" (es un usuario valioso).
        $thresholdDate = Carbon::now()->subDays(15);

        $valuableInactiveUsers = User::whereHas('reputacionEstado', function($query) {
                $query->whereIn('estado_actual', ['Excelente', 'Bueno']);
            })
            // Podríamos usar last_login_at si existiera, pero podemos asumir que su última interacción 
            // (ej. creación de publicacion o reputacion) sirve como proxy
            // En un caso ideal, filtraríamos por last_login_at < $thresholdDate
            // Para fines prácticos, lo simularemos o verificaremos si no ha creado reportes/publicaciones recientemente
            ->get()
            ->filter(function($user) use ($thresholdDate) {
                // Heurística simple: Si no tiene chats o publicaciones en los últimos 15 días.
                $hasRecentActivity = \App\Models\Publicaciones::whereHas('vendedor', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('created_at', '>=', $thresholdDate)->exists();

                return !$hasRecentActivity;
            });

        $count = 0;
        foreach ($valuableInactiveUsers as $user) {
            // Conseguir productos recomendados con el servicio
            $recommendedProducts = $recommendationService->getRecommendationsForUser($user, 4);
            
            Mail::to($user->email)->queue(new RetentionMail($user, $recommendedProducts));
            $count++;
        }

        $this->info("Correos de retención encolados exitosamente a $count usuarios.");
    }
}
