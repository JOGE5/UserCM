<?php

namespace App\Http\Controllers;

use App\Models\Foro;
use App\Models\Categorias;
use App\Models\UsuarioCampusMarket;
use App\Jobs\ModerateContentJob;
use App\Rules\NoProfanity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ForoController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $foros = Foro::with('categoria', 'creador')->where('Estado_Foro', 1)->get();
        return Inertia::render('Foros/Index', ['foros' => $foros]);
    }

    public function create()
    {
        $categorias = Categorias::all();
        return Inertia::render('Foros/Create', ['categorias' => $categorias]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Titulo_Foro' => ['required','string','max:200', new NoProfanity()],
            'Descripcion_Foro' => ['required','string', new NoProfanity()],
            'Cod_Categoria' => 'required|exists:categorias_articulos,Cod_Categoria',
            'Imagen_Foro' => 'required|image|max:2048',
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

        $data['ID_Creador'] = $perfil->ID_Usuario;
        $data['Estado_Foro'] = 1;

        if ($request->hasFile('Imagen_Foro')) {
            $path = $request->file('Imagen_Foro')->store('foros', 'public');
            $data['Imagen_Foro'] = $path;
        }

        $foro = Foro::create($data + [
            'moderation_status' => 'pending'
        ]);

        // dispatch moderation job (async)
        ModerateContentJob::dispatch($foro->ID_Foro);

        return redirect()->route('productos')->with('success', 'Foro creado y en revisión.');
    }

    public function show(Foro $foro)
    {
        $foro->load('categoria', 'creador', 'comentarios.usuario');

        $isCreator = false;
        $user = auth()->user();
        if ($user) {
            // If the forum stores the usuarios_campus_markets.ID_Usuario, compare with user's profile id
            $perfil = $user->usuarioCampusMarket;
            if ($perfil && $foro->ID_Creador == $perfil->ID_Usuario) {
                $isCreator = true;
            }
            // Fallback: if foro.ID_Creador equals the users.id
            if (! $isCreator && $foro->ID_Creador == $user->getKey()) {
                $isCreator = true;
            }
        }

        return Inertia::render('Foros/Show', [
            'foro' => $foro,
            'currentUserId' => auth()->id(),
            'canEdit' => $isCreator,
        ]);
    }

    public function edit(Foro $foro)
    {
        $this->authorize('update', $foro);
        $categorias = Categorias::all();
        return Inertia::render('Foros/Edit', ['foro' => $foro, 'categorias' => $categorias]);
    }

    public function update(Request $request, Foro $foro)
    {
        $this->authorize('update', $foro);

        $data = $request->validate([
            'Titulo_Foro' => ['required','string','max:200', new NoProfanity()],
            'Descripcion_Foro' => ['required','string', new NoProfanity()],
            'Cod_Categoria' => 'required|exists:categorias_articulos,Cod_Categoria',
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

        $foro->update($data + [
            'moderation_status' => 'pending'
        ]);

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
