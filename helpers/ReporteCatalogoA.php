<?php
date_default_timezone_set('America/La_Paz');
require_once '../config/conexion.php';
require_once '../controllers/CatalogoController.php';
require_once '../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

$controller = new CatalogoController($conn);

// Filtros (puedes adaptarlos si usas filtros en la vista)
$brandFilter = $_GET['brand'] ?? '';
$modelFilter = $_GET['model'] ?? '';
$ccFilter = $_GET['cc'] ?? '';
$motocicletas = $controller->obtenerMotocicletas($brandFilter, $modelFilter, $ccFilter);

// Obtener motocicletas y estadísticas
$totalMotos = array_sum(array_column($motocicletas, 'cantidad'));
$modelosUnicos = count(array_unique(array_column($motocicletas, 'modelo')));
$marcasUnicas = count(array_unique(array_column($motocicletas, 'marca')));

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
    <title>Reporte de Catálogo de Motocicletas - JC Automotors</title>
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
        .badge-info { background: #0dcaf0; color: #fff; }
        .badge-danger { background: #dc3545; color: #fff; }
        .badge-success { background: #198754; color: #fff; }
        .badge-warning { background: #ffc107; color: #333; }
        .footer { text-align: right; font-size: 11px; color: #888; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="' . $logoData . '" alt="JC Automotors">
        <div>
            <h1>Reporte de Catálogo de Motocicletas</h1>
            <p>Generado el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Total en Stock</h3>
            <p>' . $totalMotos . '</p>
        </div>
        <div class="stat-card">
            <h3>Modelos Únicos</h3>
            <p>' . $modelosUnicos . '</p>
        </div>
        <div class="stat-card">
            <h3>Marcas Únicas</h3>
            <p>' . $marcasUnicas . '</p>
        </div>
    </div>

    <div class="table-section">
        <h2 style="color:#A51314; font-size:17px; margin-bottom:8px;">Inventario de Motocicletas</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Cilindrada</th>
                    <th>Color</th>
                    <th>Stock</th>
                    <th>Precio USD</th>
                    <th>Precio Bs</th>
                    <th>Fecha Ingreso</th>
                </tr>
            </thead>
            <tbody>';

if (!empty($motocicletas)) {
    foreach ($motocicletas as $i => $moto) {
        $badge = ($moto['cantidad'] > 0)
            ? '<span class="badge" style="background:#701106; color:#fff;">Disponible: ' . $moto['cantidad'] . '</span>'
            : '<span class="badge" style="background:#050506; color:#fff;">Agotado</span>';
        $html .= '
            <tr>
                <td>' . ($i + 1) . '</td>
                <td>' . htmlspecialchars($moto['marca']) . '</td>
                <td>' . htmlspecialchars($moto['modelo']) . '</td>
                <td>' . htmlspecialchars($moto['cilindrada']) . ' cc</td>
                <td>' . htmlspecialchars($moto['color']) . '</td>
                <td>' . $badge . '</td>
                <td>$' . number_format($moto['precio'], 2) . '</td>
                <td>Bs. ' . number_format($moto['precio'] * 7, 2) . '</td>
                <td>' . date('d/m/Y', strtotime($moto['fecha_ingreso'])) . '</td>
            </tr>';
    }
} else {
    $html .= '
        <tr>
            <td colspan="9" style="text-align:center; color:#A51314;">No hay motocicletas en el inventario.</td>
        </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div style="margin-bottom:15px; font-size:13px;">
        <strong>Filtros aplicados:</strong>
        <ul style="margin:0; padding-left:18px;">
            '.($brandFilter ? "<li>Marca: <b>".htmlspecialchars($brandFilter)."</b></li>" : "").'
            '.($modelFilter ? "<li>Modelo: <b>".htmlspecialchars($modelFilter)."</b></li>" : "").'
            '.($ccFilter ? "<li>Cilindrada: <b>".htmlspecialchars($ccFilter)." cc</b></li>" : "").'
            '.(!$brandFilter && !$modelFilter && !$ccFilter ? "<li>Ninguno (mostrando todo el inventario)</li>" : "").'
        </ul>
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
$filename = 'reporte_catalogo_' . date('Y-m-d_H-i-s') . '.pdf';
$dompdf->stream($filename, array("Attachment" => false));
?>