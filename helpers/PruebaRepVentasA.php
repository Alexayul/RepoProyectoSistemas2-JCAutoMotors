<?php
date_default_timezone_set('America/La_Paz');

require_once '../config/conexion.php';
require_once '../controllers/VentasAdminController.php';
require_once '../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Iniciar sesión para manejar los filtros
session_start();

// Iniciar controlador
$controller = new VentasAdminController($conn);

// Obtener filtros de la sesión o de GET
$filtros = [
    'fecha_desde' => $_GET['fecha_desde'] ?? null,
    'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
    'estado' => $_GET['estado'] ?? null,
    'tipo_pago' => $_GET['tipo_pago'] ?? null,
    'empleado' => $_GET['empleado'] ?? null
];

// Obtener ventas con filtros
$ventas = $controller->obtenerVentas($filtros);

// Configurar opciones de DomPDF
$options = new Options();
$options->set('isHTML5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

// Crear instancia de DomPDF
$dompdf = new Dompdf($options);

// Generar HTML para el PDF
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - JC Automotors</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            color: #333;
            background-color: #fff;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #A51314;
            padding-bottom: 10px;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            min-width: 200px;
            text-align: center;
            padding: 15px;
            border: 1px solid #A51314;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #A51314;
            font-size: 14px;
        }
        .stat-card p {
            margin: 5px 0;
            font-weight: bold;
            color: #333;
        }
        .header h1 {
            color: #701106;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color:rgb(255, 255, 255);
            padding: 10px;
            border-radius: 5px;
            color: white;
        }
        .stat-card {
            text-align: center;
            flex: 1;
            margin: 0 5px;
            padding: 10px;
            border: 1px solid #A51314;
            border-radius: 5px;
            background-color: rgba(165, 19, 20, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
        }
        table, th, td {
            border: 1px solid #701106;
        }
        th {
            background-color: #A51314;
            color: white;
            padding: 8px;
            text-align: left;
        }
        td {
            padding: 8px;
            background-color: white;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { 
            background-color: #050506; 
            color: white; 
        }
        .badge-warning { 
            background-color: #868686; 
            color: white; 
        }
        .badge-info { 
            background-color: #701106; 
            color: white; 
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Concesionaria JC Automotors" class="logo">
        <h1>Reporte de Ventas - JC Automotors</h1>
        <p style="color: #666; margin: 0; font-size: 13px;">Generado el ' . date('d/m/Y H:i:s') . '</p>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Ventas Totales</h3>
            <p>' . count($ventas) . '</p>
        </div>
        <br>
        <div class="stat-card">
            <h3>Ventas Completadas</h3>
            <p>' . count(array_filter($ventas, fn($v) => $v['estado'] === 'Completada')) . '</p>
        </div>
        <br>
        <div class="stat-card">
            <h3>Ventas Pendientes</h3>
            <p>' . count(array_filter($ventas, fn($v) => $v['estado'] === 'Pendiente')) . '</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Saldo Total</h3>
            <p>$ ' . number_format(array_sum(array_column($ventas, 'monto_total')), 2) . '</p>
            <p>Bs. ' . number_format(array_sum(array_column($ventas, 'monto_total')) * 7, 2) . '</p>
        </div>
        <br>
        <div class="stat-card">
            <h3>Saldo Pendiente</h3>
            <p>$ ' . number_format(array_sum(array_column($ventas, 'saldo_pendiente')), 2) . '</p>
            <p>Bs. ' . number_format(array_sum(array_column($ventas, 'saldo_pendiente')) * 7, 2) . '</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Empleado</th>
                <th>Tipo Pago</th>
                <th>Monto Total</th>
                <th>Adelanto</th>
                <th>Saldo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>';

foreach ($ventas as $v) {
    $html .= '
        <tr>
            <td>' . date('d/m/Y', strtotime($v['fecha_venta'])) . '</td>
            <td>' . htmlspecialchars($v['nombre_cliente'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($v['nombre_empleado'] ?? 'N/A') . '</td>
            <td>
                <span class="badge ' . 
                    ($v['tipo_pago'] == 'Al contado' ? 'badge-success' : 
                    ($v['tipo_pago'] == 'Financiamiento bancario' ? 'badge-info' : 'badge-warning')) . '">
                    ' . htmlspecialchars($v['tipo_pago']) . '
                </span>
            </td>
            <td>$ ' . number_format($v['monto_total'], 2) . '</td>
            <td>$ ' . number_format($v['adelanto'], 2) . '</td>
            <td>$ ' . number_format($v['saldo_pendiente'], 2) . '</td>
            <td>
                <span class="badge ' . 
                    ($v['estado'] == 'Completada' ? 'badge-success' : 
                    ($v['estado'] == 'Pendiente' ? 'badge-warning' : 'badge-info')) . '">
                    ' . htmlspecialchars($v['estado']) . '
                </span>
            </td>
        </tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Cargar HTML en DomPDF
$dompdf->loadHtml($html);

// Configurar papel y orientación
$dompdf->setPaper('A4', 'landscape');

// Renderizar PDF
$dompdf->render();

// Generar nombre de archivo con fecha
$filename = 'reporte_ventas_' . date('Y-m-d_H-i-s') . '.pdf';

// Mostrar PDF en el navegador
$dompdf->stream($filename, array("Attachment" => false));
?>
