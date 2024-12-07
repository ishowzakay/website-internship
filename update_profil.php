<?php
session_start();
include 'db.php';

// Assuming the user is logged in and their user ID is stored in the session
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission to update user information
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    try {
        // Fetch current user information
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($user_password);
        $stmt->fetch();
        $stmt->close();

        // Check if the old password is correct
        if (password_verify($old_password, $user_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $password_hash, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Profil mis à jour avec succès!";
            $_SESSION['msg_type'] = "success";
            header("Location: profil.php");
            exit;
        } else {
            $_SESSION['message'] = "Ancien mot de passe incorrect.";
            $_SESSION['msg_type'] = "error";
            header("Location: update_profil.php");
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['message'] = "Erreur: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
        header("Location: update_profil.php");
        exit;
    }
} else {
    try {
        // Fetch user information
        $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($username);
        $stmt->fetch();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        $_SESSION['message'] = "Erreur: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Profil</title>
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

input[type="text"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    display: inline-block;
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
        <h2>Modifier le Profil</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?php echo $_SESSION['msg_type'] == 'success' ? 'alert-success' : 'alert-error'; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['msg_type']);
                ?>
            </div>
        <?php endif; ?>

        <form method="post" action="update_profil.php">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly>

            <label for="old_password">Ancien Mot de Passe</label>
            <input type="password" id="old_password" name="old_password" required>

            <label for="new_password">Nouveau Mot de Passe</label>
            <input type="password" id="new_password" name="new_password" required>

            <button type="submit">Mettre à jour le profil</button>
        </form>
        <a href="profil.php" class="btn-back">Retour au profil</a>
    </div>
</body>
</html>
