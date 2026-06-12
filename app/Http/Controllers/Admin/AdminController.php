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

    public function reports(Request $request)
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

        return Inertia::render('Admin/Reports', [
            'reportes' => $reportes,
            'filters'  => $request->only('search'),
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
