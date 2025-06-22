<?php
date_default_timezone_set('America/La_Paz');

require_once '../config/conexion.php';
require_once '../controllers/CreditoController.php';
require_once '../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

$controller = new CreditoController($conn);

// Puedes adaptar los filtros si los usas en la vista
$filtros = [
    'cliente'      => $_GET['cliente']      ?? '',
    'fecha_venta'  => $_GET['fecha_venta']  ?? '',
    'fecha_desde'  => $_GET['fecha_desde']  ?? '',
    'fecha_hasta'  => $_GET['fecha_hasta']  ?? '',
    'saldo'        => $_GET['saldo']        ?? '',
    'atraso'       => $_GET['atraso']       ?? '',
];

if (
    !empty($filtros['cliente']) ||
    !empty($filtros['fecha_venta']) ||
    !empty($filtros['fecha_desde']) ||
    !empty($filtros['fecha_hasta']) ||
    !empty($filtros['saldo']) ||
    !empty($filtros['atraso'])
) {
    $creditos = $controller->obtenerCreditosFiltrados($filtros);
} else {
    $creditos = $controller->obtenerCreditosDirectos();
}

// Estadísticas
$total_creditos = count($creditos);
$completados = count(array_filter($creditos, fn($c) => $c['estado'] === 'Completada'));
$pendientes = count(array_filter($creditos, fn($c) => $c['estado'] === 'Pendiente'));
$saldo_total = array_sum(array_column($creditos, 'monto_total'));
$saldo_pendiente = array_sum(array_column($creditos, 'saldo_pendiente'));

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
    <title>Reporte de Créditos Directos - JC Automotors</title>
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
        .badge-pendiente { background: #701106; color: #fff; }
        .badge-completada { background: #050506; color: #fff; }
        .badge-atrasado { background: #fff; color: #701106; border: 1px solid #701106; }
        .footer { text-align: right; font-size: 11px; color: #888; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="' . $logoData . '" alt="JC Automotors">
        <div>
            <h1>Reporte de Créditos Directos</h1>
            <p>Generado el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Créditos Totales</h3>
            <p>' . $total_creditos . '</p>
        </div>
        <div class="stat-card">
            <h3>Completados</h3>
            <p>' . $completados . '</p>
        </div>
        <div class="stat-card">
            <h3>Pendientes</h3>
            <p>' . $pendientes . '</p>
        </div>
        <div class="stat-card">
            <h3>Saldo Total</h3>
            <p>$' . number_format($saldo_total, 2) . '<br><span style="font-size:12px;">Bs. ' . number_format($saldo_total * 7, 2) . '</span></p>
        </div>
        <div class="stat-card">
            <h3>Saldo Pendiente</h3>
            <p>$' . number_format($saldo_pendiente, 2) . '<br><span style="font-size:12px;">Bs. ' . number_format($saldo_pendiente * 7, 2) . '</span></p>
        </div>
    </div>

    <div class="table-section">
        <h2 style="color:#A51314; font-size:17px; margin-bottom:8px;">Listado de Créditos Directos</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Fecha Venta</th>
                    <th>Monto Total ($)</th>
                    <th>Adelanto ($)</th>
                    <th>Saldo Pendiente ($)</th>
                    <th>Pagos Realizados</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';

if (!empty($creditos)) {
    foreach ($creditos as $i => $c) {
        $badge_estado = $c['estado'] === 'Pendiente'
            ? '<span class="badge badge-pendiente">Pendiente</span>'
            : '<span class="badge badge-completada">Completada</span>';
        $html .= '
            <tr>
                <td>' . ($i + 1) . '</td>
                <td>' . htmlspecialchars($c['nombre']) . '</td>
                <td>' . date('d/m/Y', strtotime($c['fecha_venta'])) . '</td>
                <td>$' . number_format($c['monto_total'], 2) . '<br><span style="font-size:11px;">Bs. ' . number_format($c['monto_total'] * 7, 2) . '</span></td>
                <td>$' . number_format($c['adelanto'], 2) . '<br><span style="font-size:11px;">Bs. ' . number_format($c['adelanto'] * 7, 2) . '</span></td>
                <td>$' . number_format($c['saldo_pendiente'], 2) . '<br><span style="font-size:11px;">Bs. ' . number_format($c['saldo_pendiente'] * 7, 2) . '</span></td>
                <td>' . $c['pagos_realizados'] . ' de ' . $c['total_pagos'] . '</td>
                <td>' . $badge_estado . '</td>
            </tr>';
    }
} else {
    $html .= '
        <tr>
            <td colspan="8" style="text-align:center; color:#A51314;">No hay créditos registrados.</td>
        </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>
';

if (!empty($creditos)) {
    $html .= '
    <div class="table-section">
        <h2 style="color:#A51314; font-size:17px; margin-bottom:8px;">Detalle de Pagos por Crédito</h2>';
    foreach ($creditos as $i => $c) {
        $pagos = $controller->obtenerPagosProgramados($c['id_venta']);
        if (empty($pagos)) continue;
        $html .= '
        <h4 style="margin:18px 0 6px 0; color:#701106;">' . htmlspecialchars($c['nombre']) . ' - Venta: ' . date('d/m/Y', strtotime($c['fecha_venta'])) . '</h4>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha Pago</th>
                    <th>Monto $</th>
                    <th>Monto Bs.</th>
                    <th>Mora $</th>
                    <th>Mora Bs.</th>
                    <th>Monto Pagado</th>
                    <th>Estado</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($pagos as $j => $pago) {
            $badge_pago = $pago['estado'] === 'Completada'
                ? '<span class="badge badge-completada">Completada</span>'
                : ($pago['estado'] === 'Atrasado'
                    ? '<span class="badge badge-atrasado">Atrasado</span>'
                    : '<span class="badge badge-pendiente">Pendiente</span>');

            $mora = $pago['monto_mora'] ?? 0;
            $html .= '
                <tr>
                    <td>' . ($j + 1) . '</td>
                    <td>' . date('d/m/Y', strtotime($pago['fecha_pago'])) . '</td>
                    <td>$' . number_format($pago['monto'], 2) . '</td>
                    <td>Bs. ' . number_format($pago['monto'] * 7, 2) . '</td>
                    <td>' . ($mora > 0 ? '<span class="badge badge-atrasado">$' . number_format($mora, 2) . '</span>' : '<span class="text-muted">$0.00</span>') . '</td>
                    <td>' . ($mora > 0 ? '<span class="badge badge-atrasado">Bs. ' . number_format($mora * 7, 2) . '</span>' : '<span class="text-muted">Bs. 0.00</span>') . '</td>
                    <td>$' . number_format($pago['monto_pagado'], 2) . '</td>
                    <td>' . $badge_pago . '</td>
                    <td>' . ($pago['fecha_pagado'] ? date('d/m/Y', strtotime($pago['fecha_pagado'])) : '-') . '</td>
                </tr>';
        }
        $html .= '
            </tbody>
        </table>';
    }
    $html .= '</div>';
}

$html .= '
    <div class="footer">
        JC Automotors &copy; ' . date('Y') . '
    </div>
</body>
</html>';

// Agregar sección de filtros aplicados
$html .= '
<div style="margin-bottom:15px; font-size:13px;">
    <strong>Filtros aplicados:</strong>
    <ul style="margin:0; padding-left:18px;">
        '.($filtros['cliente'] ? "<li>Cliente: <b>".htmlspecialchars($filtros['cliente'])."</b></li>" : "").'
        '.($filtros['fecha_venta'] ? "<li>Fecha de venta: <b>".htmlspecialchars($filtros['fecha_venta'])."</b></li>" : "").'
        '.($filtros['fecha_desde'] ? "<li>Desde: <b>".htmlspecialchars($filtros['fecha_desde'])."</b></li>" : "").'
        '.($filtros['fecha_hasta'] ? "<li>Hasta: <b>".htmlspecialchars($filtros['fecha_hasta'])."</b></li>" : "").'
        '.($filtros['saldo'] ? "<li>Saldo: <b>".htmlspecialchars($filtros['saldo'])."</b></li>" : "").'
        '.($filtros['atraso'] ? "<li>Atraso: <b>".htmlspecialchars($filtros['atraso'])."</b></li>" : "").'
        '.(!$filtros['cliente'] && !$filtros['fecha_venta'] && !$filtros['fecha_desde'] && !$filtros['fecha_hasta'] && !$filtros['saldo'] && !$filtros['atraso'] ? "<li>Ninguno (mostrando todos los créditos)</li>" : "").'
    </ul>
</div>
';

// Renderizar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$filename = 'reporte_creditos_' . date('Y-m-d_H-i-s') . '.pdf';
$dompdf->stream($filename, array("Attachment" => false));
?>