<?php
require_once '../config/conexion.php';
require_once '../helpers/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Configurar opciones de DomPDF
$options = new Options();
$options->set('isHTML5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$options->set('dpi', 150);
$options->set('defaultPaperSize', 'A4');
$options->set('chroot', realpath(''));

// Crear instancia de DomPDF
$dompdf = new Dompdf($options);

// Definir avatar por defecto en base64 (igual que en tu sistema principal)
define('DEFAULT_AVATAR', 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJDNi40NzcgMiAyIDYuNDc3IDIgMTJzNC40NzcgMTAgMTAgMTAgMTAtNC40NzcgMTAtMTBTMTcuNTIzIDIgMTIgMnptMCAyYzQuNDE4IDAgOCAzLjU4MiA4IDhzLTMuNTgyIDgtOCA4LTgtMy41ODItOC04IDMuNTgyLTggOC04eiIvPjxwYXRoIGQ9Ik0xMiAzYy0yLjIxIDAtNCAxLjc5LTQgNHMxLjc5IDQgNCA0IDQtMS43OSA0LTRzLTEuNzktNC00LTR6bTAgN2MtMy4zMTMgMC02IDIuNjg3LTYgNnYxaDEydi0xYzAtMy4zMTMtMi42ODctNi02LTZ6Ii8+PC9zdmc+');

// Obtener lista de empleados
try {
    $stmt = $conn->prepare("SELECT 
        e._id as id, 
        p.nombre, 
        p.apellido, 
        e.cargo, 
        e.salario, 
        e.fecha_contratacion, 
        p.telefono, 
        p.email, 
        e.estado, 
        p.documento_identidad,
        e.foto
    FROM EMPLEADO e 
    JOIN PERSONA p ON e._id = p._id
    ORDER BY p.nombre ASC");
    
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar imágenes de empleados
    foreach ($employees as &$employee) {
        if (!empty($employee['foto'])) {
            if (is_resource($employee['foto'])) {
                $employee['foto'] = stream_get_contents($employee['foto']);
            }
            
            // Determinar el tipo MIME real de la imagen
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($employee['foto']);
            
            $employee['imagen'] = 'data:' . $mime . ';base64,' . base64_encode($employee['foto']);
        } else {
            $employee['imagen'] = DEFAULT_AVATAR;
        }
    }
    unset($employee);

    // Calcular estadísticas
    $activeCount = count(array_filter($employees, fn($emp) => ($emp['estado'] ?? 'Activo') === 'Activo'));
    $inactiveCount = count(array_filter($employees, fn($emp) => ($emp['estado'] ?? '') === 'Despedido'));
    $totalEmployees = count($employees);
    $totalSalary = array_sum(array_column($employees, 'salario'));
    $averageSalary = $totalEmployees > 0 ? $totalSalary / $totalEmployees : 0;

} catch (PDOException $e) {
    die("Error al obtener empleados: " . $e->getMessage());
}

// Iniciar buffer de salida
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Empleados - JC Automotors</title>
    <style>
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.3;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #A51314;
            padding-bottom: 8px;
        }
        
        .header h1 {
            color: #701106;
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 11px;
            margin: 3px 0;
        }
        
        .header .date {
            color: #888;
            font-size: 9px;
            margin-top: 5px;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 8px;
        }
        
        .stat-card {
            border: 1px solid #A51314;
            padding: 8px;
            text-align: center;
            flex: 1;
            background-color: rgba(165, 19, 20, 0.05);
            border-radius: 3px;
        }
        
        .stat-card h3 {
            color: #A51314;
            margin: 0 0 3px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 0;
            font-size: 11px;
            color: #333;
        }
        
        .employees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        
        .employees-table th,
        .employees-table td {
            border: 1px solid #701106;
            padding: 6px 4px;
            text-align: left;
            vertical-align: middle;
        }
        
        .employees-table th {
            background-color: #A51314;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        
        .employees-table tr:nth-child(even) {
            background-color: rgba(165, 19, 20, 0.02);
        }
        
        .employee-photo {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        
        .status-active {
            color: #28a745;
            font-weight: bold;
            font-size: 8px;
        }
        
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
            font-size: 8px;
        }
        
        .footer {
            position: fixed;
            bottom: 0.5cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        /* Ajustes para columnas */
        .col-photo { width: 50px; }
        .col-name { width: 120px; }
        .col-position { width: 80px; }
        .col-document { width: 70px; }
        .col-phone { width: 70px; }
        .col-email { width: 120px; }
        .col-salary { width: 70px; }
        .col-date { width: 70px; }
        .col-status { width: 50px; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .salary-text {
            font-weight: bold;
            color: #2c5530;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>REPORTE DE EMPLEADOS</h1>
        <div class="subtitle">JC AUTOMOTORS - Sistema de Gestión</div>
        <div class="date">Fecha de generación: <?php echo date('d/m/Y H:i:s'); ?></div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <h3><?php echo $totalEmployees; ?></h3>
            <p>Total de Empleados</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $activeCount; ?></h3>
            <p>Empleados Activos</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $inactiveCount; ?></h3>
            <p>Empleados Inactivos</p>
        </div>
        <div class="stat-card">
            <h3>Bs. <?php echo number_format($totalSalary, 2); ?></h3>
            <p>Salario Total Mensual</p>
        </div>
        <div class="stat-card">
            <h3>Bs. <?php echo number_format($averageSalary, 2); ?></h3>
            <p>Salario Promedio</p>
        </div>
    </div>

    <!-- Employees Table -->
    <table class="employees-table">
        <thead>
            <tr>
                <th class="col-photo">Foto</th>
                <th class="col-name">Nombre Completo</th>
                <th class="col-position">Cargo</th>
                <th class="col-document">C.I.</th>
                <th class="col-phone">Teléfono</th>
                <th class="col-email">Email</th>
                <th class="col-salary">Salario</th>
                <th class="col-date">F. Contratación</th>
                <th class="col-status">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $index => $employee): ?>
                <?php if ($index > 0 && $index % 15 == 0): ?>
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <table class="employees-table">
                        <thead>
                            <tr>
                                <th class="col-photo">Foto</th>
                                <th class="col-name">Nombre Completo</th>
                                <th class="col-position">Cargo</th>
                                <th class="col-document">C.I.</th>
                                <th class="col-phone">Teléfono</th>
                                <th class="col-email">Email</th>
                                <th class="col-salary">Salario</th>
                                <th class="col-date">F. Contratación</th>
                                <th class="col-status">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                <?php endif; ?>
                
                <tr>
                    <td class="text-center">
                        <img src="<?php echo htmlspecialchars($employee['imagen']); ?>" 
                             alt="Foto" class="employee-photo">
                    </td>
                    <td><?php echo htmlspecialchars($employee['nombre'] . ' ' . $employee['apellido']); ?></td>
                    <td><?php echo htmlspecialchars($employee['cargo']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($employee['documento_identidad']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($employee['telefono'] ?? 'N/D'); ?></td>
                    <td><?php echo htmlspecialchars($employee['email'] ?? 'N/D'); ?></td>
                    <td class="text-right salary-text">Bs. <?php echo number_format($employee['salario'], 2); ?></td>
                    <td class="text-center"><?php echo date('d/m/Y', strtotime($employee['fecha_contratacion'])); ?></td>
                    <td class="text-center">
                        <span class="<?php 
                            echo (($employee['estado'] ?? 'Activo') == 'Activo') 
                                ? 'status-active' 
                                : 'status-inactive'; 
                        ?>">
                            <?php echo htmlspecialchars($employee['estado'] ?? 'Activo'); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        JC Automotors - Reporte generado automáticamente el <?php echo date('d/m/Y'); ?> | 
        Total de empleados: <?php echo $totalEmployees; ?> | 
        Página <script type="text/php">
            if (isset($pdf)) {
                $font = $fontMetrics->getFont("Arial");
                $pdf->page_text(520, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 8, array(0,0,0));
            }
        </script>
    </div>
</body>
</html>

<?php
// Capturar el contenido HTML
$html = ob_get_clean();

// Cargar HTML en DomPDF
$dompdf->loadHtml($html);

// Configurar papel y orientación
$dompdf->setPaper('A4', 'landscape');

// Renderizar PDF
$dompdf->render();

// Obtener el canvas para agregar numeración de páginas
$canvas = $dompdf->getCanvas();
$canvas->page_text(750, 570, "Página {PAGE_NUM} de {PAGE_COUNT}", 'Arial', 8, array(0.5, 0.5, 0.5));

// Generar nombre de archivo con fecha
$filename = 'reporte_empleados_' . date('Y-m-d_H-i-s') . '.pdf';

// Mostrar PDF en el navegador
$dompdf->stream($filename, array("Attachment" => false));
exit;
?>