<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Foro;
use App\Services\NLPModerationService;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function store(Request $request, Foro $foro)
    {
        $request->validate([
            'texto' => ['required','string','max:2000'],
        ]);

        // NLP Moderation
        $nlpService = new NLPModerationService();
        $score = $nlpService->analyzeText($request->input('texto'));

        if ($score >= 2) {
            // Contenido altamente tóxico o ilegal.
            // Crear reporte automático sobre el foro y el infractor.
            \App\Models\Report::create([
                'reportable_type' => get_class($foro),
                'reportable_id'   => $foro->ID_Foro,
                'reporter_id'     => auth()->id(),
                'reason'          => 'NLP Auto-Moderación: Intento de comentario tóxico o ilegal.',
                'status'          => 'pending',
                'metadata'        => ['texto_bloqueado' => $request->input('texto')]
            ]);

            // Fake success para no alertar al atacante
            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'id' => 999999, // ID falso
                    'foro_id' => $foro->ID_Foro,
                    'user_id' => auth()->id(),
                    'texto' => $request->input('texto'),
                    'created_at' => now(),
                    'usuario' => auth()->user()
                ]);
            }
            return back()->with('success', 'Comentario publicado.');
        }

        $comentario = Comentario::create([
            'foro_id' => $foro->ID_Foro,
            'user_id' => auth()->id(),
            'texto' => $request->input('texto'),
        ]);

        $comentario->load('usuario');

        broadcast(new \App\Events\ForumMessageSent($comentario))->toOthers();

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($comentario);
        }

        return back();
    }
}
