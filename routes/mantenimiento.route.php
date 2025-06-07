<?php
require_once '../config/conexion.php';
require_once '../controllers/MantenimientoController.php';

header('Content-Type: application/json');

try {
    session_start();

    if (!isset($_SESSION['user'])) {
        throw new Exception('No autenticado: sesión de usuario no encontrada');
    }

    // Obtener conexión
    $conn = new PDO(
        "mysql:host=b4tbtxmwwzuudshpuohy-mysql.services.clever-cloud.com;dbname=b4tbtxmwwzuudshpuohy;charset=utf8mb4",
        'ulbdcz4pcdollulm',
        'LHxsOFWkoDWRo4xFENP9'
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $controller = new MantenimientoController($conn);

    // Obtener ID de empleado desde el usuario en sesión
    $id_usuario = $_SESSION['user']['id'];
    $id_empleado = $controller->getIdEmpleado($id_usuario);

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
        $datos = $_POST;
        $resultado = $controller->crearMantenimiento($datos, $id_empleado);
        echo json_encode($resultado);
        exit;
    }

    // Nueva ruta para verificar mantenimiento gratuito
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'check_mantenimiento_gratuito') {
        $cliente_id = $_GET['cliente_id'] ?? null;
        if (!$cliente_id) {
            echo json_encode([
                'success' => false, 
                'message' => 'Falta cliente_id'
            ]);
            exit;
        }

        // Llamar al método de verificación de mantenimiento gratuito
        $resultado = $controller->checkMantenimientoGratuito($cliente_id);
        echo json_encode($resultado);
        exit;
    }

    // Ruta para obtener clientes (si es necesario)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_clientes') {
        $clientes = $controller->getClientes();
        echo json_encode($clientes);
        exit;
    }

    // Ruta para obtener motocicletas (si es necesario)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_motocicletas') {
        $motocicletas = $controller->getMotocicletas();
        echo json_encode($motocicletas);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_mantenimiento') {
    $mantenimiento_id = $_GET['id'] ?? null;
    if (!$mantenimiento_id) {
        echo json_encode(['success' => false, 'message' => 'ID de mantenimiento no proporcionado']);
        exit;
    }
    
    $resultado = $controller->obtenerDetalleMantenimiento($mantenimiento_id);
    echo json_encode($resultado);
    exit;
}

    throw new Exception('Acción no válida');

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'session_data' => isset($_SESSION) ? $_SESSION : 'No session data'
    ]);
    exit;
}
