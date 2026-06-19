@extends('pdf.layout')

@section('title', 'Reporte de Usuarios')

@section('content')
    <div style="margin-bottom: 20px;">
        <h2 style="margin: 0; color: #111827;">Reporte de Usuarios</h2>
        <p style="margin: 5px 0 0 0; color: #6B7280; font-size: 11px;">
            @if(!empty($filters['search']) || !empty($filters['rol']) || !empty($filters['universidad']) || !empty($filters['reputacion']))
                <strong>Filtros aplicados:</strong> 
                @if(!empty($filters['search'])) Búsqueda: "{{ $filters['search'] }}" @endif
                @if(!empty($filters['rolNombre'])) | Rol: {{ $filters['rolNombre'] }} @endif
                @if(!empty($filters['universidadNombre'])) | Univ: {{ $filters['universidadNombre'] }} @endif
                @if(!empty($filters['reputacion'])) | Reputación: {{ $filters['reputacion'] }} @endif
            @else
                Mostrando todos los usuarios registrados en el sistema.
            @endif
        </p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Nombre y Correo</th>
                <th style="width: 20%;">Rol</th>
                <th style="width: 30%;">Universidad</th>
                <th style="width: 10%;">Estado</th>
                <th style="width: 10%;">Reputación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $index => $u)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $u->name }} {{ optional($u->usuarioCampusMarket)->Apellidos }}</strong><br>
                        <span style="color: #6B7280; font-size: 10px;">{{ $u->email }}</span>
                    </td>
                    <td>
                        {{ optional(optional($u->usuarioCampusMarket)->rol)->Nombre_Rol ?? 'Sin Rol' }}
                    </td>
                    <td>
                        {{ optional(optional($u->usuarioCampusMarket)->universidad)->Nombre_Universidad ?? 'N/A' }}
                    </td>
                    <td>
                        @if(optional($u->usuarioCampusMarket)->verificado)
                            <span style="color: #059669;">Verificado</span>
                        @else
                            <span style="color: #DC2626;">No Verif.</span>
                        @endif
                    </td>
                    <td>
                        {{ optional($u->reputacionEstado)->estado_actual ?? 'N/A' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        No se encontraron usuarios con los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
