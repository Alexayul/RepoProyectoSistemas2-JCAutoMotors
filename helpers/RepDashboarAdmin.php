<?php
date_default_timezone_set('America/La_Paz');

require_once '../config/conexion.php';
require_once '../controllers/AdminController.php';
require_once '../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Iniciar sesión
session_start();

// Iniciar controlador
$adminController = new AdminController($conn);

// Obtener datos para los gráficos
$ventas_mensuales = $adminController->getVentasMensuales();
$mantenimientos_mensuales = $adminController->getMantenimientosMensuales();
$top_modelos = $adminController->getTopModelos();
$stats = $adminController->getDashboardStats();

// Configurar opciones de DomPDF
$options = new Options();
$options->set('isHTML5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

// Crear instancia de DomPDF
$dompdf = new Dompdf($options);

// Preparar datos para los gráficos
$labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
$ventasData = array_fill(0, 12, 0);
$mantenimientosData = array_fill(0, 12, 0);

foreach ($ventas_mensuales as $venta) {
    $ventasData[$venta['mes']-1] = $venta['total'] * 7;
}

foreach ($mantenimientos_mensuales as $mant) {
    $mantenimientosData[$mant['mes']-1] = $mant['total'];
}

// Generar HTML para el PDF con gráficos
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Gráficos - JC Automotors</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #A51314;
            padding-bottom: 10px;
        }
        .chart-container {
            width: 100%;
            height: 300px;
            margin-bottom: 20px;
        }
        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            margin: 0 10px;
            padding: 10px;
            border: 1px solid #A51314;
            border-radius: 5px;
            text-align: center;
            background-color: rgba(165, 19, 20, 0.1);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #A51314;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Concesionaria JC Automotors" style="max-width: 150px;">
        <h1>Reporte de Gráficos - JC Automotors</h1>
        <p style="color: #666; margin: 0; font-size: 13px;">Generado el ' . date('d/m/Y H:i:s') . '</p>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Ventas Totales</h3>
            <p>' . $stats['ventas_count'] . '</p>
        </div>
        <div class="stat-card">
            <h3>Ingresos Mensuales</h3>
            <p>Bs. ' . number_format($stats['ventas_total'] * 7, 2) . '</p>
        </div>
        <div class="stat-card">
            <h3>Mantenimientos</h3>
            <p>' . $stats['mantenimientos_count'] . '</p>
        </div>
    </div>

    <div class="chart-container">
        <h2>Rendimiento Mensual</h2>
        <img src="data:image/svg+xml;base64,' . base64_encode(generarGraficoLineal($labels, $ventasData, $mantenimientosData)) . '" style="width:100%; height:300px;">
    </div>

    <div class="chart-container">
        <h2>Distribución de Ventas</h2>
        <img src="data:image/svg+xml;base64,' . base64_encode(generarGraficoPastel()) . '" style="width:100%; height:300px;">
    </div>

    <div class="chart-container">
        <h2>Modelos Más Vendidos</h2>
        <img src="data:image/svg+xml;base64,' . base64_encode(generarGraficoBarras($top_modelos)) . '" style="width:100%; height:300px;">
    </div>
</body>
</html>';

// Función para generar gráfico de líneas
function generarGraficoLineal($labels, $ventasData, $mantenimientosData) {
    $width = 800;
    $height = 400;
    $padding = 40;

    $svg = "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'>";
    
    // Fondo
    $svg .= "<rect width='100%' height='100%' fill='#f4f4f4'/>";
    
    // Ejes
    $svg .= "<line x1='$padding' y1='$height-$padding' x2='$width-$padding' y2='$height-$padding' stroke='black' stroke-width='2'/>";
    $svg .= "<line x1='$padding' y1='$padding' x2='$padding' y2='$height-$padding' stroke='black' stroke-width='2'/>";
    
    // Máximo valor
    $maxVentas = max($ventasData);
    $maxMantenimientos = max($mantenimientosData);
    $maxValue = max($maxVentas, $maxMantenimientos);

    // Línea de ventas
    $pointsVentas = generarPuntos($labels, $ventasData, $width, $height, $padding, $maxValue, '#A51314');
    $svg .= $pointsVentas['path'];
    
    // Línea de mantenimientos
    $pointsMantenimientos = generarPuntos($labels, $mantenimientosData, $width, $height, $padding, $maxValue, '#1e3a8a');
    $svg .= $pointsMantenimientos['path'];

    $svg .= "</svg>";
    return $svg;
}

