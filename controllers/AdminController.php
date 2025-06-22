<?php
$DEFAULT_AVATAR = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJDNi40NzcgMiAyIDYuNDc3IDIgMTJzNC40NzcgMTAgMTAgMTAgMTAtNC40NzcgMTAtMTBTMTcuNTIzIDIgMTIgMnptMCAyYzQuNDE4IDAgOCAzLjU4MiA4IDhzLTMuNTgyIDgtOCA4LTgtMy41ODItOC04IDMuNTgyLTggOC04eiIvPjxwYXRoIGQ9Ik0xMiAzYy0yLjIxIDAtNCAxLjc5LTQgNHMxLjc5IDQgNCA0IDQtMS43OSA0LTRzLTEuNzktNC00LTR6bTAgN2MtMy4zMTMgMC02IDIuNjg3LTYgNnYxaDEydi0xYzAtMy4zMTMtMi42ODctNi02LTZ6Ii8+PC9zdmc+';

require_once '../config/conexion.php';
require_once __DIR__ . '/../models/AdminModel.php';

class AdminController {
    private $model;

    public function __construct($conexion) {
        $this->model = new AdminModel($conexion);
    }

    public function getDashboardStats() {
        return $this->model->getDashboardStats();
    }

    public function getVentasMensuales() {
        return $this->model->getVentasMensuales();
    }

    public function getMantenimientosMensuales() {
        return $this->model->getMantenimientosMensuales();
    }

    public function getTopModelos() {
        return $this->model->getTopModelos();
    }

    public function getUserData($user_id) {
        return $this->model->getUserData($user_id);
    }
    function limpiarData($data) {
        return array_map(function($v) {
            if (is_array($v)) {
                $v = array_values($v)[0];
                if (is_array($v)) $v = 0;
            }
            return (float)$v;
        }, $data);
    }

}
