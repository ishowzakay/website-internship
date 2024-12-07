<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Incluez le fichier autoload de Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    $mail = new PHPMailer(true); // Instancier PHPMailer
    try {
        // Configurer le serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Adresse du serveur SMTP pour Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'elcourtzd@gmail.com'; // Votre adresse email
        $mail->Password = 'czvn fbhr ezki oztn'; // Mot de passe de votre email ou mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Paramétrage de l'expéditeur
        $mail->setFrom($email, $name); // Adresse fournie par l'utilisateur comme expéditeur
        $mail->addAddress('elcourtzd@gmail.com'); // Adresse du destinataire

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Nouveau message de contact';
        $mail->Body    = "<html><body>
                            <p><strong>Nom :</strong> $name</p>
                            <p><strong>Email :</strong> $email</p>
                            <p><strong>Message :</strong><br>$message</p>
                            </body></html>";

        $mail->send();
        
        // Redirection après succès
        header('Location: login.php');
        exit();
    } catch (Exception $e) {
        // Affichage d'un message d'erreur dans un pop-up
        echo "<script>alert('Désolé, une erreur est survenue. Veuillez réessayer. Erreur de messagerie: {$mail->ErrorInfo}');</script>";
    }
} else {
    echo "Accès non autorisé.";
}
?>
