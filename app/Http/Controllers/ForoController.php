<?php

namespace App\Http\Controllers;

use App\Models\Foro;
use App\Models\Categoria_foros;
use App\Models\UsuarioCampusMarket;
use App\Jobs\ModerateContentJob;
use App\Services\NLPModerationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ForoController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $foros = Foro::with('categoria', 'creador.user')
            ->withCount('comentarios')
            ->where('Estado_Foro', 1)
            ->latest()
            ->get();
        return Inertia::render('Foros/Index', ['foros' => $foros]);
    }

    public function create()
    {
        $categorias = Categoria_foros::all();
        $carreras = \App\Models\Carrera::all();
        return Inertia::render('Foros/Create', ['categorias' => $categorias, 'carreras' => $carreras]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Titulo_Foro' => ['required','string','max:50'],
            'Descripcion_Foro' => ['required','string','max:100'],
            'Cod_Categoria' => 'required|exists:categorias_foros,Cod_Categoria',
            'Imagen_Foro' => 'nullable|image|max:2048',
            'tipo_acceso' => 'required|in:abierto,exclusivo',
            'carrera_destino' => 'nullable|required_if:tipo_acceso,exclusivo|exists:carreras,Cod_Carrera',
        ]);

        // Asegurar que usamos el ID_Usuario de la tabla usuarios_campus_markets
        $user = auth()->user();
        $perfil = $user->usuarioCampusMarket;
        if (! $perfil) {
            // Crear registro mínimo para vincular el foro
            $perfil = UsuarioCampusMarket::create([
                'user_id' => $user->getKey(),
                'Estado' => 'Habilitado',
                'Cod_Rol' => 3,
            ]);
        }

        $data['ID_Creador'] = $perfil->id;

        // NLP Moderation Check
        $nlpService = new NLPModerationService();
        $scoreTitulo = $nlpService->analyzeText($data['Titulo_Foro']);
        $scoreDesc = $nlpService->analyzeText($data['Descripcion_Foro']);
        $totalScore = $scoreTitulo + $scoreDesc;

        // Cualquier palabra prohibida da score 2, así que >= 2 significa que hay groserías.
        if ($totalScore >= 2) {
            // Contenido tóxico detectado
            $data['Estado_Foro'] = 0; // Nace oculto
        } else {
            $data['Estado_Foro'] = 1;
        }

        // Gamificación: Otorgar XP por crear un foro
        $perfil->addExperiencia(5);

        if ($request->hasFile('Imagen_Foro')) {
            $path = $request->file('Imagen_Foro')->store('foros', 'public');
            $data['Imagen_Foro'] = $path;
        }

        $foro = Foro::create($data + [
            'moderation_status' => 'pending'
        ]);

        // dispatch moderation job (async)
        ModerateContentJob::dispatch($foro->ID_Foro);

        // Si el foro nació oculto por NLP, creamos el reporte crítico
        if ($totalScore >= 2) {
            \App\Models\Report::create([
                'reportable_type' => get_class($foro),
                'reportable_id'   => $foro->ID_Foro,
                'reporter_id'     => $user->getKey(),
                'reason'          => 'NLP Auto-Moderación: Contenido prohibido o groserías detectadas en la creación.',
                'status'          => 'pending'
            ]);
            // El usuario ve mensaje normal para no alertarlo
            return redirect()->route('productos', ['foro_id' => $foro->ID_Foro])
                ->with('success', 'Foro creado exitosamente. Está en proceso de publicación.');
        }

        return redirect()->route('productos', ['foro_id' => $foro->ID_Foro])
            ->with('success', 'Foro creado exitosamente.');
    }

    public function show(Request $request, Foro $foro)
    {
        $foro->load('categoria', 'creador.user', 'comentarios.usuario');

        $isCreator = false;
        $user = auth()->user();
        if ($user) {
            // If the forum stores the usuarios_campus_markets.ID_Usuario, compare with user's profile id
            $perfil = $user->usuarioCampusMarket;
            if ($perfil && $foro->ID_Creador == $perfil->id) {
                $isCreator = true;
            }
            // Fallback: if foro.ID_Creador equals the users.id
            if (! $isCreator && $foro->ID_Creador == $user->getKey()) {
                $isCreator = true;
            }
        }

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'foro' => $foro,
                'canEdit' => $isCreator,
            ]);
        }

        // Redirigir al index con el ID del foro para cargarlo en el split-pane
        return redirect()->route('productos', ['foro_id' => $foro->ID_Foro]);
    }

    public function edit(Foro $foro)
    {
        $this->authorize('update', $foro);
        $categorias = Categoria_foros::all();
        return Inertia::render('Foros/Edit', ['foro' => $foro, 'categorias' => $categorias]);
    }

    public function update(Request $request, Foro $foro)
    {
        $this->authorize('update', $foro);

        $data = $request->validate([
            'Titulo_Foro' => ['required','string','max:50'],
            'Descripcion_Foro' => ['required','string','max:100'],
            'Cod_Categoria' => 'required|exists:categorias_foros,Cod_Categoria',
            'Imagen_Foro' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('Imagen_Foro')) {
            // delete old
            if ($foro->Imagen_Foro) {
                Storage::disk('public')->delete($foro->Imagen_Foro);
            }
            $path = $request->file('Imagen_Foro')->store('foros', 'public');
            $data['Imagen_Foro'] = $path;
        }

        // NLP Moderation Check
        $nlpService = new NLPModerationService();
        $scoreTitulo = $nlpService->analyzeText($data['Titulo_Foro']);
        $scoreDesc = $nlpService->analyzeText($data['Descripcion_Foro']);
        $totalScore = $scoreTitulo + $scoreDesc;

        if ($totalScore >= 2) {
            $data['Estado_Foro'] = 0;
        }

        $foro->update($data + [
            'moderation_status' => 'pending'
        ]);

        if ($totalScore >= 2) {
            \App\Models\Report::create([
                'reportable_type' => get_class($foro),
                'reportable_id'   => $foro->ID_Foro,
                'reporter_id'     => auth()->id(),
                'reason'          => 'NLP Auto-Moderación: Contenido prohibido detectado al editar.',
                'status'          => 'pending'
            ]);
        }

        ModerateContentJob::dispatch($foro->ID_Foro);

        return redirect()->route('productos')->with('success', 'Foro actualizado y en revisión.');
    }

    public function destroy(Foro $foro)
    {
        $this->authorize('delete', $foro);
        if ($foro->Imagen_Foro) {
            Storage::disk('public')->delete($foro->Imagen_Foro);
        }
        $foro->delete();
        return redirect()->route('productos')->with('success', 'Foro eliminado.');
    }
}
