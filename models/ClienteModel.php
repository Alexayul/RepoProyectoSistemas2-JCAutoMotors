<?php
/**
 * Modelo para la gestión de clientes
 */
class ClienteModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getDb() {
        return $this->db;
    }

    // Obtener todos los clientes (uniendo PERSONA y CLIENTE)
    public function getAll() {
        $sql = "SELECT p._id, p.nombre, p.apellido, p.telefono, p.email, p.documento_identidad, 
                       c.croquis_domicilio, c.factura_servicio
                FROM PERSONA p
                INNER JOIN CLIENTE c ON p._id = c._id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un cliente por ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT p._id, p.nombre, p.apellido, p.telefono, p.email, p.documento_identidad, c.croquis_domicilio, c.factura_servicio
                                    FROM PERSONA p
                                    INNER JOIN CLIENTE c ON p._id = c._id
                                    WHERE p._id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear nuevo cliente
    public function create($persona, $cliente) {
        // Insertar en PERSONA
        $stmt = $this->db->prepare("INSERT INTO PERSONA (nombre, apellido, telefono, email, documento_identidad) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $persona['nombre'],
            $persona['apellido'],
            $persona['telefono'],
            $persona['email'],
            $persona['documento_identidad']
        ]);
        $persona_id = $this->db->lastInsertId();

        // Insertar en CLIENTE
        $stmt2 = $this->db->prepare("INSERT INTO CLIENTE (_id, croquis_domicilio, factura_servicio, id_rol) VALUES (?, ?, ?, ?)");
        $stmt2->execute([
            $persona_id,
            $cliente['croquis_domicilio'],
            $cliente['factura_servicio'],
            $cliente['id_rol']
        ]);

        return $persona_id;
    }

    // Editar cliente
    public function update($id, $persona, $cliente) {
        $stmt = $this->db->prepare("UPDATE PERSONA SET nombre=?, apellido=?, telefono=?, email=?, documento_identidad=? WHERE _id=?");
        $stmt->execute([
            $persona['nombre'],
            $persona['apellido'],
            $persona['telefono'],
            $persona['email'],
            $persona['documento_identidad'],
            $id
        ]);

        $stmt2 = $this->db->prepare("UPDATE CLIENTE SET croquis_domicilio=?, factura_servicio=? WHERE _id=?");
        $stmt2->execute([
            $cliente['croquis_domicilio'],
            $cliente['factura_servicio'],
            $id
        ]);
    }

    // Método requerido por otros módulos, NO BORRAR NI MODIFICAR
    public function getAllClientes() {
        $stmtClientes = $this->db->query("CALL sp_obtener_clientes()");
        $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
        $stmtClientes->closeCursor();
        return $clientes;
    }

    // Verificar si el documento de identidad ya existe
    public function existeDocumento($documento, $excluir_id = null) {
        $sql = "SELECT COUNT(*) FROM PERSONA WHERE documento_identidad = ?";
        $params = [$documento];
        if ($excluir_id) {
            $sql .= " AND _id != ?";
            $params[] = $excluir_id;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Verificar si el email ya existe
    public function existeEmail($email, $excluir_id = null) {
        $sql = "SELECT COUNT(*) FROM PERSONA WHERE email = ?";
        $params = [$email];
        if ($excluir_id) {
            $sql .= " AND _id != ?";
            $params[] = $excluir_id;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}