<?php
require_once '../models/CreditoModel.php';

class CreditoController {
    private $conn;
    private $model;
    
    public function __construct($conn) {
        $this->conn = $conn;
        require_once '../models/CreditoModel.php';
        $this->model = new CreditoModel($conn);
        // Actualizar pagos atrasados automáticamente al inicializar
        $this->model->actualizarPagosAtrasados();
    }
public function obtenerDatosUsuario($user_id) {
        return $this->model->obtenerDatosUsuario($user_id);
    }
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
            $this->handleGetRequest();
        }
    }

    private function handlePostRequest() {
        if (!isset($_POST['action'])) {
            return;
        }

        try {
            switch ($_POST['action']) {
                case 'programar_pagos':
                    $this->programarPagos();
                    break;
                case 'registrar_pago':
                    $this->registrarPago();
                    break;
                case 'marcar_no_pagado':
                    $this->marcarNoPagado();
                    break;
                case 'actualizar_estado_credito':
                    $this->actualizarEstadoCredito();
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }

    private function handleGetRequest() {
        switch ($_GET['action']) {
            case 'filter':
                $this->aplicarFiltros();
                break;
            case 'clear_filters':
                $this->limpiarFiltros();
                break;
        }
    }

    private function aplicarFiltros() {
        $filtros = [
            'cliente' => filter_input(INPUT_GET, 'cliente', FILTER_SANITIZE_STRING) ?? '',
            'fecha_venta' => filter_input(INPUT_GET, 'fecha_venta', FILTER_SANITIZE_STRING) ?? '',
            'fecha_desde' => filter_input(INPUT_GET, 'fecha_desde', FILTER_SANITIZE_STRING) ?? '',
            'fecha_hasta' => filter_input(INPUT_GET, 'fecha_hasta', FILTER_SANITIZE_STRING) ?? '',
            'saldo' => filter_input(INPUT_GET, 'saldo', FILTER_SANITIZE_STRING) ?? '',
            'atraso' => filter_input(INPUT_GET, 'atraso', FILTER_SANITIZE_STRING) ?? ''
        ];
        
        $_SESSION['filtros_creditos'] = $filtros;
    }

    private function limpiarFiltros() {
        unset($_SESSION['filtros_creditos']);
    }

    private function programarPagos() {
        $idVenta = filter_input(INPUT_POST, 'id_venta', FILTER_VALIDATE_INT);
        $fechas = $_POST['fechas_pago'] ?? [];
        $montos = $_POST['montos_pago'] ?? [];
        $saldoPendiente = filter_input(INPUT_POST, 'saldo_pendiente', FILTER_VALIDATE_FLOAT);

        if (!$idVenta || empty($fechas) || empty($montos)) {
            $_SESSION['error'] = "Datos incompletos para programar pagos";
            return;
        }

        if (count($fechas) !== count($montos)) {
            $_SESSION['error'] = "El número de fechas y montos no coincide";
            return;
        }

        $totalMontos = array_sum($montos);
        if (abs($totalMontos - $saldoPendiente) > 0.01) {
            $_SESSION['error'] = "La suma de los montos (" . number_format($totalMontos, 2) . 
                               ") debe ser igual al saldo pendiente (" . number_format($saldoPendiente, 2) . ")";
            return;
        }

        $success = $this->model->programarPagosPersonalizados($idVenta, $fechas, $montos);
        $_SESSION[$success ? 'success' : 'error'] = $success ? 
            "Pagos programados exitosamente" : "Error al programar los pagos";
    }

    private function registrarPago() {
        $idPago = filter_input(INPUT_POST, 'id_pago', FILTER_VALIDATE_INT);
        $montoPagado = filter_input(INPUT_POST, 'monto_pagado', FILTER_VALIDATE_FLOAT);
        $idVenta = filter_input(INPUT_POST, 'id_venta', FILTER_VALIDATE_INT);

        if (!$idPago || !$idVenta) {
            $_SESSION['error'] = "Datos incompletos para registrar pago";
            return;
        }

        $success = $this->model->registrarPago($idPago, $montoPagado);
        
        if ($success) {
            $this->model->verificarYActualizarEstadoCredito($idVenta);
            $_SESSION['success'] = "Pago registrado exitosamente";
        } else {
            $_SESSION['error'] = "Error al registrar el pago";
        }
    }

    private function marcarNoPagado() {
        $idPago = filter_input(INPUT_POST, 'id_pago', FILTER_VALIDATE_INT);
        $idVenta = filter_input(INPUT_POST, 'id_venta', FILTER_VALIDATE_INT);

        if (!$idPago || !$idVenta) {
            $_SESSION['error'] = "Datos incompletos para marcar como no pagado";
            return;
        }

        $success = $this->model->marcarNoPagado($idPago);
        $_SESSION[$success ? 'success' : 'error'] = $success ? 
            "El pago se ha marcado como no pagado y se calculó la mora correspondiente." : 
            "No se pudo marcar el pago como no pagado.";
    }

    private function actualizarEstadoCredito() {
        $idVenta = filter_input(INPUT_POST, 'id_venta', FILTER_VALIDATE_INT);
        
        if (!$idVenta) {
            $_SESSION['error'] = "ID de venta inválido";
            return;
        }

        $success = $this->model->verificarYActualizarEstadoCredito($idVenta);
        if ($success) {
            $_SESSION['success'] = "Estado del crédito actualizado";
        }
    }

    public function obtenerCreditosDirectos() {
        $filtros = $_SESSION['filtros_creditos'] ?? [];
        return $this->model->obtenerCreditosDirectos($filtros);
    }

    public function obtenerPagosProgramados($idVenta) {
        return $this->model->obtenerPagosProgramados($idVenta);
    }

    public function obtenerTotalesPagos($idVenta) {
        return $this->model->obtenerTotalesPagos($idVenta);
    }

    public function obtenerEstadisticasCredito($idVenta) {
        return $this->model->obtenerEstadisticasCredito($idVenta);
    }

    public function obtenerCreditosFiltrados($filtros) {
        return $this->model->obtenerCreditosFiltrados($filtros);
    }
}
?>