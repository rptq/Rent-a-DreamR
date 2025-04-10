<?php

// check if form is submitted
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
}

class UserController {
    private $conn;

    public function __construct() {
        // database conexión
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "rentadream";

        // crear la conexión
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        // echo "Connected successfully"; // Puedes comentar esto si no quieres mostrarlo
    }

    public function login(): void {
    
        // Obtener datos del formulario
        $username = $_POST['username'];
        $password = $_POST['password'];
    
        // Validar que no estén vacíos
        if (empty($username) || empty($password)) {
            $_SESSION['logged'] = false;
            $_SESSION['error'] = "Por favor, complete todos los campos.";
            header("Location: ../view/login.php");
            exit();
        }
    
        // Preparar y ejecutar consulta
        $stmt = $this->conn->prepare("SELECT name, email, password FROM users WHERE name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Verificar si existe el usuario
        if ($row = $result->fetch_assoc()) {
            // Verificar contraseña
            if (password_verify($password, $row['password'])) {
                // Autenticación exitosa
                $_SESSION['logged'] = true;
                $_SESSION['user'] = $row['name'];
                $_SESSION['email'] = $row['email'];
    
                // Redirigir
                header("Location: ../view/profile.php");
                exit();
            }
        }
    
        // Si falla
        $_SESSION['logged'] = false;
        $_SESSION['error'] = "Nombre de usuario o contraseña inválidos.";
        header("Location: ../view/login.php");
        exit();
    }

    public function register(): void {
        
        
    }

    public function logout(): void {
        session_start();
        session_unset();
        session_destroy();

        header("Location: ../../view/login.php");
        exit();
    }
}