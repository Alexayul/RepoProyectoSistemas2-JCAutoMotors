<?php
date_default_timezone_set('America/La_Paz');

require_once '../config/conexion.php';
require_once '../controllers/ClienteController.php';
require_once '../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

$controller = new ClienteController($conn);

// Obtener todos los clientes
$clientes = $controller->index();

// Estadísticas
$total_clientes = count($clientes);
$clientes_nuevos = count(array_filter($clientes, function($c) {
    $fecha_registro = strtotime($c['fecha_registro'] ?? date('Y-m-d'));
    return (time() - $fecha_registro) < (30 * 24 * 60 * 60);
}));

// Opciones DomPDF
$options = new Options();
$options->set('isHTML5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);
$logoPath = $_SERVER['DOCUMENT_ROOT'] . '/RepoProyectoSistemas2-JCAutoMotors/public/logo.png';
$logoData = '';
if (file_exists($logoPath)) {
    $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
    $logoBase64 = base64_encode(file_get_contents($logoPath));
    $logoData = 'data:image/' . $logoType . ';base64,' . $logoBase64;
}

// HTML del reporte
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes - JC Automotors</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; background: #fff; }
        .header { display: flex; align-items: center; border-bottom: 2px solid #A51314; margin-bottom: 20px; padding-bottom: 10px; }
        .header img { max-width: 170px; max-height: 110px; }
        .header h1 { margin: 0; color: #701106; font-size: 22px; padding-left: 18px; text-align: left; }
        .header p { color: #666; margin: 0; font-size: 13px; padding-left: 18px; text-align: left; }
        .stats-row { display: flex; justify-content: center; gap: 30px; margin-bottom: 25px; }
        .stat-card { flex: 0 0 200px; margin: 0; padding: 12px 10px; border: 1px solid #A51314; border-radius: 8px; text-align: center; background-color: #f9f2f2; box-shadow: 0 2px 6px rgba(0,0,0,0.04);}
        .stat-card h3 { margin: 0 0 8px 0; color: #A51314; font-size: 16px; }
        .stat-card p { margin: 0; font-weight: bold; color: #333; font-size: 15px; }
        .table-section { margin: 0 auto 30px auto; width: 98%; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #A51314; padding: 6px 4px; font-size: 12px; }
        th { background: #A51314; color: #fff; }
        tr:nth-child(even) { background: #f9f2f2; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-success { background: #198754; color: #fff; }
        .badge-secondary { background: #6c757d; color: #fff; }
        .footer { text-align: right; font-size: 11px; color: #888; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="' . $logoData . '" alt="JC Automotors">
        <div>
            <h1>Reporte de Clientes</h1>
            <p>Generado el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Total Clientes</h3>
            <p>' . $total_clientes . '</p>
        </div>
        <div class="stat-card">
            <h3>Nuevos (últimos 30 días)</h3>
            <p>' . $clientes_nuevos . '</p>
        </div>
    </div>

    <div class="table-section">
        <h2 style="color:#A51314; font-size:17px; margin-bottom:8px;">Listado Completo de Clientes</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre Completo</th>
                    <th>CI</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Fecha Registro</th>
                    <th>Croquis Domicilio</th>
                    <th>Factura Servicio</th>
                </tr>
            </thead>
            <tbody>';

if (!empty($clientes)) {
    foreach ($clientes as $i => $c) {
        $badge_croquis = !empty($c['croquis_domicilio'])
            ? '<span class="badge" style="background:#701106; color:#fff;">Entregado</span>'
            : '<span class="badge" style="background:#050506; color:#fff;">No entregado</span>';
        $badge_factura = !empty($c['factura_servicio'])
            ? '<span class="badge" style="background:#701106; color:#fff;">Entregado</span>'
            : '<span class="badge" style="background:#050506; color:#fff;">No entregado</span>';
        $fecha_registro = isset($c['fecha_registro']) ? date('d/m/Y', strtotime($c['fecha_registro'])) : '-';
        $html .= '
            <tr>
                <td>' . ($i + 1) . '</td>
                <td>' . htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) . '</td>
                <td>' . htmlspecialchars($c['documento_identidad']) . '</td>
                <td>' . htmlspecialchars($c['telefono']) . '</td>
                <td>' . htmlspecialchars($c['email']) . '</td>
                <td>' . $fecha_registro . '</td>
                <td>' . $badge_croquis . '</td>
                <td>' . $badge_factura . '</td>
            </tr>';
    }
} else {
    $html .= '
        <tr>
            <td colspan="8" style="text-align:center; color:#A51314;">No hay clientes registrados.</td>
        </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div class="footer">
        JC Automotors &copy; ' . date('Y') . '
    </div>
</body>
</html>';

// Renderizar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$filename = 'reporte_clientes_' . date('Y-m-d_H-i-s') . '.pdf';
$dompdf->stream($filename, array("Attachment" => false));
?>