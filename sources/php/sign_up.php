<?php
session_start();
if ($_SESSION["users"])


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["Register"])) {
    $email = htmlspecialchars(trim($_POST['email']));
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $dni = htmlspecialchars(trim($_POST['dni']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar contraseña

    // Verificar si ya existe el usuario
    $exists = false;
    foreach ($_SESSION["users"] as $user) {
        if ($user["email"] === $email) {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        // Agregar nuevo usuario a la sesión
        $_SESSION["users"][] = [
            "email" => $email,
            "name" => $nombre,
            "dni" => $dni,
            "password" => $password,
            "rol" => "user"
        ];
        
        // Mandar al login
        header("Location: login.php");
    } else {
        echo "El email ya está registrado.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>

    <div class="login">
        <div style="justify-content: center;text-align:center;">
            <a href="../index.html"><img src="../img/Rent-a-dream-logo-only.png" width="150rem" alt=""></a>
        </div>
        <div style="display:flex;text-align: center;justify-content:center;">
            <!-- From Uiverse.io by Smit-Prajapati -->
            <div class="container">
                <div class="heading">Register</div>
                <form method="POST" class="form">
                    <input required class="input" type="email" name="email" id="email" placeholder="E-mail">
                    <input required class="input" type="dni" name="nombre" id="nombre" placeholder="Nombre">
                    <input required class="input" type="dni" name="dni" id="dni" placeholder="DNI">
                    <input required class="input" type="password" name="password" id="password" placeholder="Password">
                    <span class="forgot-password"><a href="login.php" style="text-shadow: none">You alredy have an account?</a></span>
                    <input class="login-button" type="submit" name="Register" value="Register">
                </form>
            </div>
        </div>

    </div>
    <div></div>

</body>

</html>