<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
} 

$employee_id = $_SESSION['employee_id'];

// Obtenir les informations de l'employé connecté
$sql_employee = "SELECT full_name, email, matricule FROM employees WHERE employee_id = ?";
$stmt_employee = $conn->prepare($sql_employee);
$stmt_employee->bind_param("i", $employee_id);
$stmt_employee->execute();
$stmt_employee->bind_result($full_name, $email, $matricule);
$stmt_employee->fetch();
$stmt_employee->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <style>
        body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4; /* Couleur de fond neutre */
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.profile-container {
    background: #ffffff; /* Fond blanc pour le conteneur */
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
    max-width: 500px;
    width: 100%;
    margin: 20px;
    border-top: 5px solid #e94e1b; /* Couleur orange typique d'Attijariwafa Bank */
}

.profile-container img {
    border-radius: 50%;
    max-width: 120px;
    margin-bottom: 20px;
}

.profile-container h1 {
    margin-bottom: 10px;
    font-size: 2em;
    color: #333333; /* Couleur sombre pour le texte */
}

.profile-container p {
    margin: 5px 0;
    font-size: 1.1em;
    color: #666666; /* Couleur de texte plus clair */
}

.profile-container .profile-info {
    margin: 20px 0;
    text-align: left;
}

.profile-container .profile-info p {
    margin-bottom: 10px;
    font-size: 1.2em;
}

.profile-container .profile-info p span {
    font-weight: bold;
    color: #333333;
}

.profile-container button {
    background-color: #e94e1b; /* Couleur orange */
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    font-size: 1.1em;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.profile-container button:hover {
    background-color: #c74b1f; /* Couleur orange plus foncée pour le survol */
}

.profile-container .btn-container {
    margin-top: 20px;
}

.profile-container .btn-container a {
    display: inline-block;
    margin-right: 10px;
    text-decoration: none;
}

    </style>
</head>
<body>
    <div class="profile-container">
        <img src="icone.png" alt="Profile Picture">
        <h1><?php echo htmlspecialchars($full_name); ?></h1>
        <div class="profile-info">
            <p><span>Email:</span> <?php echo htmlspecialchars($email); ?></p>
            <p><span>Matricule:</span> <?php echo htmlspecialchars($matricule); ?></p>
        </div>
        <div class="btn-container">
            <a href="dash.php"><button>Retour à l'Accueil</button></a>
            <a href="update_profil2.php"><button>Modifier le Profil</button></a>
        </div>
    </div>
</body>
</html>
