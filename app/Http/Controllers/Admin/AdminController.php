<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsuarioCampusMarket;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_usuarios'      => UsuarioCampusMarket::count(),
            'total_publicaciones' => DB::table('publicaciones')->count(),
            'total_foros'         => DB::table('foros')->count(),
            'total_reportes'      => DB::table('reportsPubli')->count(),
            'usuarios_por_rol'    => DB::table('usuarios_campus_markets')
                ->join('roles', 'usuarios_campus_markets.Cod_Rol', '=', 'roles.Cod_Rol')
                ->select('roles.Nombre_Rol', DB::raw('count(*) as total'))
                ->groupBy('roles.Nombre_Rol')
                ->get(),
            'universidades_top'   => DB::table('usuarios_campus_markets')
                ->join('universidades', 'usuarios_campus_markets.Cod_Universidad', '=', 'universidades.Cod_Universidad')
                ->select('universidades.Nombre_Universidad', DB::raw('count(*) as total'))
                ->groupBy('universidades.Nombre_Universidad')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
        ];

        return Inertia::render('Admin/Dashboard', compact('stats'));
    }
}