function generarPuntos($labels, $data, $width, $height, $padding, $maxValue, $color) {
    $points = [];
    $path = "<path d='M";
    
    foreach ($data as $index => $value) {
        $x = $padding + ($index / (count($labels) - 1)) * ($width - 2 * $padding);
        $y = $height - $padding - ($value / $maxValue) * ($height - 2 * $padding);
        $points[] = "$x,$y";
        $path .= "$x $y ";
    }
    
    $path .= "' fill='none' stroke='$color' stroke-width='3'/>";
    
    return ['path' => $path, 'points' => $points];
}

function generarGraficoPastel() {
    $width = 400;
    $height = 400;
    $centerX = $width / 2;
    $centerY = $height / 2;
    $radius = 150;

    $svg = "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'>";
    
    $data = [
        ['label' => 'Motocicletas', 'value' => 65, 'color' => '#A51314'],
        ['label' => 'Accesorios', 'value' => 15, 'color' => '#ffc107'],
        ['label' => 'Servicios', 'value' => 12, 'color' => '#1e3a8a'],
        ['label' => 'Repuestos', 'value' => 8, 'color' => '#9d4edd']
    ];

    $total = array_sum(array_column($data, 'value'));
    $startAngle = 0;

    foreach ($data as $slice) {
        $angle = ($slice['value'] / $total) * 360;
        $endAngle = $startAngle + $angle;

        $x1 = $centerX + $radius * cos(deg2rad($startAngle));
        $y1 = $centerY + $radius * sin(deg2rad($startAngle));
        $x2 = $centerX + $radius * cos(deg2rad($endAngle));
        $y2 = $centerY + $radius * sin(deg2rad($endAngle));

        $largeArc = $angle > 180 ? 1 : 0;

        $path = "M$centerX,$centerY L$x1,$y1 A$radius,$radius 0 $largeArc,1 $x2,$y2 Z";
        $svg .= "<path d='$path' fill='{$slice['color']}' stroke='white' stroke-width='2'/>";

        $startAngle = $endAngle;
    }

    $svg .= "</svg>";
    return $svg;
}

function generarGraficoBarras($top_modelos) {
    $width = 600;
    $height = 300;
    $padding = 40;
    $barWidth = 50;

    $svg = "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'>";
    
    $maxCantidad = max(array_column($top_modelos, 'cantidad'));

    foreach ($top_modelos as $index => $modelo) {
        $x = $padding + $index * ($barWidth + 20);
        $barHeight = ($modelo['cantidad'] / $maxCantidad) * ($height - 2 * $padding);
        $y = $height - $padding - $barHeight;

        $svg .= "<rect x='$x' y='$y' width='$barWidth' height='$barHeight' fill='#A51314' />";
        $svg .= "<text x='" . ($x + $barWidth/2) . "' y='" . ($y - 5) . "' text-anchor='middle' font-size='12'>{$modelo['cantidad']}</text>";
    }

    $svg .= "</svg>";
    return $svg;
}

// Cargar HTML en DomPDF
$dompdf->loadHtml($html);

// Configurar papel y orientación
$dompdf->setPaper('A4', 'landscape');

// Renderizar PDF
$dompdf->render();

// Generar nombre de archivo con fecha
$filename = 'reporte_graficos_' . date('Y-m-d_H-i-s') . '.pdf';

// Mostrar PDF en el navegador
$dompdf->stream($filename, array("Attachment" => false));
?>
