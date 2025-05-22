<?php
require_once __DIR__ . '/../models/ClienteModel.php';

class ClienteController {
    private $model;

    public function __construct($db) {
        $this->model = new ClienteModel($db);
    }

    public function index() {
        return $this->model->getAll();
    }

    public function get($id) {
        return $this->model->getById($id);
    }

    public function store($data) {
        // Procesar imágenes
        $clienteData = $data['cliente'];
        
        // Procesar croquis
        if (isset($_FILES['croquis_domicilio']) && $_FILES['croquis_domicilio']['error'] === UPLOAD_ERR_OK) {
            $clienteData['croquis_domicilio'] = file_get_contents($_FILES['croquis_domicilio']['tmp_name']);
        } else {
            $clienteData['croquis_domicilio'] = null;
        }
        
        // Procesar factura
        if (isset($_FILES['factura_servicio']) && $_FILES['factura_servicio']['error'] === UPLOAD_ERR_OK) {
            $clienteData['factura_servicio'] = file_get_contents($_FILES['factura_servicio']['tmp_name']);
        } else {
            $clienteData['factura_servicio'] = null;
        }
        
        return $this->model->create($data['persona'], $clienteData);
    }

    public function update($id, $data) {
        // Procesar imágenes
        $clienteData = $data['cliente'];
        
        // Procesar croquis (solo si se subió uno nuevo)
        if (isset($_FILES['croquis_domicilio']) && $_FILES['croquis_domicilio']['error'] === UPLOAD_ERR_OK) {
            $clienteData['croquis_domicilio'] = file_get_contents($_FILES['croquis_domicilio']['tmp_name']);
        } else {
            // Mantener el existente si no se subió uno nuevo
            $current = $this->model->getById($id);
            $clienteData['croquis_domicilio'] = $current['croquis_domicilio'] ?? null;
        }
        
        // Procesar factura (solo si se subió una nueva)
        if (isset($_FILES['factura_servicio']) && $_FILES['factura_servicio']['error'] === UPLOAD_ERR_OK) {
            $clienteData['factura_servicio'] = file_get_contents($_FILES['factura_servicio']['tmp_name']);
        } else {
            // Mantener la existente si no se subió una nueva
            $current = $this->model->getById($id);
            $clienteData['factura_servicio'] = $current['factura_servicio'] ?? null;
        }
        
        return $this->model->update($id, $data['persona'], $clienteData);
    }
}