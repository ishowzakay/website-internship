<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$sql = "SELECT employee_id, full_name, email, matricule, created_at FROM employees";
$result = mysqli_query($conn, $sql);

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Liste des Employés</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 0;
            transition: background-color 0.3s, color 0.3s;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #e94e1b;
            padding: 10px 20px;
            color: white;
            height: 60px;
            transition: background-color 0.3s;
        }

        .header img {
            max-height: 40px;
        }

        .header .menu-toggle, .header .mode-toggle {
            font-size: 24px;
            cursor: pointer;
        }

        .sidebar {
            width: 200px;
            background-color: #ff6f3c;
            position: fixed;
            top: 60px;
            bottom: 0;
            padding-top: 20px;
            color: white;
            overflow-x: hidden;
            transition: background-color 0.3s;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #c0392b;
        }

        .content {
            margin-left: 220px;
            padding: 20px;
        }

        .content h1 {
            color: #e94e1b;
            text-align: center;
            margin-bottom: 20px;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
            padding: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .table th, .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #f8f9fa;
            color: #333;
            text-transform: uppercase;
            font-size: 14px;
        }

        .table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table tr:hover {
            background-color: #e9ecef;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            text-decoration: none;
            color: #fff;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-warning {
            background-color: #f39c12;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .dropdown {
            display: none;
            position: absolute;
            right: 20px;
            top: 60px;
            background-color: white;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown a {
            color: #333;
            padding: 10px 20px;
            text-decoration: none;
            display: block;
        }

        .dropdown a:hover {
            background-color: #ddd;
        }

        .show {
            display: block;
        }

        /* Dark mode styles */
        .dark-mode {
            background-color: #333;
            color: #f4f4f4;
        }

        .dark-mode .header {
            background-color: #333;
            color: #f4f4f4;
        }

        .dark-mode .sidebar {
            background-color: #333;
            color: #f4f4f4;
        }

        .dark-mode .sidebar a {
            color: #f4f4f4;
        }

        .dark-mode .sidebar a:hover {
            background-color: #666;
        }

        .dark-mode .content {
            background-color: #555;
            color: #f4f4f4;
        }

        .dark-mode .table {
            background-color: #444;
            color: #f4f4f4;
        }

        .dark-mode .table th {
            background-color: #555;
            color: #f4f4f4;
        }

        .dark-mode .table tr:nth-child(even) {
            background-color: #555;
        }

        .dark-mode .table tr:hover {
            background-color: #666;
        }

        .dark-mode .table-container {
            background-color: #444;
        }

        .dark-mode .dropdown {
            background-color: #333;
            color: #f4f4f4;
        }

        .dark-mode .dropdown a {
            color: #f4f4f4;
        }

        .dark-mode .dropdown a:hover {
            background-color: #666;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .sidebar a {
                display: inline-block;
                padding: 10px;
            }

            .content {
                margin: 0;
                padding: 10px;
            }

            .table th, .table td {
                padding: 8px;
            }

            .btn {
                padding: 8px 12px;
            }
        }
    </style>
    <script>
        // Apply dark mode immediately if enabled
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.documentElement.classList.add('dark-mode');
        }

        function confirmDelete(employeeId) {
            const confirmation = confirm("Êtes-vous sûr de vouloir supprimer cet employé ?");
            if (confirmation) {
                window.location.href = 'supprimerEmploye.php?id=' + employeeId;
            }
        }

        function toggleDropdown() {
            document.getElementById("dropdown").classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches('.menu-toggle')) {
                var dropdowns = document.getElementsByClassName("dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark-mode');

            // Save dark mode state to localStorage
            if (document.documentElement.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <img src="awb logo desktop.png" alt="Attijariwafa Bank Logo">
        <div>
            <span class="menu-toggle" onclick="toggleDropdown()">☰</span>
            <span class="mode-toggle ml-3" onclick="toggleDarkMode()">🌙</span>
        </div>
    </div>
    <div class="sidebar">
        <a href="accueil.php">Accueil</a>
        <a href="listeEmploy.php">Employés</a>
        <a href="task.php">Tâches</a>
        <a href="archive.php">Archives</a>
    </div>

    <div class="content">
        <div class="table-container">
            <h1>Liste des Employés</h1>
            <a href="ajoutEmploye.php" class="btn btn-primary"><img src="ajouter.png" alt="Ajouter" style="width: 20px; height: 20px;"> Ajouter un employé</a>

            <?php if (!empty($message)): ?>
                <div class="message">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Matricule</th>
                        <th>Date de Création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['employee_id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['matricule']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
                            echo '<td>';
                            echo '<a href="modifierEmploye.php?id=' . htmlspecialchars($row['employee_id']) . '"><img src="edit.png" alt="Modifier" style="width: 20px; height: 20px;"></a> ';
                            echo '<a href="#" onclick="confirmDelete(' . htmlspecialchars($row['employee_id']) . ')"><img src="supprimer.png" alt="Supprimer" style="width: 20px; height: 20px;"></a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6">Aucun employé trouvé.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="dropdown" class="dropdown">
        <a href="profil.php">Profil</a>
        <a href="logout.php">Déconnexion</a>
    </div>
</body>
</html>