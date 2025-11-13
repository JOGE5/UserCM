<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use Illuminate\Http\Request;

class CarreraController extends Controller
{
    public function index(Request $request)
    {
        $universidadId = $request->query('universidad_id');

        if ($universidadId) {
            $carreras = Carrera::where('Cod_Universidad', $universidadId)->get();
        } else {
            $carreras = Carrera::all();
        }

        return response()->json($carreras);
    }
}
