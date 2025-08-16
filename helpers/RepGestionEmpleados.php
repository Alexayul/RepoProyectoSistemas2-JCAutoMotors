<?php
date_default_timezone_set('America/La_Paz');

require_once '../config/conexion.php';
require_once '../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

// Obtener empleados y estadísticas
try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT e._id as id, p.nombre, p.apellido, e.cargo, e.salario, 
        e.fecha_contratacion, p.telefono, p.email, e.estado, p.documento_identidad
        FROM EMPLEADO e JOIN PERSONA p ON e._id = p._id
        ORDER BY p.nombre ASC");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = count($employees);
    $activos = 0;
    $inactivos = 0;
    foreach ($employees as $emp) {
        if ($emp['estado'] == 'Despedido') $inactivos++;
        else $activos++;
    }
} catch (PDOException $e) {
    $employees = [];
    $total = $activos = $inactivos = 0;
}

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
    <title>Reporte de Empleados - JC Automotors</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; background: #fff; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #A51314; }
        .header img { max-width: 170px; max-height: 110px; }
        .header h1 { margin: 0; color: #701106; font-size: 22px; }
        .header p { color: #666; margin: 0; font-size: 13px; }
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
        .badge-activo { background: #198754; color: #fff; }
        .badge-inactivo { background: #701106; color: #fff; }
        .footer { text-align: right; font-size: 11px; color: #888; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <div style="flex:0 0 auto;">
            <img src="' . $logoData . '" alt="JC Automotors" style="max-width:170px; max-height:110px;">
        </div>
        <div style="flex:1 1 auto; text-align:left; padding-left:18px;">
            <h1 style="margin:0; color:#701106; font-size:22px;">Reporte de Gestión de Empleados - JC Automotors</h1>
            <p style="color:#666; margin:0; font-size:13px;">Generado el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Total Empleados</h3>
            <p>' . $total . '</p>
        </div>
        <div class="stat-card">
            <h3>Activos</h3>
            <p>' . $activos . '</p>
        </div>
        <div class="stat-card">
            <h3>Inactivos</h3>
            <p>' . $inactivos . '</p>
        </div>
    </div>

    <div class="table-section">
        <h2 style="color:#A51314; font-size:17px; margin-bottom:8px;">Lista de Empleados</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cargo</th>
                    <th>Salario</th>
                    <th>Fecha Contratación</th>
                    <th>CI</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';

if (!empty($employees)) {
    foreach ($employees as $i => $emp) {
        $badge = ($emp['estado'] == 'Despedido')
            ? '<span class="badge badge-inactivo">Inactivo</span>'
            : '<span class="badge badge-activo">Activo</span>';
        $html .= '
            <tr>
                <td>' . ($i + 1) . '</td>
                <td>' . htmlspecialchars($emp['nombre']) . '</td>
                <td>' . htmlspecialchars($emp['apellido']) . '</td>
                <td>' . htmlspecialchars($emp['cargo']) . '</td>
                <td>$' . number_format($emp['salario'], 2) . '</td>
                <td>' . date('d/m/Y', strtotime($emp['fecha_contratacion'])) . '</td>
                <td>' . htmlspecialchars($emp['documento_identidad']) . '</td>
                <td>' . htmlspecialchars($emp['telefono']) . '</td>
                <td>' . htmlspecialchars($emp['email']) . '</td>
                <td>' . $badge . '</td>
            </tr>';
    }
} else {
    $html .= '
        <tr>
            <td colspan="10" style="text-align:center; color:#A51314;">No hay empleados registrados.</td>
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
$filename = 'reporte_empleados_' . date('Y-m-d_H-i-s') . '.pdf';
$dompdf->stream($filename, array("Attachment" => false));
?>