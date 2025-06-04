<?php
class CatalogoModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }
public function obtenerDatosUsuario($user_id) {
        $stmt = $this->conn->prepare("SELECT p.nombre, p.apellido, e.foto, e.cargo
                                      FROM PERSONA p LEFT JOIN EMPLEADO e ON p._id = e._id
                                      WHERE p._id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Procesar la imagen si existe
        if (!empty($userData['foto'])) {
            $userData['foto'] = 'data:image/jpeg;base64,' . base64_encode($userData['foto']);
        } else {
            $userData['foto'] = 'https://cdn-icons-png.flaticon.com/512/10307/10307911.png';
        }

        return $userData;
    }
    // Métodos para Modelos de Moto
    public function obtenerModeloPorId($id) {
        $query = "SELECT * FROM MODELO_MOTO WHERE _id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarModelo($marca, $modelo, $cilindrada) {
        $query = "SELECT * FROM MODELO_MOTO 
                  WHERE marca = :marca 
                  AND modelo = :modelo 
                  AND cilindrada = :cilindrada";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':marca', $marca);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':cilindrada', $cilindrada);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearModelo($marca, $modelo, $cilindrada, $imagen = null) {
        $query = "INSERT INTO MODELO_MOTO (marca, modelo, cilindrada, imagen) 
                  VALUES (:marca, :modelo, :cilindrada, :imagen)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':marca', $marca);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':cilindrada', $cilindrada);
        $stmt->bindParam(':imagen', $imagen, PDO::PARAM_LOB);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function actualizarImagenModelo($id, $imagen) {
        $query = "UPDATE MODELO_MOTO SET imagen = :imagen WHERE _id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':imagen', $imagen, PDO::PARAM_LOB);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Métodos para Motocicletas
    public function obtenerMotocicletaPorId($id) {
        $query = "SELECT * FROM MOTOCICLETA WHERE _id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetallesCompletosMoto($id) {
        $query = "SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
                         M.color, M.precio, M.fecha_ingreso, M.cantidad
                  FROM MOTOCICLETA M
                  INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
                  WHERE M._id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $moto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Asegúrate de devolver la imagen en base64 si existe (para AJAX)
        if ($moto && !empty($moto['imagen'])) {
            $moto['imagen'] = base64_encode($moto['imagen']);
        } else if ($moto) {
            $moto['imagen'] = null;
        }

        return $moto;
    }

    public function crearMotocicleta($idModelo, $color, $precio, $fechaIngreso, $cantidad) {
        $query = "INSERT INTO MOTOCICLETA 
                  (id_modelo, color, precio, fecha_ingreso, cantidad) 
                  VALUES (:id_modelo, :color, :precio, :fecha_ingreso, :cantidad)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_modelo', $idModelo);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':fecha_ingreso', $fechaIngreso);
        $stmt->bindParam(':cantidad', $cantidad);
        return $stmt->execute();
    }

    public function actualizarMotocicleta($id, $idModelo, $color, $precio, $fechaIngreso, $cantidad) {
        $query = "UPDATE MOTOCICLETA SET 
                  id_modelo = :id_modelo,
                  color = :color,
                  precio = :precio,
                  fecha_ingreso = :fecha_ingreso,
                  cantidad = :cantidad
                  WHERE _id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_modelo', $idModelo);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':fecha_ingreso', $fechaIngreso);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Métodos para el catálogo
    public function obtenerMotocicletas($brandFilter = '', $modelFilter = '', $ccFilter = '') {
        $query = "SELECT M._id AS moto_id, MM.marca, MM.modelo, MM.cilindrada, MM.imagen,
                         M.color, M.precio, M.fecha_ingreso, M.cantidad 
                  FROM MOTOCICLETA M
                  INNER JOIN MODELO_MOTO MM ON M.id_modelo = MM._id
                  WHERE M.cantidad >= 0";
        
        $params = [];

        if (!empty($brandFilter)) {
            $query .= " AND MM.marca LIKE :marca";
            $params[':marca'] = "%$brandFilter%";
        }
        if (!empty($modelFilter)) {
            $query .= " AND MM.modelo LIKE :modelo";
            $params[':modelo'] = "%$modelFilter%";
        }
        if (!empty($ccFilter) && $ccFilter > 0) {
            $query .= " AND MM.cilindrada = :cilindrada";
            $params[':cilindrada'] = $ccFilter;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMarcas() {
        $stmt = $this->conn->prepare("SELECT DISTINCT marca FROM MODELO_MOTO");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>