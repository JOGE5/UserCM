<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Foro;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function store(Request $request, Foro $foro)
    {
        $request->validate([
            'texto' => 'required|string|max:2000',
        ]);

        Comentario::create([
            'foro_id' => $foro->ID_Foro,
            'user_id' => auth()->id(),
            'texto' => $request->input('texto'),
        ]);

        return back();
    }
}
