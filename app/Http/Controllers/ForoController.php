<?php

namespace App\Http\Controllers;

use App\Models\Foro;
use App\Models\Categorias;
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
            'Titulo_Foro' => 'required|string|max:200',
            'Descripcion_Foro' => 'required|string',
            'Cod_Categoria' => 'required|exists:categorias_articulos,Cod_Categoria',
            'Imagen_Foro' => 'required|image|max:2048',
        ]);

        $data['ID_Creador'] = auth()->id();
        $data['Estado_Foro'] = 1;

        if ($request->hasFile('Imagen_Foro')) {
            $path = $request->file('Imagen_Foro')->store('foros', 'public');
            $data['Imagen_Foro'] = $path;
        }

        Foro::create($data);

        return redirect()->route('productos')->with('success', 'Foro creado.');
    }

    public function show(Foro $foro)
    {
        $foro->load('categoria', 'creador', 'comentarios.usuario');
        return Inertia::render('Foros/Show', ['foro' => $foro]);
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
            'Titulo_Foro' => 'required|string|max:200',
            'Descripcion_Foro' => 'required|string',
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

        $foro->update($data);

        return redirect()->route('productos')->with('success', 'Foro actualizado.');
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
