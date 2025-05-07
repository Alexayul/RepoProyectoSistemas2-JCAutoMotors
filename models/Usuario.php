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

    public function registrarUsuario($nombre, $apellido, $email, $usuario, $passwordHash) {
        try {
            $this->conn->beginTransaction();
            
            $stmt_persona = $this->conn->prepare(
                "INSERT INTO PERSONA (_id, nombre, apellido, telefono, email, documento_identidad) 
                VALUES (NULL, :nombre, :apellido, '', :email, NULL)"
            );
            $stmt_persona->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':email' => $email
            ]);
        
            $id_persona = $this->conn->lastInsertId();

            $stmt_usuario = $this->conn->prepare(
                "INSERT INTO USUARIO (_id, id_persona, usuario, password, id_rol) 
                VALUES (NULL, :id_persona, :usuario, :password, 3)"
            );
            $stmt_usuario->execute([
                ':id_persona' => $id_persona,
                ':usuario' => $usuario,
                ':password' => $passwordHash
            ]);
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
