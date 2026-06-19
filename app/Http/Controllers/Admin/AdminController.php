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

        $forecastService = app(\App\Services\ForecastService::class);
        $forecast = $forecastService->predictNextWeekPublications(0.3); // alpha = 0.3

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'forecast' => $forecast,
        ]);
    }

    public function users(Request $request)
    {
        $query = User::with(['usuarioCampusMarket.rol', 'usuarioCampusMarket.universidad', 'reputacionEstado'])
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
        $universidades = \App\Models\Universidad::with('carreras')
            ->orderBy('Nombre_Universidad')
            ->get();

        return Inertia::render('Admin/Users', [
            'usuarios'      => $usuarios,
            'roles'         => $roles,
            'universidades' => $universidades,
            'filters'       => $request->only('search', 'rol'),
        ]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'Apellidos'       => 'nullable|string|max:60',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8',
            'Cod_Rol'         => 'required|exists:roles,Cod_Rol',
            'Cod_Universidad' => 'required|exists:universidades,Cod_Universidad',
            'Cod_Carrera'     => 'required|exists:carreras,Cod_Carrera',
            'verificado'      => 'boolean',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);
        $user->email_verified_at = now();
        $user->save();

        $user->usuarioCampusMarket()->create([
            'Apellidos'       => $data['Apellidos'] ?? null,
            'Estado'          => 'Activo',
            'Cod_Rol'         => $data['Cod_Rol'],
            'Cod_Universidad' => $data['Cod_Universidad'],
            'Cod_Carrera'     => $data['Cod_Carrera'],
            'verificado'      => $request->boolean('verificado'),
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
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

    // ==================== GESTIÓN DE ROLES ====================

    public function roles()
    {
        $roles = Roles::withCount('usuariosCampusMarket')
            ->orderBy('Cod_Rol')
            ->get();

        return Inertia::render('Admin/Roles', [
            'roles' => $roles,
        ]);
    }

    public function rolesStore(Request $request)
    {
        $data = $request->validate([
            'Nombre_Rol'  => 'required|string|max:255|unique:roles,Nombre_Rol',
            'Descripcion' => 'nullable|string|max:255',
        ]);

        Roles::create($data);

        return back()->with('success', 'Rol creado correctamente.');
    }

    public function rolesUpdate(Request $request, Roles $rol)
    {
        $data = $request->validate([
            'Nombre_Rol'  => 'required|string|max:255|unique:roles,Nombre_Rol,'.$rol->Cod_Rol.',Cod_Rol',
            'Descripcion' => 'nullable|string|max:255',
        ]);

        $rol->update($data);

        return back()->with('success', 'Rol actualizado correctamente.');
    }

    public function rolesDestroy(Roles $rol)
    {
        // Los 3 roles base del sistema no se pueden borrar.
        if (in_array($rol->Cod_Rol, [1, 2, 3])) {
            return back()->with('error', 'No se pueden eliminar los roles base del sistema.');
        }

        // No borrar un rol que tenga usuarios asignados.
        if ($rol->usuariosCampusMarket()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
        }

        $rol->delete();

        return back()->with('success', 'Rol eliminado correctamente.');
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
        $query = \App\Models\Report::with(['reporter', 'reportable'])
            ->where(function($q) {
                $q->where('status', 'pending')
                  ->orWhereNull('status');
            })
            ->orderBy('created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->whereHasMorph('reportable', [\App\Models\Publicaciones::class, \App\Models\Foro::class], function($q, $type) use ($search) {
                if ($type === \App\Models\Publicaciones::class) {
                    $q->where('Titulo_Publicacion', 'like', "%{$search}%");
                } elseif ($type === \App\Models\Foro::class) {
                    $q->where('Titulo_Foro', 'like', "%{$search}%");
                }
            });
        }

        // Map data to match what the Vue component expects
        $reportes = $query->paginate(20)->withQueryString();
        
        // Transform items to include unified fields for the UI
        $reportes->getCollection()->transform(function ($report) {
            $titulo = 'Contenido eliminado';
            $estado = '—';
            $autorId = null;
            
            if ($report->reportable) {
                if ($report->reportable_type === \App\Models\Publicaciones::class || str_contains($report->reportable_type, 'Publicaciones')) {
                    $titulo = '[Publicación] ' . $report->reportable->Titulo_Publicacion;
                    $estado = $report->reportable->estado;
                    $autorId = $report->reportable->vendedor?->user_id;
                } else {
                    $titulo = '[Foro] ' . $report->reportable->Titulo_Foro;
                    $estado = $report->reportable->Estado_Foro ? 'activo' : 'oculto';
                    $autorId = $report->reportable->creador?->user_id ?? $report->reportable->ID_Creador; // Dependiendo de la relación
                }
            }

            return [
                'id' => $report->id,
                'reportable_type' => $report->reportable_type,
                'reportable_id' => $report->reportable_id,
                'reporter_name' => $report->reporter?->name ?? 'Sistema NLP',
                'reporter_email' => $report->reporter?->email ?? 'Bot',
                'reason' => $report->reason,
                'created_at' => $report->created_at,
                'pub_estado' => $estado,
                'Titulo_Publicacion' => $titulo,
                'autor_user_id' => $autorId,
            ];
        });

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

        // -------------------------------------------------------------
        // ASIGNACIÓN ÓPTIMA DE MODERADORES (Algoritmo Húngaro / Heurístico)
        // -------------------------------------------------------------
        $assignmentService = app(\App\Services\ModeratorAssignmentService::class);
        $assignments = $assignmentService->getOptimalAssignments(collect($reportes->items()));

        return Inertia::render('Admin/Reports', [
            'reportes' => $reportes,
            'filters'  => $request->only('search', 'c'),
            'queueMetrics' => $queueMetrics,
            'assignments' => $assignments,
        ]);
    }

    public function resolveReport(Request $request, $reportId)
    {
        $report = \App\Models\Report::findOrFail($reportId);
        
        // 1. Ocultar el contenido (Publicación o Foro)
        if ($report->reportable_type === \App\Models\Publicaciones::class || str_contains($report->reportable_type, 'Publicaciones')) {
            $report->reportable->update([
                'estado'    => 'oculta',
                'oculta_at' => now(),
            ]);
        } elseif ($report->reportable_type === \App\Models\Foro::class || str_contains($report->reportable_type, 'Foro')) {
            $report->reportable->update([
                'Estado_Foro' => 0, // 0 = Oculto
            ]);
        }

        // 2. Opcional: Castigar al usuario en Markov (-50)
        $autorId = null;
        if ($report->reportable_type === \App\Models\Publicaciones::class || str_contains($report->reportable_type, 'Publicaciones')) {
            $autorId = $report->reportable->vendedor?->user_id;
        } else {
            // Foros
            $autorId = $report->reportable->creador?->user_id ?? $report->reportable->ID_Creador;
        }

        if ($autorId) {
            $autorUser = \App\Models\User::find($autorId);
            if ($autorUser) {
                // Crear una puntuación negativa en ReputacionEntreUsuarios
                \App\Models\ReputacionEntreUsuarios::create([
                    'ID_Usuario_Calificador' => auth()->id(), // El Admin
                    'ID_Usuario_Calificado' => $autorId,
                    'Puntuacion' => 1, // La puntuación más baja posible
                    'Comentario' => 'Sanción administrativa por violación de normas.',
                ]);
                
                // Actualizar estado Markov
                $markovService = new \App\Services\MarkovReputationService();
                $markovService->actualizarEstado($autorUser);
            }
        }

        // 3. Cerrar el reporte
        $report->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'admin_note' => 'Contenido ocultado y usuario sancionado.',
        ]);

        return back()->with('success', 'Reporte resuelto. Contenido oculto y usuario sancionado.');
    }

    public function dismissReport(Request $request, $reportId)
    {
        $report = \App\Models\Report::findOrFail($reportId);
        $report->update([
            'status' => 'dismissed',
            'resolved_at' => now(),
            'admin_note' => 'Reporte descartado (Falsa alarma).',
        ]);

        return back()->with('success', 'Reporte descartado. La cola se ha liberado.');
    }

    // ==================== GESTIÓN DE UNIVERSIDADES ====================

    public function universities()
    {
        $universidades = \App\Models\Universidad::withCount('carreras')
            ->orderBy('Nombre_Universidad')
            ->get();

        return Inertia::render('Admin/Universities', [
            'universidades' => $universidades,
        ]);
    }

    public function universitiesStore(Request $request)
    {
        $data = $request->validate([
            'Nombre_Universidad' => 'required|string|max:255|unique:universidades,Nombre_Universidad',
            'Sede_Universidad' => 'nullable|string|max:255',
            'imgen' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('imgen')) {
            $imagePath = $request->file('imgen')->store('universidades', 'public');
        }

        \App\Models\Universidad::create([
            'Nombre_Universidad' => $data['Nombre_Universidad'],
            'Sede_Universidad' => $data['Sede_Universidad'] ?? null,
            'Universisdad_foto_de_perfil' => $imagePath, // Typo in original DB schema preserved
        ]);

        return back()->with('success', 'Universidad creada correctamente.');
    }

    public function universitiesUpdate(Request $request, \App\Models\Universidad $universidad)
    {
        $data = $request->validate([
            'Nombre_Universidad' => 'required|string|max:255|unique:universidades,Nombre_Universidad,'.$universidad->Cod_Universidad.',Cod_Universidad',
            'Sede_Universidad' => 'nullable|string|max:255',
            'imgen' => 'nullable|image|max:2048',
        ]);

        $updateData = [
            'Nombre_Universidad' => $data['Nombre_Universidad'],
            'Sede_Universidad' => $data['Sede_Universidad'] ?? null,
        ];

        if ($request->hasFile('imgen')) {
            $updateData['Universisdad_foto_de_perfil'] = $request->file('imgen')->store('universidades', 'public');
        }

        $universidad->update($updateData);

        return back()->with('success', 'Universidad actualizada correctamente.');
    }

    public function universitiesDestroy(\App\Models\Universidad $universidad)
    {
        if ($universidad->carreras()->count() > 0) {
            return back()->with('error', 'No se puede eliminar porque tiene carreras asociadas.');
        }

        $universidad->delete();

        return back()->with('success', 'Universidad eliminada correctamente.');
    }

    // ==================== GESTIÓN DE FOROS ====================

    public function forums(Request $request)
    {
        $query = \App\Models\Foro::with(['autor', 'categoria'])
            ->withCount('comentarios')
            ->orderBy('created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where('Titulo_Foro', 'like', "%{$search}%");
        }

        if ($estado = $request->get('estado')) {
            $query->where('Estado_Foro', $estado);
        }

        $foros = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Forums', [
            'foros' => $foros,
            'filters' => $request->only('search', 'estado'),
        ]);
    }

    public function forumsUpdate(Request $request, \App\Models\Foro $foro)
    {
        $request->validate(['Estado_Foro' => 'required|boolean']);
        $foro->update(['Estado_Foro' => $request->Estado_Foro]);

        return back()->with('success', 'Estado del foro actualizado.');
    }

    public function forumsDestroy(\App\Models\Foro $foro)
    {
        // Esto depende de cómo tengas configurada tu base de datos (cascada o no)
        $foro->comentarios()->delete();
        $foro->delete();

        return back()->with('success', 'Foro eliminado correctamente.');
    }
}
