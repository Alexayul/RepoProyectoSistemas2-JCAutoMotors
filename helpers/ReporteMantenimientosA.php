<?php
date_default_timezone_set('America/La_Paz');

require_once '../config/conexion.php';
require_once '../controllers/MantenimientosAdminController.php';
require_once '../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Iniciar sesión para manejar los filtros
session_start();

// Iniciar controlador
$controller = new MantenimientosAdminController($conn);

// Obtener filtros de la sesión o de GET (puedes agregar más si lo deseas)
$filtros = [
    'fecha_desde' => $_GET['fecha_desde'] ?? null,
    'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
    'tipo' => $_GET['tipo'] ?? null,
    'cliente' => $_GET['cliente'] ?? null,
    'empleado' => $_GET['empleado'] ?? null
];

$mantenimientos = $controller->obtenerMantenimientos($filtros);

// Configurar opciones de DomPDF
$options = new Options();
$options->set('isHTML5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

// Crear instancia de DomPDF
$dompdf = new Dompdf($options);

$logoPath = $_SERVER['DOCUMENT_ROOT'] . '/RepoProyectoSistemas2-JCAutoMotors/public/logo.png';
$logoData = '';
if (file_exists($logoPath)) {
    $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
    $logoBase64 = base64_encode(file_get_contents($logoPath));
    $logoData = 'data:image/' . $logoType . ';base64,' . $logoBase64;
}

// Generar HTML para el PDF
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Mantenimientos - JC Automotors</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            color: #333;
            background-color: #fff;
            margin: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #A51314;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header img {
            max-width: 170px;
            max-height: 110px;
        }
        .header h1 {
            margin: 0;
            color: #701106;
            font-size: 22px;
            padding-left: 18px;
            text-align: left;
        }
        .header p {
            color: #666;
            margin: 0;
            font-size: 13px;
            padding-left: 18px;
            text-align: left;
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
            background-color: #198754; 
            color: white; 
        }
        .badge-primary { 
            background-color: #A51314; 
            color: white; 
        }
        .badge-gratis { background: #701106; color: #fff; }
        .badge-pagado { background: #050506; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <img src="' . $logoData . '" alt="JC Automotors">
        <div>
            <h1>Reporte de Mantenimientos - JC Automotors</h1>
            <p>Generado el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Mantenimientos Totales</h3>
            <p>' . count($mantenimientos) . '</p>
        </div>
        <div class="stat-card">
            <h3>Gratuitos</h3>
            <p>' . count(array_filter($mantenimientos, fn($m) => $m['es_gratuito'])) . '</p>
        </div>
        <div class="stat-card">
            <h3>Pagados</h3>
            <p>' . count(array_filter($mantenimientos, fn($m) => !$m['es_gratuito'])) . '</p>
        </div>
    </div>

    <div style="margin-bottom:15px; font-size:13px;">
        <strong>Filtros aplicados:</strong>
        <ul style="margin:0; padding-left:18px;">
            '.($filtros['fecha_desde'] ? "<li>Desde: <b>".htmlspecialchars($filtros['fecha_desde'])."</b></li>" : "").'
            '.($filtros['fecha_hasta'] ? "<li>Hasta: <b>".htmlspecialchars($filtros['fecha_hasta'])."</b></li>" : "").'
            '.($filtros['tipo'] ? "<li>Tipo: <b>".htmlspecialchars($filtros['tipo'])."</b></li>" : "").'
            '.($filtros['cliente'] ? "<li>Cliente: <b>".htmlspecialchars($filtros['cliente'])."</b></li>" : "").'
            '.($filtros['empleado'] ? "<li>Empleado: <b>".htmlspecialchars($filtros['empleado'])."</b></li>" : "").'
            '.(!$filtros['fecha_desde'] && !$filtros['fecha_hasta'] && !$filtros['tipo'] && !$filtros['cliente'] && !$filtros['empleado'] ? "<li>Ninguno (mostrando todos los mantenimientos)</li>" : "").'
        </ul>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Empleado</th>
                <th>Motocicleta</th>
                <th>Tipo</th>
                <th>Costo</th>
                <th>Estado</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>';

foreach ($mantenimientos as $m) {
    $html .= '
        <tr>
            <td>' . date('d/m/Y', strtotime($m['fecha'])) . '</td>
            <td>' . htmlspecialchars($m['nombre_cliente'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($m['nombre_empleado'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($m['modelo_motocicleta'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($m['tipo'] ?? 'N/A') . '</td>
            <td>Bs ' . number_format($m['costo'], 2) . '</td>
            <td>
                <span class="badge ' . ($m['es_gratuito'] ? 'badge-gratis' : 'badge-pagado') . '">
                    ' . ($m['es_gratuito'] ? 'Gratuito' : 'Pagado') . '
                </span>
            </td>
            <td>' . (empty($m['observaciones']) ? '<em>Sin observaciones</em>' : htmlspecialchars($m['observaciones'])) . '</td>
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
$filename = 'reporte_mantenimientos_' . date('Y-m-d_H-i-s') . '.pdf';

// Mostrar PDF en el navegador
$dompdf->stream($filename, array("Attachment" =>(false)));