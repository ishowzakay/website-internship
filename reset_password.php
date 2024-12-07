<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "awb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule = $_POST['matricule'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT employee_id FROM employees WHERE matricule = ? AND email = ?");
    $stmt->bind_param("ss", $matricule, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($employee_id);
        $stmt->fetch();

        $new_password = bin2hex(random_bytes(4)); // Génère un mot de passe de 8 caractères hexadécimaux
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $update_stmt = $conn->prepare("UPDATE employees SET password = ? WHERE employee_id = ?");
        $update_stmt->bind_param("si", $hashed_password, $employee_id);
        if ($update_stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'elcourtzd@gmail.com'; // Remplacez par votre adresse e-mail
                $mail->Password = 'czvn fbhr ezki oztn'; // Remplacez par votre mot de passe ou mot de passe d'application
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('elcourtzd@gmail.com', 'Attijariwafa bank');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de votre mot de passe';
                $mail->Body = 'Votre nouveau mot de passe est : ' . $new_password;

                $mail->send();
                $message = "Votre mot de passe a été réinitialisé. Veuillez vérifier votre e-mail pour le nouveau mot de passe.";
                $message_type = "success";
            } catch (Exception $e) {
                $message = "Une erreur est survenue lors de l'envoi de l'e-mail : " . $mail->ErrorInfo;
                $message_type = "error";
            }
        } else {
            $message = "Une erreur est survenue lors de la réinitialisation du mot de passe.";
            $message_type = "error";
        }

        $update_stmt->close();
    } else {
        $message = "Aucun employé trouvé avec ces informations.";
        $message_type = "error";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe</title>
    <style>
        body, h1, p, a {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            height: 60px;
        }

        main {
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #f39200;
        }

        .message-box {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 4px;
            font-size: 16px;
        }

        .message-box.success {
            background-color: #e7f7e8;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #c62828;
            border: 1px solid #c62828;
        }

        .return-link {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .return-link:hover {
            background-color: #0056b3;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            color: #777;
        }

        footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <a href='login.php'><img src="awb3.0.png" alt="Logo" class="logo"></a>
        </header>
        <main>
            <h1>Réinitialisation de votre mot de passe</h1>
            <?php if ($message): ?>
                <div class="message-box <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <a href="login.php" class="return-link">Retour à la page de connexion</a>
        </main>
        <footer>
            <p>&copy; 2024 Attijariwafa bank</p>
        </footer>
    </div>
</body>
</html>