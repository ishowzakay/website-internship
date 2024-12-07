<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom_complet = $_POST['nom_complet'];
        $email = $_POST['email'];
        $matricule = $_POST['matricule'];

        $sql_update = "UPDATE employees SET full_name = ?, email = ?, matricule = ? WHERE employee_id = ?";
        $stmt = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt, 'sssi', $nom_complet, $email, $matricule, $id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            header('Location: listeEmploy.php');
            exit();
        } else {
            echo "Erreur lors de la modification de l'employé.";
        }
    } else {
        $sql = "SELECT full_name, email, matricule FROM employees WHERE employee_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $nom_complet, $email, $matricule);
        mysqli_stmt_fetch($stmt);
    }
} else {
    header('Location: listeEmploye.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Employé</title>
    <style>
        /* Réinitialisation de certains styles par défaut */
        body, h1, form, input, label, button {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #333;
        }

        body {
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        /* Style de l'en-tête */
        header {
            background-color: #e94e1b;
            padding: 20px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }

        header .logo img {
        width: 200px; /* Agrandir l'image du logo */
}

        /* Conteneur principal */
        .form-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            margin-top: 20px;
        }

        /* Titres */
        h1 {
            color: #e94e1b;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Formulaire */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            width: calc(100% - 22px);
        }

        button {
            background-color: #e94e1b;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #d84315;
        }

        /* Styles pour responsivité */
        @media (max-width: 600px) {
            body {
                padding: 20px;
            }

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
            <h1>Modifier Employé</h1>
            <form action="modifierEmploye.php?id=<?php echo $id; ?>" method="post">
                <label for="nom_complet">Nom complet:</label>
                <input type="text" id="nom_complet" name="nom_complet" value="<?php echo htmlspecialchars($nom_complet); ?>" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <label for="matricule">Matricule:</label>
                <input type="text" id="matricule" name="matricule" value="<?php echo htmlspecialchars($matricule); ?>" required>
                <button type="submit">Modifier</button>
            </form>
        </div>
    </main>
</body>
</html>