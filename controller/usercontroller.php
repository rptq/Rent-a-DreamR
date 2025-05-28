<?php
session_start();
require_once "../controller/functions.php";
if (!isset($_SESSION["activeUser"])) {
  $_SESSION["activeUser"] = [
    "email" => null,
    "name" => null,
    "dni" => null,
    "password" => null,
    "rol" => null
  ];
}
?>


<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = new UserController();

    if (isset($_POST["login"])) {
        $user->login();
    }

    if (isset($_POST["register"])) {
        $user->register();
    }

    if (isset($_POST["logout"])) {
        $user->logout();
    }

    if (isset($_POST["updatePassword"])) {
        $user->updatePassword();
    }
    
    if (isset($_POST["deleteAccount"])) {
        $user->deleteAccount();
    }
}

class UserController
{
    private $pdo;

    public function __construct()
    {
        try {
            $host = 'localhost';
            $dbname = 'rentadream';
            $username = 'root';
            $password = '';

            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $username,
                $password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function listWorkersGirls()
    {
        // 2) Saca todos los registros de workergirl
        $stmt = $this->pdo->query("SELECT idWorker, name, age, height, description, rating, price FROM workergirl");
        return $stmt->fetchAll();
    }
    public function listWorkersMan()
    {
        // 2) Saca todos los registros de workergirl
        $stmt = $this->pdo->query("SELECT idWorker, name, age, height, description, rating, price FROM workerman");
        return $stmt->fetchAll();
    }

    public function getWorkerById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM workerman WHERE idWorker = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $stmt = $this->pdo->prepare("
                SELECT name, email, password, rol, dni 
                FROM users 
                WHERE email = :email
            ");

            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['logged'] = true;
                $_SESSION['username'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['dni'] = $user['dni'];

                header("Location: ../view/index.php");
                exit();
            }

            $_SESSION['logged'] = false;
            $_SESSION['error'] = "Credenciales inválidas";
            header("Location: ../view/login.html");
            exit();
        } catch (PDOException $e) {
            error_log("Error de login: " . $e->getMessage());
            $_SESSION['error'] = "Error en el sistema";
            header("Location: ../view/login.html");
            exit();
        }
    }

    public function register(): void
    {
        $email = $_POST['email'] ?? '';
        $username = $_POST['name'] ?? '';
        $password = $_POST['password'] ?? '';
        $rol = $_POST["rol"] ?? 'user';
        $dni = $_POST["dni"] ?? '';

        // Validaciones
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Todos los campos son requeridos";
            header("Location: ../view/sign_up.html");
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email inválido";
            header("Location: ../view/sign_up.html");
            exit();
        }

        try {
            // Verificar si el email ya existe
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->fetch()) {
                $_SESSION['error'] = "El email ya está registrado";
                header("Location: ../view/sign_up.html");
                exit();
            }

            // Hash de contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("
                INSERT INTO users 
                (name, email, password, rol, dni) 
                VALUES (:name, :email, :password, :rol, :dni)
            ");

            $stmt->execute([
                ':name' => $username,
                ':email' => $email,
                ':password' => $passwordHash,
                ':rol' => $rol,
                ':dni' => $dni
            ]);

            $_SESSION['logged'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['rol'] = $rol;
            $_SESSION['dni'] = $dni;

            header("Location: ../view/index.php");
            exit();
        } catch (PDOException $e) {
            error_log("Error de registro: " . $e->getMessage());
            $_SESSION['error'] = "Error en el registro";
            header("Location: ../view/sign_up.html");
            exit();
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header("Location: ../view/login.html");
        exit();
    }


    public function updatePassword() {

    if (!isset($_SESSION['email'])) {
        header("Location: ../views/userconfig.php?msg=notfound");
        exit();
    }

    require_once '../model/UserModel.php';
    $userModel = new UserModel();

    $email = $_SESSION['email'];
    $currentPassword = $_POST['actual'] ?? '';
    $newPassword = $_POST['nueva'] ?? '';
    $confirmPassword = $_POST['confirmar'] ?? '';

    // Obtener usuario desde la base de datos
    $user = $userModel->getUserByEmail($email);

    if (!$user) {
        header("Location: ../views/userconfig.php?msg=notfound");
        exit();
    }

    // Verificar contraseña actual
    if (!password_verify($currentPassword, $user['password'])) {
        header("Location: ../views/userconfig.php?msg=wrongcurrent");
        exit();
    }

    // Verificar que las nuevas contraseñas coinciden
    if ($newPassword !== $confirmPassword) {
        header("Location: ../views/userconfig.php?msg=nomatch");
        exit();
    }

    // Encriptar nueva contraseña y actualizar
    $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updated = $userModel->updateUserPassword($email, $hashedNewPassword);

    if ($updated) {
        header("Location: ../views/userconfig.php?msg=success");
    } else {
        header("Location: ../views/userconfig.php?msg=error");
    }

    exit();
    }


    public function deleteAccount(): void
{
    // Verifica si el usuario está logueado
    if (!isset($_SESSION['email'])) {
        $_SESSION['error'] = "Debes iniciar sesión para eliminar tu cuenta.";
        header("Location: ../view/login.html");
        exit();
    }

    $email = $_SESSION['email'];

    try {
        // Eliminar la cuenta del usuario
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Cerrar sesión y limpiar datos
        $_SESSION = [];
        session_destroy();

        // Redirigir a la página principal
        header("Location: ../view/index.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error al eliminar la cuenta: " . $e->getMessage());
        $_SESSION['error'] = "Ocurrió un error al eliminar tu cuenta.";
        header("Location: ../view/userconfig.php");
        exit();
    }
    }

}
