<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Reporte PDF')</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            vertical-align: middle;
        }
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }
        .subtitle {
            font-size: 12px;
            color: #6B7280;
        }
        .text-right {
            text-align: right;
        }
        .date {
            font-size: 10px;
            color: #6B7280;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 10px;
            color: #6B7280;
            text-align: center;
            border-top: 1px solid #E5E7EB;
            padding-top: 5px;
        }
        .page-number:before {
            content: "Página " counter(page);
        }
        .content {
            margin-bottom: 40px;
        }
        /* Tablas */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB;
            text-align: left;
        }
        .table th {
            background-color: #F9FAFB;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .table tr:nth-child(even) {
            background-color: #F9FAFB;
        }
    </style>
</head>
<body>

    <!-- Header / Membrete -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 50%;">
                    <div class="logo-text">Campus Market</div>
                    <div class="subtitle">Reporte Oficial del Sistema</div>
                </td>
                <td style="width: 50%;" class="text-right">
                    <div class="date">Fecha de Emisión: {{ now()->format('d/m/Y H:i') }}</div>
                    <div class="date">Generado por: Administrador</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Contenido -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Footer -->
    <div class="footer">
        Campus Market - Documento generado automáticamente &bull; <span class="page-number"></span>
    </div>

</body>
</html>
