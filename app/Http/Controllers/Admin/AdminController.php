<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publicaciones;
use App\Models\Roles;
use App\Models\User;
use App\Models\UsuarioCampusMarket;
use Illuminate\Http\Request;
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

    public function users(Request $request)
    {
        $query = User::with(['usuarioCampusMarket.rol', 'usuarioCampusMarket.universidad'])
            ->orderBy('created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($rol = $request->get('rol')) {
            $query->whereHas('usuarioCampusMarket', fn ($q) => $q->where('Cod_Rol', $rol));
        }

        $usuarios = $query->paginate(20)->withQueryString();
        $roles    = Roles::all();

        return Inertia::render('Admin/Users', [
            'usuarios' => $usuarios,
            'roles'    => $roles,
            'filters'  => $request->only('search', 'rol'),
        ]);
    }

    public function updateRol(Request $request, User $user)
    {
        $request->validate(['Cod_Rol' => 'required|exists:roles,Cod_Rol']);

        $perfil = $user->usuarioCampusMarket;
        if (! $perfil) {
            return back()->with('error', 'Este usuario no tiene perfil extendido.');
        }
        $perfil->update(['Cod_Rol' => $request->Cod_Rol]);

        return back()->with('success', 'Rol actualizado correctamente.');
    }

    public function updateVerificado(Request $request, User $user)
    {
        $perfil = $user->usuarioCampusMarket;
        if (! $perfil) {
            return back()->with('error', 'Este usuario no tiene perfil extendido.');
        }
        $perfil->update(['verificado' => ! $perfil->verificado]);

        return back()->with('success', 'Estado de verificación actualizado.');
    }

    public function publications(Request $request)
    {
        $query = Publicaciones::with(['categoria', 'vendedor.user'])
            ->orderBy('created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where('Titulo_Publicacion', 'like', "%{$search}%");
        }

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        $publicaciones = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Publications', [
            'publicaciones' => $publicaciones,
            'filters'       => $request->only('search', 'estado'),
        ]);
    }

    public function updatePublicacion(Request $request, Publicaciones $publicacion)
    {
        $request->validate(['estado' => 'required|in:activa,oculta,borrador,vendida']);
        $publicacion->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado de publicación actualizado.');
    }

    public function reports(Request $request, \App\Services\QueueTheoryService $queueService)
    {
        $query = DB::table('reportsPubli')
            ->leftJoin('publicaciones', 'reportsPubli.reportable_id', '=', 'publicaciones.id')
            ->leftJoin('users as reporter', 'reportsPubli.reporter_id', '=', 'reporter.id')
            ->select(
                'reportsPubli.*',
                'publicaciones.Titulo_Publicacion',
                'publicaciones.estado as pub_estado',
                'reporter.name as reporter_name',
                'reporter.email as reporter_email'
            )
            ->where('reportsPubli.reportable_type', 'like', '%Publicaciones%')
            ->orderBy('reportsPubli.created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where('publicaciones.Titulo_Publicacion', 'like', "%{$search}%");
        }

        $reportes = $query->paginate(20)->withQueryString();

        // -------------------------------------------------------------
        // CÁLCULOS TEORÍA DE COLAS (M/M/c) PARA LA PRESENTACIÓN
        // -------------------------------------------------------------
        $c = max(1, (int) $request->get('c', 2)); // Número de moderadores (editable desde UI)
        
        $days = 30;
        $totalReports = DB::table('reportsPubli')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
            
        $urgentReports = DB::table('reportsPubli')
            ->where('created_at', '>=', now()->subDays($days))
            ->where(function($q) {
                $q->where('reason', 'like', '%ofensiv%')
                  ->orWhere('reason', 'like', '%acoso%')
                  ->orWhere('reason', 'like', '%insult%')
                  ->orWhere('reason', 'like', '%explícit%')
                  ->orWhere('reason', 'like', '%imagen%');
            })->count();

        $normalReports = $totalReports - $urgentReports;

        $realLambda1 = $days > 0 ? $urgentReports / $days : 0;
        $realLambda2 = $days > 0 ? $normalReports / $days : 0;
        
        // Si no hay suficientes datos en la DB, usamos los valores del Escenario Ejemplo (Lambda=20)
        if ($realLambda1 + $realLambda2 < 1) {
            $realLambda1 = 8;  // Reportes ofensivos/urgentes
            $realLambda2 = 12; // Reportes menores/bugs
        }
        
        $realMu = 15; // Tasa de servicio promedio por moderador (según Diapositiva 3)

        $queueMetrics = $queueService->calculateMetrics($realLambda1, $realLambda2, $realMu, $c);

        return Inertia::render('Admin/Reports', [
            'reportes' => $reportes,
            'filters'  => $request->only('search', 'c'),
            'queueMetrics' => $queueMetrics,
        ]);
    }

    public function hideReportedPublication(Request $request, $publicacionId)
    {
        Publicaciones::where('id', $publicacionId)->update([
            'estado'    => 'oculta',
            'oculta_at' => now(),
        ]);

        return back()->with('success', 'Publicación ocultada correctamente.');
    }
}
