<?php
    session_start();
    $_SESSION["users"][] = [
        "email" => "admin@admin.net",
        "password" => "admin"
    ];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
        // Recoger y limpiar los datos
        $email = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars($_POST['password']);
        
        foreach($_SESSION["users"] as $user){
            $emailExists = ($user["email"] == $email);
            $passwordExists = ($user["password"] == $password);
        }
        
        if ($emailExists && $passwordExists){
            header("Location: /index.html");
        } else {
            echo "<p>Incorrect user</p>";
        }
        
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="login">
        <div style="justify-content: center;text-align:center;">
            <a href="/index.html"><img src="img/Rent-a-dream-logo-only.png" width="180rem" alt=""></a>
        </div>
        <div style="display:flex;text-align: center;justify-content:center;">
            <!-- From Uiverse.io by Smit-Prajapati -->
            <div class="container">
                <div class="heading">Log in</div>
                <form action="login.php" class="form" method="POST">
                    <input required class="input" type="email" name="email" id="email" placeholder="E-mail">
                    <input required class="input" type="password" name="password" id="password" placeholder="Password">
                    <span class="forgot-password"><a href="sign_up.php" style="text-shadow: none">Forgot Password ?</a></span>
                    <input class="login-button" type="submit" value="Log In">
                </form>
            </div>
        </div>

    </div>

</body>
</html>