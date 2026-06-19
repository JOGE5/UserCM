<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin_notificaciones;
use App\Models\Roles;
use App\Models\User;
use App\Mail\AdminNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $notificaciones = Admin_notificaciones::with(['usuario', 'rol'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $roles = Roles::all();
        $usuarios = User::whereNotNull('email')->select('id', 'name', 'email')->get();

        return Inertia::render('Admin/Notifications', [
            'notificaciones' => $notificaciones,
            'roles' => $roles,
            'usuarios' => $usuarios,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_envio' => 'required|in:Usuario especifico,Por rol,A todos los usuarios',
            'ID_Usuario' => 'required_if:tipo_envio,Usuario especifico|nullable|exists:users,id',
            'Cod_Rol' => 'required_if:tipo_envio,Por rol|nullable|exists:roles,Cod_Rol',
            'Titulo_Notificacion' => 'required|string|max:150',
            'Mensaje_Notificacion' => 'required|string',
            'imgen' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('imgen')) {
            $imagePath = $request->file('imgen')->store('notificaciones', 'public');
        }

        $destinatarios = [];

        if ($data['tipo_envio'] === 'Usuario especifico') {
            $user = User::find($data['ID_Usuario']);
            if ($user && $user->email) {
                $destinatarios[] = $user;
            }
            $dest_name = $user ? $user->email : 'Usuario Invalido';
        } elseif ($data['tipo_envio'] === 'Por rol') {
            $rol = Roles::find($data['Cod_Rol']);
            $destinatarios = User::whereHas('usuarioCampusMarket', function ($q) use ($data) {
                $q->where('Cod_Rol', $data['Cod_Rol']);
            })->whereNotNull('email')->get();
            $dest_name = 'Rol: ' . ($rol ? $rol->Nombre_Rol : 'Desconocido');
        } else {
            $destinatarios = User::whereNotNull('email')->get();
            $dest_name = 'A todos los usuarios';
        }

        // Crear una notificación principal para el historial
        $notificacion = Admin_notificaciones::create([
            'tipo_envio' => $data['tipo_envio'],
            'Destinatario_Notificacion' => $dest_name,
            'ID_Usuario' => $data['ID_Usuario'] ?? null,
            'Cod_Rol' => $data['Cod_Rol'] ?? null,
            'Titulo_Notificacion' => $data['Titulo_Notificacion'],
            'Mensaje_Notificacion' => $data['Mensaje_Notificacion'],
            'imgen' => $imagePath,
            'Estado_Notificacion' => 'Pendiente',
        ]);

        $errores = 0;
        $enviados = 0;

        foreach ($destinatarios as $user) {
            try {
                // ShouldQueue interface on Mailable makes this queued automatically
                Mail::to($user->email)->send(new AdminNotificationMail($notificacion));
                $enviados++;
            } catch (\Exception $e) {
                Log::error("Error enviando notificacion a {$user->email}: " . $e->getMessage());
                $errores++;
            }
        }

        if ($enviados > 0 && $errores === 0) {
            $notificacion->update(['Estado_Notificacion' => 'Enviado', 'Fecha_Envio' => now()]);
            $msg = "Se enviaron $enviados correos correctamente.";
        } elseif ($enviados > 0 && $errores > 0) {
            $notificacion->update(['Estado_Notificacion' => 'Enviado (con errores)', 'Fecha_Envio' => now()]);
            $msg = "Se enviaron $enviados correos, fallaron $errores.";
        } else {
            $notificacion->update(['Estado_Notificacion' => 'Error', 'Fecha_Envio' => null]);
            $msg = "No se pudieron enviar los correos.";
        }

        return back()->with('success', $msg);
    }
}
