<?php
session_start();
include 'db.php';

function validate_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = validate_input($_POST['username']);
    $password = validate_input($_POST['password']);
    $message = "Identifiant ou mot de passe incorrect.";

    if ($conn) {
        $stmt = $conn->prepare('SELECT user_id, password FROM users WHERE username = ?');
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    header('Location: accueil.php');
                    exit();
                }
            }
            $stmt->close();
        }

        $stmt = $conn->prepare('SELECT employee_id, password FROM employees WHERE email = ?');
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($employee_id, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['employee_id'] = $employee_id;
                    $_SESSION['username'] = $username;
                    header('Location: dash.php');
                    exit();
                }
            }
            $stmt->close();
        }

    } else {
        $message = "Échec de la connexion à la base de données.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        header {
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 10px 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header .logo img {
            height: 80px;
            margin-left: 20px;
        }

        header nav {
            margin-right: 20px;
        }

        header nav a {
            color: #ff9800;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }

        header nav a:hover {
            text-decoration: underline;
        }

        .sub-header {
            background-color: #ffffff;
            width: 100%;
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #dcdcdc; /* Bordure grise */
        }

        .sub-header h1 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .sub-header p {
            color: #666666;
            font-size: 16px;
            margin-bottom: 0;
        }

        main {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
            width: 100%;
            max-width: 1200px;
        }
        .carousel-inner {
    max-height: 500px; /* Ajustez la hauteur selon vos besoins */
        }

.carousel-inner img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Assure que l'image couvre toute la surface du carrousel */
}

        .main-content {
            flex: 1;
            margin-right: 20px;
        }

        .main-content .card {
            background-color: #ffffff;
            padding: 1px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .auth-form-container {
            display: flex;
        }

        .auth-form {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            padding: 20px;
            width: 300px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        .auth-form h2 {
            color: #666666;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .auth-form label {
            display: block;
            font-size: 14px;
            color: #888888;
            margin-bottom: 5px;
        }

        .auth-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            font-size: 14px;
        }

        .auth-form button {
            width: 100%;
            padding: 10px;
            background-color: #ff9800;
            color: #ffffff;
            border: none;
            border-radius: 3px;
            font-size: 16px;
            cursor: pointer;
        }

        .auth-form button:hover {
            background-color: #e68900;
        }

        .auth-form a {
            display: block;
            text-align: center;
            color: #007bff;
            text-decoration: none;
            margin-top: 10px;
        }

        .auth-form a:hover {
            text-decoration: underline;
        }

        footer {
            background-color: white;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #ddd;
            width: 100%;
        }

        footer a {
            color: #f77e22;
            text-decoration: none;
            margin: 0 10px;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href='index.php'><img src="awb3.0.png" alt="Attijariwafa Bank"></a>
        </div>
        <nav>
            <a href="index.php">Home</a>
            <a href="#" data-toggle="modal" data-target="#aboutModal">À propos</a>
            <a href="#" data-toggle="modal" data-target="#contactModal">Contact</a>
        </nav>
    </header>

    <div class="sub-header">
        <h1>ATTIJARI TASKS</h1>
        <p>Contrôlez l'état de votre tâche en toute simplicité.</p>
    </div>

    <main>
        <div class="main-content">
            <!-- Carrousel avec le style de la classe card -->
            <div class="card">
                <div id="carouselExample" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="task.png" class="d-block w-100" alt="Image 1">
                        </div>
                        <div class="carousel-item">
                            <img src="slide2.jpeg" class="d-block w-100" alt="Image 2">
                        </div>
                        <div class="carousel-item">
                            <img src="slide3.jpg" class="d-block w-100" alt="Image 3">
                        </div>
                    </div>
                    <a class="carousel-control-prev" href="#carouselExample" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExample" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="auth-form-container">
            <div class="auth-form">
                <h2>AUTHENTIFICATION</h2>
                <?php if (isset($message)): ?>
                    <p style="color:red;"><?php echo $message; ?></p>
                <?php endif; ?>
                <form action="" method="post">
                    <label for="username">IDENTIFIANT</label>
                    <input type="text" name="username" id="username" placeholder="Identifiant ou Email" required>
                    <label for="password">MOT DE PASSE</label>
                    <input type="password" name="password" id="password" placeholder="Mot de passe" required>
                    <button type="submit">SE CONNECTER</button>
                </form>
                <p><a href="assistanceConnexion.html">Aide à la Connexion</a></p>

                
            </div>
        </div>
    </main>

    <!-- Fenêtre Modale À propos -->
    <div class="modal fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aboutModalLabel">À propos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Bienvenue sur notre application de gestion de tâches pour Attijariwafa Bank. Ici, vous pouvez gérer efficacement les tâches, assigner des responsabilités et suivre l'avancement des projets.</p>
                    <p>Version 1.0.0</p>
                    <p>Pour toute question ou assistance, veuillez contacter notre support à support@attijariwafabank.com.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fenêtre Modale Contact -->
    <div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">Contactez-nous</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="send_contact.php" method="post">
                        <div class="form-group">
                            <label for="name">Nom</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <a href="#">Sécurité garantie</a> |
        <a href="#">Services innovants</a> |
        <a href="#">Accessibilité</a> |
        <a href="#">Aide contextuelle</a> |
        <a href="#">Support</a>
        <p>© 2024 Attijariwafa Bank</p>
    </footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>