<?php
require_once __DIR__ . '/../config/db.php'; // ajusta el path según tu estructura


class UserModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect(); // Asume que tienes una clase Database con un método connect()
    }

    // Obtener usuario por email
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Devuelve un array asociativo del usuario
    }

    // Actualizar contraseña
    public function updateUserPassword($email, $hashedPassword) {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        return $stmt->execute();
    }
}