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
        include 'functions.php';
        session_start();
        createUserIfnotExists();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = htmlspecialchars(trim($_POST['email']));
            $password = htmlspecialchars($_POST['password']);

            $isValidUser = false;

            foreach ($_SESSION["users"] as $user) {
                if ($user["email"] === $email && password_verify($password, $user["password"])) {
                    $isValidUser = true;
                    $_SESSION["activeUser"] = [
                        "email" => $user["email"],
                        "name" => $user["name"],
                        "dni" => $user["dni"],
                        "password" => $user["password"],
                        "rol" => $user["rol"]
                    ];
                    break;
                }
            }

            if ($isValidUser) {
                header("Location: ../../view/index.php");
                exit();
            } else {
                echo "<p style='color:red; text-align:center;'>Incorrect user</p>";
            }
        }
    }

    public function register(): void {
        include 'functions.php';
        session_start();
        createUserIfnotExists();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = htmlspecialchars(trim($_POST['name'] ?? ''));
            $email = htmlspecialchars(trim($_POST['email'] ?? ''));
            $password = htmlspecialchars($_POST['password'] ?? '');
            $dni = htmlspecialchars(trim($_POST['dni'] ?? ''));
            $rol = ($_POST["radio"] == "2") ? "admin" : "user";

            if (empty($name) || empty($email) || empty($password) || empty($dni)) {
                echo "<p style='color:red; text-align:center;'>Please fill in all fields.</p>";
                return;
            }

            foreach ($_SESSION["users"] as $user) {
                if ($user["email"] === $email) {
                    echo "<p style='color:red; text-align:center;'>User already exists.</p>";
                    return;
                }
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $_SESSION["users"][] = [
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword,
                "dni" => $dni,
                "rol" => $rol
            ];

            $_SESSION["activeUser"] = [
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword,
                "dni" => $dni,
                "rol" => $rol
            ];

            header("Location: ../../view/index.php");
            exit();
        }
    }

    public function logout(): void {
        session_start();
        session_unset();
        session_destroy();

        header("Location: ../../view/login.php");
        exit();
    }
}