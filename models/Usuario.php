<?php
class Usuario {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerPorUsuario($usuario) {
        $stmt = $this->conn->prepare("SELECT * FROM USUARIO WHERE usuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function verificarEmailExistente($email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM PERSONA WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    public function verificarUsuarioExistente($usuario) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM USUARIO WHERE usuario = :usuario");
        $stmt->execute(['usuario' => $usuario]);
        return $stmt->fetchColumn() > 0;
    }

    public function registrarUsuario($nombre, $apellido, $email, $usuario, $passwordHash, $id_persona = null) {
        try {
            $this->conn->beginTransaction();

            if ($id_persona === null) {
                $stmt_persona = $this->conn->prepare(
                    "INSERT INTO PERSONA (nombre, apellido, telefono, email, documento_identidad) 
                    VALUES (:nombre, :apellido, '', :email, NULL)"
                );
                $stmt_persona->execute([
                    ':nombre' => $nombre,
                    ':apellido' => $apellido,
                    ':email' => $email
                ]);
        
                $id_persona = $this->conn->lastInsertId();
            }

            $stmt_usuario = $this->conn->prepare(
                "INSERT INTO USUARIO (_id, id_persona, usuario, password, id_rol) 
                VALUES (NULL, :id_persona, :usuario, :password, 3)"
            );
            $stmt_usuario->execute([
                ':id_persona' => $id_persona,
                ':usuario' => $usuario,
                ':password' => $passwordHash
            ]);

            $stmt_check = $this->conn->prepare("SELECT COUNT(*) FROM CLIENTE WHERE _id = :id_persona");
            $stmt_check->execute([':id_persona' => $id_persona]);
            if ($stmt_check->fetchColumn() == 0) {
                $stmt_cliente = $this->conn->prepare(
                    "INSERT INTO CLIENTE (_id, croquis_domicilio, factura_servicio, id_rol) 
                    VALUES (:id_persona, NULL, NULL, 3)"
                );
                $stmt_cliente->execute([
                    ':id_persona' => $id_persona
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function actualizarUsuario($id_persona, $usuario, $passwordHash = null) {
        if ($passwordHash) {
            $stmt = $this->conn->prepare("UPDATE USUARIO SET usuario = :usuario, password = :password WHERE id_persona = :id_persona");
            $stmt->execute([
                ':usuario' => $usuario,
                ':password' => $passwordHash,
                ':id_persona' => $id_persona
            ]);
        } else {
            $stmt = $this->conn->prepare("UPDATE USUARIO SET usuario = :usuario WHERE id_persona = :id_persona");
            $stmt->execute([
                ':usuario' => $usuario,
                ':id_persona' => $id_persona
            ]);
        }
        return true;
    }

    public function obtenerUsuarioPorPersona($id_persona) {
        $stmt = $this->conn->prepare("SELECT usuario FROM USUARIO WHERE id_persona = ?");
        $stmt->execute([$id_persona]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['usuario'] : '';
    }

}
