<?php
include 'db.php';

$error_message = '';

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $matricule = mysqli_real_escape_string($conn, $_POST['matricule']);

    $query = "SELECT * FROM `users` WHERE username=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $error_message = "L'utilisateur existe déjà.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $registration_date = date("Y-m-d H:i:s");

        $insert_query = "INSERT INTO `users` (username, password, email, matricule, created_at) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'sssss', $username, $hashed_password, $email, $matricule, $registration_date);
        $insert_result = mysqli_stmt_execute($stmt);

        if ($insert_result) {
            header('Location: login.php');
            exit;
        } else {
            $error_message = "Erreur lors de l'inscription: " . mysqli_error($conn);
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="registration.css">
</head>
<body>
    <header>
        <div class="logo">
        <a href='login.php'><img src="awb3.0.png" alt="Logo" class="logo"></a>

        </div>
        <nav>
            <a href="login.php">PROFESSIONNEL</a>
        </nav>
    </header>
    <div class="login-container">
        <h2>Inscription</h2>
        <?php if (!empty($error_message)) { echo "<p>$error_message</p>"; } ?>
        <form action="" method="post">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" placeholder="Nom d'utilisateur" required>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Email" required>
            
            <label for="matricule">Matricule</label>
            <input type="text" id="matricule" name="matricule" placeholder="Matricule" required>
             
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="submit">S'inscrire</button>
        </form>
        <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
    </div>
</body>
</html>
