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

// Preparar datos
$labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
$ventasData = array_fill(0, 12, 0);
$mantenimientosData = array_fill(0, 12, 0);

foreach ($ventas_mensuales as $venta) {
    $ventasData[$venta['mes'] - 1] = (float)$venta['total'] * 7;
}

// Asegura que mantenimientosData solo tenga valores numéricos
foreach ($mantenimientos_mensuales as $mant) {
    $mantenimientosData[$mant['mes'] - 1] = is_array($mant['total']) 
        ? (float)array_values($mant['total'])[0] 
        : (float)$mant['total'];
}


// Configurar opciones de DomPDF
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

// HTML del PDF
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Dashboard - JC Automotors</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; background: #fff; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #A51314; }
        .header img { max-width: 170px; max-height: 110px; }
        .header h1 { margin: 0; color: #701106; font-size: 22px; }
        .header p { color: #666; margin: 0; font-size: 13px; }
        .stats-row { display: flex; justify-content: center; gap: 30px; margin-bottom: 25px; }
        .stat-card { flex: 0 0 200px; margin: 0; padding: 12px 10px; border: 1px solid #A51314; border-radius: 8px; text-align: center; background-color: #f9f2f2; box-shadow: 0 2px 6px rgba(0,0,0,0.04);}
        .stat-card h3 { margin: 0 0 8px 0; color: #A51314; font-size: 16px; }
        .stat-card p { margin: 0; font-weight: bold; color: #333; font-size: 15px; }
        .chart-section { margin: 0 auto 30px auto; text-align: center; width: 90%; max-width: 900px; }
        .chart-section h2 { color: #A51314; font-size: 18px; margin-bottom: 8px; }
        .chart-img { display: block; margin: 0 auto 10px auto; width: 700px; height: 260px; max-width: 100%; background: #fff; border: 1px solid #eee; border-radius: 8px; }
        .footer { text-align: right; font-size: 11px; color: #888; margin-top: 30px; }
        .progress-bar { background: #198754; height: 18px; border-radius: 6px; }
        .progress-container { background: #e9ecef; border-radius: 6px; height: 18px; width: 100%; }
        .progress-label { font-size: 12px; margin-bottom: 2px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-primary { background: #a51314; color: #fff; }
        .badge-success { background: #198754; color: #fff; }
        .badge-secondary { background: #6c757d; color: #fff; }
        .badge-dark { background: #212529; color: #fff; }
        .badge-light { background: #f8f9fa; color: #333; border: 1px solid #ccc; }
        ul { margin: 0; padding-left: 18px; }
    </style>
</head>
<body>
    <div class="header">
        <div style="flex:0 0 auto;">
            <img src="' . $logoData . '" alt="JC Automotors" style="max-width:170px; max-height:110px;">
        </div>
        <div style="flex:1 1 auto; text-align:left; padding-left:18px;">
            <h1 style="margin:0; color:#701106; font-size:22px;">Reporte de Dashboard - JC Automotors</h1>
            <p style="color:#666; margin:0; font-size:13px;">Generado el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <h3>Motocicletas</h3>
            <p>' . $stats['motocicletas'] . '</p>
            <span class="badge badge-primary">Disponibles</span>
        </div>
        <div class="stat-card">
            <h3>Ventas</h3>
            <p>' . $stats['ventas_count'] . '</p>
            <span class="badge badge-dark">Este mes</span>
        </div>
        <div class="stat-card">
            <h3>Accesorios</h3>
            <p>' . $stats['accesorios'] . '</p>
            <span class="badge badge-light">En inventario</span>
        </div>
        <div class="stat-card">
            <h3>Mantenimientos</h3>
            <p>' . $stats['mantenimientos_count'] . '</p>
            <span class="badge badge-secondary">Este mes</span>
        </div>
        <div class="stat-card">
            <h3>Ingresos</h3>
            <p>Bs. ' . number_format($stats['ventas_total'] * 7, 2) . '</p>
            <span class="badge badge-primary">Este mes</span>
        </div>
    </div>

    <div class="chart-section">
        <h2>Rendimiento Mensual</h2>
        <img class="chart-img" src="data:image/svg+xml;base64,' . base64_encode(generarGraficoLineal($labels, $ventasData, $mantenimientosData)) . '">
    </div>

    <div class="chart-section">
        <h2>Distribución de Ventas</h2>
        <img class="chart-img" src="data:image/svg+xml;base64,' . base64_encode(generarGraficoPastel()) . '">
    </div>

    <div class="chart-section">
        <h2>Ventas Mensuales</h2>
        <img class="chart-img" src="data:image/svg+xml;base64,' . base64_encode(generarGraficoBarrasSimples($labels, $ventasData, "#a51314")) . '">
    </div>

    <div class="chart-section">
        <h2>Mantenimientos Mensuales</h2>
        <img class="chart-img" src="data:image/svg+xml;base64,' . base64_encode(generarGraficoLinealSimple($labels, $mantenimientosData, "#1e3a8a")) . '">
    </div>

    <div class="chart-section">
        <h2>Modelos Más Vendidos</h2>
        <img class="chart-img" src="data:image/svg+xml;base64,' . base64_encode(generarGraficoBarras($top_modelos)) . '">
        <div style="margin-top:10px; text-align:left; font-size:13px;">
            <strong>Resumen:</strong>
            <ul>';
foreach ($top_modelos as $modelo) {
    $cantidad = is_array($modelo['cantidad']) ? (int)array_values($modelo['cantidad'])[0] : (int)$modelo['cantidad'];
    $html .= '<li>' . htmlspecialchars(($modelo['marca'] ?? '') . ' ' . $modelo['modelo']) . ': <strong>' . $cantidad . '</strong> ventas</li>';
}
$html .= '
            </ul>
        </div>
    </div>

    <div class="chart-section">
        <h2>Progreso de Metas Mensuales</h2>
        <img class="chart-img" src="data:image/svg+xml;base64,' . base64_encode(generarGraficoRadar()) . '">
        <div style="margin-top:10px; text-align:left; font-size:13px;">
            <strong>Resumen:</strong>
            <ul>
                <li>Ventas: 75%</li>
                <li>Servicios: 60%</li>
                <li>Clientes nuevos: 80%</li>
                <li>Satisfacción: 90%</li>
                <li>Eficiencia: 70%</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        JC Automotors &copy; ' . date('Y') . '
    </div>
</body>
</html>';

// --- FUNCIONES DE GRÁFICOS (las mismas que ya tienes, más las nuevas para barras simples y radar) ---

// Función para generar gráfico de líneas
function generarGraficoLineal($labels, $ventasData, $mantenimientosData) {
    $width = 700;
    $height = 260;
    $padding = 45;

    $svg = "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'>";
    $svg .= "<rect width='100%' height='100%' fill='#fff'/>";

    // Ejes
    $svg .= "<line x1='$padding' y1='".($height-$padding)."' x2='".($width-$padding)."' y2='".($height-$padding)."' stroke='#333' stroke-width='2'/>";
    $svg .= "<line x1='$padding' y1='$padding' x2='$padding' y2='".($height-$padding)."' stroke='#333' stroke-width='2'/>";

    // Máximo valor
    $maxVentas = max($ventasData);
    $maxMantenimientos = max($mantenimientosData);
    $maxValue = max($maxVentas, $maxMantenimientos, 1);

    // Líneas y puntos
    $svg .= generarLinePath($labels, $ventasData, $width, $height, $padding, $maxValue, '#A51314', 'Ventas');
    $svg .= generarLinePath($labels, $mantenimientosData, $width, $height, $padding, $maxValue, '#1e3a8a', 'Mantenimientos');

    // Etiquetas X
    foreach ($labels as $i => $label) {
        $x = $padding + ($i / (count($labels) - 1)) * ($width - 2 * $padding);
        $svg .= "<text x='$x' y='".($height-$padding+18)."' font-size='12' text-anchor='middle'>$label</text>";
    }

    // Etiquetas Y
    for ($i = 0; $i <= 5; $i++) {
        $yVal = round($maxValue * (1 - $i/5));
        $y = $padding + ($i/5) * ($height - 2*$padding);
        $svg .= "<text x='".($padding-10)."' y='".($y+5)."' font-size='11' text-anchor='end'>$yVal</text>";
        $svg .= "<line x1='$padding' y1='$y' x2='".($width-$padding)."' y2='$y' stroke='#eee' stroke-width='1'/>";
    }

    // Leyenda
    $svg .= "<rect x='".($width-$padding-120)."' y='".($padding+5)."' width='12' height='12' fill='#A51314'/>";
    $svg .= "<text x='".($width-$padding-100)."' y='".($padding+15)."' font-size='12'>Ventas</text>";
    $svg .= "<rect x='".($width-$padding-120)."' y='".($padding+25)."' width='12' height='12' fill='#1e3a8a'/>";
    $svg .= "<text x='".($width-$padding-100)."' y='".($padding+35)."' font-size='12'>Mantenimientos</text>";

    $svg .= "</svg>";
    return $svg;
}

function generarLinePath($labels, $data, $width, $height, $padding, $maxValue, $color, $label) {
    $points = [];
    $path = "M";
    foreach ($data as $index => $value) {
        $x = $padding + ($index / (count($labels) - 1)) * ($width - 2 * $padding);
        $y = $height - $padding - ($value / $maxValue) * ($height - 2 * $padding);
        $points[] = [$x, $y];
        $path .= "$x $y ";
    }
    $svg = "<path d='$path' fill='none' stroke='$color' stroke-width='3'/>";
    // Puntos
    foreach ($points as [$x, $y]) {
        $svg .= "<circle cx='$x' cy='$y' r='4' fill='$color' stroke='#fff' stroke-width='1'/>";
    }
    return $svg;
}

function generarGraficoPastel() {
    $width = 700;
    $height = 260;
    $centerX = $width / 2;
    $centerY = $height / 2;
    $radius = 90;

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

        // Etiqueta
        $midAngle = $startAngle + $angle / 2;
        $labelX = $centerX + ($radius + 25) * cos(deg2rad($midAngle));
        $labelY = $centerY + ($radius + 25) * sin(deg2rad($midAngle));
        $svg .= "<text x='$labelX' y='$labelY' font-size='13' text-anchor='middle' fill='#333'>{$slice['label']}</text>";

        $startAngle = $endAngle;
    }

    $svg .= "</svg>";
    return $svg;
}

function generarGraficoBarras($top_modelos) {
    $width = 700;
    $height = 260;
    $padding = 60;
    $barWidth = 40;
    $gap = 30;
    $colores = ['#A51314', '#1e3a8a', '#ffc107', '#198754', '#9d4edd', '#ff6f61', '#00b4d8', '#ffb703', '#8d99ae', '#e63946'];

    $svg = "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'>";
    $valores = array_map(function($m) {
        $cantidad = $m['cantidad'];
        if (is_array($cantidad)) {
            $cantidad = array_values($cantidad)[0];
            if (is_array($cantidad)) $cantidad = 0;
        }
        return (int)$cantidad;
    }, $top_modelos);

    $maxCantidad = max(1, ...$valores);
    $n = count($top_modelos);

    if ($n === 0) {
        $svg .= "<text x='".($width/2)."' y='".($height/2)."' text-anchor='middle' font-size='18' fill='#A51314'>No hay datos suficientes</text>";
    } else {
        foreach ($top_modelos as $index => $modelo) {
            $cantidad = $modelo['cantidad'];
            if (is_array($cantidad)) {
                $cantidad = array_values($cantidad)[0];
                if (is_array($cantidad)) $cantidad = 0;
            }
            $cantidad = (int)$cantidad;
            $x = $padding + $index * ($barWidth + $gap);
            $barHeight = ($maxCantidad > 0) ? ($cantidad / $maxCantidad) * ($height - 2 * $padding) : 0;
            $y = $height - $padding - $barHeight;
            $color = $colores[$index % count($colores)];

            // Barra
            $svg .= "<rect x='$x' y='$y' width='$barWidth' height='$barHeight' fill='$color' rx='6' />";
            // Etiqueta cantidad
            $svg .= "<text x='" . ($x + $barWidth/2) . "' y='" . ($y - 8) . "' text-anchor='middle' font-size='12'>{$cantidad}</text>";
            // Etiqueta modelo
            $svg .= "<text x='" . ($x + $barWidth/2) . "' y='" . ($height - $padding + 18) . "' text-anchor='middle' font-size='11' fill='#333'>" . htmlspecialchars($modelo['modelo']) . "</text>";
        }

        // Eje Y
        $svg .= "<line x1='$padding' y1='$padding' x2='$padding' y2='".($height-$padding)."' stroke='#333' stroke-width='2' />";
        // Eje X
        $svg .= "<line x1='$padding' y1='".($height-$padding)."' x2='".($width-$padding)."' y2='".($height-$padding)."' stroke='#333' stroke-width='2' />";

        // Etiquetas eje Y
        for ($i = 0; $i <= 5; $i++) {
            $yVal = round($maxCantidad * (1 - $i/5));
            $yPos = $padding + ($i/5) * ($height - 2*$padding);
            $svg .= "<text x='".($padding-10)."' y='".($yPos+5)."' font-size='11' text-anchor='end'>$yVal</text>";
            $svg .= "<line x1='$padding' y1='$yPos' x2='".($width-$padding)."' y2='$yPos' stroke='#eee' stroke-width='1'/>";
        }

        // Leyenda
        $legendY = $padding - 35;
        foreach ($top_modelos as $index => $modelo) {
            $color = $colores[$index % count($colores)];
            $legendX = $padding + $index * 120;
            $svg .= "<rect x='$legendX' y='$legendY' width='12' height='12' fill='$color'/>";
            $svg .= "<text x='".($legendX+18)."' y='".($legendY+11)."' font-size='12'>" . htmlspecialchars($modelo['modelo']) . "</text>";
        }
    }

    $svg .= "</svg>";
    return $svg;
}

function generarGraficoBarrasSimples($labels, $data, $color) {
    $width = 700; $height = 260; $padding = 60; $barWidth = 30; $gap = 20;

    // Ensure all data values are numeric
    $numericData = array_map(function($v) {
        if (is_array($v)) {
            return (float)array_values($v)[0]; // Get first value if it's an array
        }
        return (float)$v;
    }, $data);

    $svg = "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'>";
    $maxValue = max($numericData) ?: 1; // Handle case where all values might be 0
    
    foreach ($numericData as $i => $valor) {
        $x = $padding + $i * ($barWidth + $gap);
        $barHeight = ($valor / $maxValue) * ($height - 2 * $padding);
        $y = $height - $padding - $barHeight;
        $svg .= "<rect x='$x' y='$y' width='$barWidth' height='$barHeight' fill='$color' rx='6'/>";
        $svg .= "<text x='" . ($x + $barWidth/2) . "' y='" . ($y - 8) . "' text-anchor='middle' font-size='12'>{$valor}</text>";
        $svg .= "<text x='" . ($x + $barWidth/2) . "' y='" . ($height - $padding + 18) . "' text-anchor='middle' font-size='11' fill='#333'>{$labels[$i]}</text>";
    }
    
    // Ejes
    $svg .= "<line x1='$padding' y1='$padding' x2='$padding' y2='".($height-$padding)."' stroke='#333' stroke-width='2'/>";
    $svg .= "<line x1='$padding' y1='".($height-$padding)."' x2='".($width-$padding)."' y2='".($height-$padding)."' stroke='#333' stroke-width='2'/>";
    $svg .= "</svg>";
    return $svg;
}

function generarGraficoLinealSimple($labels, $data, $color) {
    $width = 700; 
    $height = 260; 
    $padding = 45;
    
    // Ensure all data values are numeric
    $numericData = array_map(function($v) {
        if (is_array($v)) {
            return (float)array_values($v)[0]; // Get first value if it's an array
        }
        return (float)$v;
    }, $data);

    $svg = "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'>";
    $svg .= "<rect width='100%' height='100%' fill='#fff'/>";
    
    $maxValue = max($numericData) ?: 1; // Handle case where all values might be 0
    
    $points = [];
    $path = "M";
    
    foreach ($numericData as $i => $valor) {
        $x = $padding + ($i / (count($labels) - 1)) * ($width - 2 * $padding);
        $y = $height - $padding - ($valor / $maxValue) * ($height - 2 * $padding);
        $points[] = [$x, $y];
        $path .= "$x $y ";
    }
    
    $svg .= "<path d='$path' fill='none' stroke='$color' stroke-width='3'/>";
    
    foreach ($points as [$x, $y]) {
        $svg .= "<circle cx='$x' cy='$y' r='4' fill='$color' stroke='#fff' stroke-width='1'/>";
    }
    
    foreach ($labels as $i => $label) {
        $x = $padding + ($i / (count($labels) - 1)) * ($width - 2 * $padding);
        $svg .= "<text x='$x' y='".($height-$padding+18)."' font-size='12' text-anchor='middle'>$label</text>";
    }
    
    $svg .= "</svg>";
    return $svg;
}

function generarGraficoRadar() {
    // Radar simple con valores fijos (puedes adaptar a tus metas reales)
    $labels = ['Ventas', 'Servicios', 'Clientes nuevos', 'Satisfacción', 'Eficiencia'];
    $meta = [100, 100, 100, 100, 100];
    $actual = [75, 60, 80, 90, 70];
    $cx = 350; $cy = 130; $r = 90;
    $svg = "<svg width='700' height='260' xmlns='http://www.w3.org/2000/svg'>";
    // Ejes y líneas
    for ($i = 0; $i < 5; $i++) {
        $angle = deg2rad(72 * $i - 90);
        $x = $cx + $r * cos($angle);
        $y = $cy + $r * sin($angle);
        $svg .= "<line x1='$cx' y1='$cy' x2='$x' y2='$y' stroke='#ccc'/>";
    }
    // Polígonos meta y actual
    $svg .= "<polygon points='";
    for ($i = 0; $i < 5; $i++) {
        $angle = deg2rad(72 * $i - 90);
        $x = $cx + $r * cos($angle);
        $y = $cy + $r * sin($angle);
        $svg .= "$x,$y ";
    }
    $svg .= "' fill='rgba(165,19,20,0.1)' stroke='#a51314' stroke-width='2'/>";
    $svg .= "<polygon points='";
    for ($i = 0; $i < 5; $i++) {
        $angle = deg2rad(72 * $i - 90);
        $x = $cx + $r * $actual[$i]/100 * cos($angle);
        $y = $cy + $r * $actual[$i]/100 * sin($angle);
        $svg .= "$x,$y ";
    }
    $svg .= "' fill='rgba(30,58,138,0.3)' stroke='#1e3a8a' stroke-width='2'/>";
    // Etiquetas
    for ($i = 0; $i < 5; $i++) {
        $angle = deg2rad(72 * $i - 90);
        $x = $cx + ($r+20) * cos($angle);
        $y = $cy + ($r+20) * sin($angle) + 5;
        $svg .= "<text x='$x' y='$y' font-size='13' text-anchor='middle' fill='#333'>{$labels[$i]}</text>";
    }
    $svg .= "</svg>";
    return $svg;
}

// Cargar HTML en DomPDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$filename = 'reporte_dashboard_' . date('Y-m-d_H-i-s') . '.pdf';
$dompdf->stream($filename, array("Attachment" => false));
?>