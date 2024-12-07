<?php
session_start();
include 'db.php';

// Vérifiez si l'employé est connecté
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire pour la mise à jour du mot de passe
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Vérifiez si le nouveau mot de passe respecte la longueur minimale requise
    if (strlen($new_password) < 8) {
        $_SESSION['message'] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        $_SESSION['msg_type'] = "error";
        header("Location: update_profil2.php");
        exit;
    }

    try {
        // Récupérer le mot de passe actuel de l'employé depuis la base de données
        $stmt = $conn->prepare("SELECT password FROM employees WHERE employee_id = ?");
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $stmt->bind_result($employee_password);
        $stmt->fetch();
        $stmt->close();

        // Vérifier si l'ancien mot de passe est correct
        if (password_verify($old_password, $employee_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe de l'employé
            $stmt = $conn->prepare("UPDATE employees SET password = ? WHERE employee_id = ?");
            $stmt->bind_param("si", $password_hash, $employee_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Mot de passe mis à jour avec succès!";
            $_SESSION['msg_type'] = "success";
            header("Location: profil2.php");
            exit;
        } else {
            $_SESSION['message'] = "Ancien mot de passe incorrect.";
            $_SESSION['msg_type'] = "error";
            header("Location: update_profil2.php");
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['message'] = "Erreur: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
        header("Location: update_profil2.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Mot de Passe</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
            margin: 20px;
            border-top: 5px solid #f57c00; /* Orange Attijari */
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #f57c00; /* Orange Attijari */
            color: white;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #e65100; /* Couleur orange plus foncée */
        }

        .alert {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: left;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn-back {
            display: block;
            width: 95%;
            margin: 10px 0;
            padding: 10px;
            text-align: center;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier le Mot de Passe</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?php echo $_SESSION['msg_type'] == 'success' ? 'alert-success' : 'alert-error'; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['msg_type']);
                ?>
            </div>
        <?php endif; ?>

        <form method="post" action="update_profil2.php">
            <label for="old_password">Ancien Mot de Passe</label>
            <input type="password" id="old_password" name="old_password" required>

            <label for="new_password">Nouveau Mot de Passe</label>
            <input type="password" id="new_password" name="new_password" minlength="8" required>

            <button type="submit">Mettre à jour le mot de passe</button>
        </form>
        <a href="profil2.php" class="btn-back">Retour au profil</a>
    </div>
</body>
</html>
