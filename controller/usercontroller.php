<?php
session_start();

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
}
