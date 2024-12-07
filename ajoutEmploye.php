<?php
session_start();
include 'db.php';
require 'vendor/autoload.php'; // Inclut PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Afficher les erreurs PHP pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifiez si l'utilisateur est authentifié
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom_complet = mysqli_real_escape_string($conn, $_POST['nom_complet']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $matricule = mysqli_real_escape_string($conn, $_POST['matricule']);

    // Vérifiez si l'email existe déjà
    $sql_check_email = "SELECT * FROM employees WHERE email = '$email'";
    $result = mysqli_query($conn, $sql_check_email);

    if (mysqli_num_rows($result) > 0) {
        $message = 'L\'email existe déjà. Veuillez utiliser un email différent.';
    } else {
        // Générer un mot de passe aléatoire de 10 caractères
        $password = bin2hex(random_bytes(5)); // Génère un mot de passe de 10 caractères
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hacher le mot de passe

        // Ajoutez l'utilisateur à la base de données
        $sql = "INSERT INTO employees (full_name, email, matricule, password, created_at) VALUES ('$nom_complet', '$email', '$matricule', '$hashed_password', NOW())";

        if (mysqli_query($conn, $sql)) {
            // Configuration de PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Paramètres du serveur
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'elcourtzd@gmail.com'; // Remplacez par votre adresse e-mail
                $mail->Password = 'czvn fbhr ezki oztn'; // Remplacez par votre mot de passe ou mot de passe d'application
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('no-reply@attijariwafabank.com', 'Attijariwafa Bank');
                $mail->addAddress($email, $nom_complet);

                $mail->isHTML(true);
                $mail->Subject = 'Votre nouveau compte employé';
                $mail->Body = "Bonjour $nom_complet,<br><br>Votre compte a été créé avec succès. Voici votre mot de passe : <strong>$password</strong><br><br>Cordialement,<br>L'équipe Attijariwafa Bank";

                $mail->send();
                $message = 'Employé ajouté avec succès et un email a été envoyé avec le mot de passe!';
            } catch (Exception $e) {
                $message = "Employé ajouté, mais l'email n'a pas pu être envoyé. Erreur de messagerie: {$mail->ErrorInfo}";
            }

            // Redirection vers listeEmploy.php après succès
            header('Location: listeEmploy.php');
            exit();
        } else {
            $message = 'Erreur: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Employé</title>
   <style>/* Reset CSS */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f0f2f5;
    color: #333;
}

header {
    background-color: #e94e1b;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 60px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

header .logo img {
    max-height: 40px;
}

main {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 60px);
    padding: 20px;
}

.form-container {
    background: #fff;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 500px;
}

.form-container h1 {
    margin-bottom: 20px;
    color: #e94e1b;
    text-align: center;
}

.form-container p {
    color: #e94e1b;
    text-align: center;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

.form-group input:focus {
    border-color: #e94e1b;
    outline: none;
    box-shadow: 0 0 5px rgba(233, 78, 27, 0.5);
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    color: #fff;
    background-color: #e94e1b;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s ease;
    width: 100%;
}

.btn:hover {
    background-color: #d43d15;
}

/* Responsive styling */
@media (max-width: 768px) {
    .form-container {
        padding: 20px;
    }
}
</style>
</head>
<body>
    <header>
        <div class="logo">
            <a href='listeEmploy.php'><img src="awb logo desktop.png" alt="Attijariwafa Bank Logo"></a>
        </div>
    </header>
    <main>
        <div class="form-container">
            <h1>Ajouter un Employé</h1>
            <?php if ($message): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <form action="ajoutEmploye.php" method="POST">
                <div class="form-group">
                    <label for="nom_complet">Nom complet:</label>
                    <input type="text" id="nom_complet" name="nom_complet" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="matricule">Matricule:</label>
                    <input type="text" id="matricule" name="matricule" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>