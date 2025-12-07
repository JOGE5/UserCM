<?php

namespace App\Jobs;

use App\Models\Foro;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ModerateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $foroId;

    public function __construct(int $foroId)
    {
        $this->foroId = $foroId;
    }

    public function handle()
    {
        $foro = Foro::find($this->foroId);
        if (! $foro) return;

        // prepare image url (public disk assumed)
        $imageUrl = null;
        if ($foro->Imagen_Foro) {
            try {
                // assume public disk is served at /storage
                $imageUrl = asset('storage/' . ltrim($foro->Imagen_Foro, '/'));
            } catch (\Throwable $e) {
                $imageUrl = null;
            }
        }

        $serviceUrl = config('services.moderation.url', env('MOD_SERVICE_URL'));
        $apiKey = config('services.moderation.key', env('MOD_SERVICE_KEY'));

        if (! $serviceUrl) {
            // No external service configured: do not auto-approve.
            // We'll try Google Vision SafeSearch as a fallback below if available.
            Log::info('ModerateContentJob: no external moderation service configured; will attempt Google Vision if available.');
            $foro->moderation_status = $foro->moderation_status ?? 'pending';
            $foro->moderation_checked_at = now();
            $foro->save();
            // continue to Google Vision section
        }

        try {
            $payload = [
                'id' => $foro->ID_Foro,
                'title' => $foro->Titulo_Foro,
                'text' => $foro->Descripcion_Foro,
                'image_url' => $imageUrl,
            ];

            $resp = Http::withHeaders([
                'Authorization' => $apiKey ? 'Bearer ' . $apiKey : null,
            ])->timeout((int)config('services.moderation.timeout', env('MOD_TIMEOUT', 10)))
              ->post(rtrim($serviceUrl, '/') . '/v1/moderate/content', $payload);

            if ($resp->successful()) {
                $data = $resp->json();
                $foro->moderation_scores = $data['scores'] ?? $data['raw'] ?? null;
                $foro->moderation_checked_at = now();
                $foro->moderation_reason = $data['reason'] ?? null;
                $foro->moderation_status = ($data['safe'] ?? ($data['rejected'] ? false : true)) ? 'approved' : 'rejected';
                $foro->save();
            } else {
                // service error: keep pending and maybe retry later
                $foro->moderation_status = 'pending';
                $foro->moderation_checked_at = now();
                $foro->save();
            }
        } catch (\Throwable $e) {
            // on error, leave as pending
            $foro->moderation_status = 'pending';
            $foro->moderation_checked_at = now();
            $foro->save();
        }

        // --- Google Vision SafeSearch ---
        if ($foro->Imagen_Foro) {
            $imageUrl = asset('storage/' . ltrim($foro->Imagen_Foro, '/'));

            try {
                if (! class_exists(ImageAnnotatorClient::class)) {
                    Log::warning('Google Vision ImageAnnotatorClient no está disponible en el autoload.');
                    $foro->moderation_status = 'pending';
                    $foro->moderation_reason = 'Google Vision client no disponible';
                    $foro->moderation_checked_at = now();
                    $foro->save();
                    return;
                }

                // Preferir variable de entorno, si existe
                $credPath = env('GOOGLE_APPLICATION_CREDENTIALS') ?: base_path('google-vision.json');
                $visionConfig = [];
                if ($credPath && file_exists($credPath)) {
                    $visionConfig['credentials'] = $credPath;
                    Log::info('Using Google Vision credentials at: ' . $credPath);
                } else {
                    Log::warning('Google Vision credentials not found at ' . $credPath . '. Trying default application credentials.');
                }

                $vision = new ImageAnnotatorClient($visionConfig);

                // Try to get image bytes. First try HTTP/asset, then local storage path
                $imageContent = false;
                try {
                    $imageContent = @file_get_contents($imageUrl);
                } catch (\Throwable $inner) {
                    $imageContent = false;
                }

                if ($imageContent === false || strlen($imageContent) === 0) {
                    // try local storage path (storage/app/public/...)
                    $local = storage_path('app/public/' . ltrim($foro->Imagen_Foro, '/'));
                    if (file_exists($local)) {
                        $imageContent = @file_get_contents($local);
                    }
                }

                if ($imageContent === false || strlen($imageContent) === 0) {
                    Log::warning('ModerateContentJob: no se pudo leer la imagen para ID ' . $foro->ID_Foro . ' (' . $imageUrl . ')');
                    $foro->moderation_status = 'pending';
                    $foro->moderation_reason = 'No se pudo acceder a la imagen para análisis';
                    $foro->moderation_checked_at = now();
                    $foro->save();
                    $vision->close();
                    return;
                }

                Log::info('ModerateContentJob: image bytes length=' . strlen($imageContent) . ' for foro ' . $foro->ID_Foro);

                $response = $vision->safeSearchDetection($imageContent);
                $safe = $response->getSafeSearchAnnotation();

                // Log raw safeSearch values for debugging
                Log::info('SafeSearch for foro ' . $foro->ID_Foro, [
                    'adult' => $safe->getAdult(),
                    'violence' => $safe->getViolence(),
                    'racy' => $safe->getRacy(),
                    'medical' => $safe->getMedical(),
                    'spoof' => $safe->getSpoof(),
                ]);

                $isUnsafe = (
                    $safe->getAdult() >= 3 ||
                    $safe->getViolence() >= 3 ||
                    $safe->getRacy() >= 3
                );

                if ($isUnsafe) {
                    $foro->moderation_status = 'rejected';
                    $foro->moderation_reason = 'Imagen detectada como inapropiada por Google Vision';
                } else {
                    $foro->moderation_status = 'approved';
                }
                $foro->moderation_checked_at = now();
                $foro->save();

                $vision->close();
                return;
            } catch (\Throwable $e) {
                // Si hay error, deja pendiente y registra el motivo
                Log::error('ModerateContentJob error for foro ' . $foro->ID_Foro . ': ' . $e->getMessage());
                $foro->moderation_status = 'pending';
                $foro->moderation_reason = 'Error al analizar imagen: ' . $e->getMessage();
                $foro->moderation_checked_at = now();
                $foro->save();
                return;
            }
        }
    }
}
