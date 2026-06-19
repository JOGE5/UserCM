<?php

namespace App\Services;

use App\Mail\AdminNotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NLPModerationService
{
    /**
     * Diccionario heurístico de palabras críticas adicionales (delitos).
     * Clasificadas por pesos. Mayor peso = mayor urgencia.
     */
    private const CRITICAL_CRIMES = [
        // Nivel 3 (Crítico / Ilegal / Peligroso)
        'estafa' => 3, 'robo' => 3, 'fraude' => 3, 'ladrón' => 3, 'ladron' => 3,
        'arma' => 3, 'droga' => 3, 'acoso' => 3, 'amenaza' => 3, 'falso' => 3,
        
        // Nivel 1 (Reportes comunes)
        'roto' => 1, 'caro' => 1, 'equivocado' => 1, 'no funciona' => 1,
    ];

    /**
     * Umbral para disparar alerta a administradores.
     */
    private const CRITICAL_THRESHOLD = 3;

    /**
     * Analiza un texto (ej. descripción de un reporte) usando NLP básico (Bag of Words / Heurística)
     * y retorna el score de gravedad.
     */
    public function analyzeText(string $text): int
    {
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text)); // Limpiar texto
        $words = explode(' ', $text);
        
        $score = 0;
        
        // 1. Evaluar delitos graves y quejas comunes
        foreach ($words as $word) {
            if (isset(self::CRITICAL_CRIMES[$word])) {
                $score += self::CRITICAL_CRIMES[$word];
            }
        }
        
        // 2. Evaluar profanidades del archivo de configuración (config/profanities.php)
        // Damos un peso de 2 a cada grosería o insulto.
        $profanities = config('profanities.words', []);
        foreach ($profanities as $badWord) {
            // El config contiene frases con espacios, debemos buscar si la frase está en el texto original
            // O iterar las palabras simples
            $badWordClean = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $badWord));
            if (in_array($badWordClean, $words)) {
                $score += 2; 
            }
        }

        return $score;
    }

    /**
     * Procesa un reporte recién creado.
     * Si el score NLP es alto, alerta inmediatamente a los administradores.
     */
    public function processReport($reportModel, string $reasonText)
    {
        $score = $this->analyzeText($reasonText);

        if ($score >= self::CRITICAL_THRESHOLD) {
            $this->alertAdmins($reportModel, $reasonText, $score);
            return true; // Se catalogó como crítico
        }

        return false; // Reporte normal
    }

    /**
     * Envía un email a los administradores (Roles 1 y 2).
     */
    private function alertAdmins($reportModel, string $reason, int $score)
    {
        try {
            // Buscar administradores (Role 1 y 2)
            $admins = User::whereHas('usuarioCampusMarket', function ($query) {
                $query->whereIn('Cod_Rol', [1, 2]);
            })->get();

            if ($admins->isEmpty()) {
                return;
            }

            $subject = "🚨 URGENTE: Reporte Crítico Detectado por IA (Score: $score)";
            $messageContent = "El sistema NLP de CampusMarket ha detectado un reporte de alta prioridad.\n\n"
                            . "Motivo del usuario: \"{$reason}\"\n"
                            . "Puntuación de NLP: {$score} / 3\n\n"
                            . "Por favor ingresa al panel de administración para tomar acción inmediata.";

            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(new AdminNotificationMail($subject, $messageContent));
            }
        } catch (\Throwable $e) {
            Log::error('Fallo al enviar alerta NLP a Admins: ' . $e->getMessage());
        }
    }
}
