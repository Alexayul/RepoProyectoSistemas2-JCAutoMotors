<?php
require_once '../models/VentasAdminModel.php';

class VentasAdminController {
    private $model;

    public function __construct($conn) {
        $this->model = new VentasAdminModel($conn);
    }

    public function obtenerDatosUsuario($user_id) {
        return $this->model->obtenerDatosUsuario($user_id);
    }

    public function obtenerClientes() {
        return $this->model->obtenerClientes();
    }

    public function obtenerVentas($filtros = []) {
        // Obtener ventas desde el modelo
        $ventas = $this->model->obtenerVentas();

        // Ordenar las ventas por fecha en orden descendente (más recientes primero)
        usort($ventas, function($a, $b) {
            return strtotime($b['fecha_venta']) - strtotime($a['fecha_venta']);
        });

        // Si no hay filtros, devolver directamente
        if (empty($filtros)) {
            return $ventas;
        }

        // Aplicar filtros
        $ventasFiltradas = [];
        foreach ($ventas as $venta) {
            $cumpleFiltros = true;

            // Filtro por rango de fechas
            if (!empty($filtros['fecha_desde']) && 
                strtotime($venta['fecha_venta']) < strtotime($filtros['fecha_desde'])) {
                $cumpleFiltros = false;
            }

            if (!empty($filtros['fecha_hasta']) && 
                strtotime($venta['fecha_venta']) > strtotime($filtros['fecha_hasta'])) {
                $cumpleFiltros = false;
            }

            // Filtro por estado
            if (!empty($filtros['estado']) && $venta['estado'] !== $filtros['estado']) {
                $cumpleFiltros = false;
            }

            // Filtro por tipo de pago
            if (!empty($filtros['tipo_pago']) && $venta['tipo_pago'] !== $filtros['tipo_pago']) {
                $cumpleFiltros = false;
            }

            // Filtro por empleado
            if (!empty($filtros['empleado']) && $venta['id_empleado'] != $filtros['empleado']) {
                $cumpleFiltros = false;
            }

            if ($cumpleFiltros) {
                $ventasFiltradas[] = $venta;
            }
        }

        return $ventasFiltradas;
    }
    public function obtenerProductosDisponibles() {
        return $this->model->obtenerProductosDisponibles();
    }

    public function registrarVenta($data, $user_id) {
        return $this->model->registrarVenta($data, $user_id);
    }
    public function completarVenta($id_venta) {
    return $this->model->completarVenta($id_venta);
}
}