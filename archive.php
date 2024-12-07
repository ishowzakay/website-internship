<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

$sql = "SELECT user_id, email, matricule FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) != 1) {
    die("Erreur lors de la récupération de l'utilisateur : " . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);
$admin_id = $row['user_id'];
$admin_email = $row['email'];
$admin_matricule = $row['matricule'];

$query = "SELECT archive_tasks.*, employees.full_name AS employee_name, employees.email AS employee_email, employees.matricule AS employee_matricule 
          FROM archive_tasks 
          INNER JOIN employees ON archive_tasks.user_id = employees.employee_id 
          WHERE archive_tasks.task_status = 'done'";
$queryResult = mysqli_query($conn, $query);

if (!$queryResult) {
    die("Erreur dans la requête : " . mysqli_error($conn));
}

if (isset($_POST['download_pdf'])) {
    require_once('vendor/autoload.php');
    $pdf = new TCPDF();

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $html = '<h1>Liste des tâches terminées</h1>';
    $html .= '<table border="1" cellpadding="4">
                <thead>
                    <tr>
                        <th>Nom de l\'employé</th>
                        <th>Email de l\'employé</th>
                        <th>Matricule de l\'employé</th>
                        <th>Titre de la tâche</th>
                        <th>Description de la tâche</th>
                        <th>Date d\'archivage</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>';

    if ($queryResult && mysqli_num_rows($queryResult) > 0) {
        while ($line = mysqli_fetch_assoc($queryResult)) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($line['employee_name']) . '</td>
                        <td>' . htmlspecialchars($line['employee_email']) . '</td>
                        <td>' . htmlspecialchars($line['employee_matricule']) . '</td>
                        <td>' . htmlspecialchars($line['task_title']) . '</td>
                        <td>' . htmlspecialchars($line['task_description']) . '</td>
                        <td>' . htmlspecialchars($line['archived_at']) . '</td>
                        <td>' . htmlspecialchars($line['task_status']) . '</td>
                      </tr>';
        }
    } else {
        $html .= '<tr>
                    <td colspan="7">Aucune tâche trouvée.</td>
                  </tr>';
    }
    
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output('taches_archivees.pdf', 'D');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Liste des tâches terminées</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style> 
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            transition: background-color 0.3s, color 0.3s;
        }

        body.dark-mode {
            background-color: #1c1c1c;
            color: #f4f4f4;
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

        .header.dark-mode {
            background-color: #333;
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
            background: #ff6f3c;
            position: fixed;
            top: 60px;
            bottom: 0;
            padding-top: 20px;
            color: white;
            overflow-x: hidden;
            transition: background-color 0.3s;
        }

        .sidebar.dark-mode {
            background: #333;
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

        .sidebar.dark-mode a:hover {
            background-color: #555;
        }

        .content {
            margin-left: 220px;
            padding: 20px;
        }

        .content h1 {
            text-align: center;
            color: #e94e1b;
        }

        .content.dark-mode h1 {
            color: #e94e1b;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
            padding: 20px;
        }

        .table-container.dark-mode {
            background-color: #444;
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
            color: black;
        }

        .table th {
            background-color: #f8f9fa;
            color: #333;
            text-transform: uppercase;
            font-size: 14px;
        }

        .table.dark-mode th {
            background-color: #666;
            color: #f4f4f4;
        }

        .table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table.dark-mode tr:nth-child(even) {
            background-color: #555;
        }

        .table tr:hover {
            background-color: #e9ecef;
        }

        .table.dark-mode tr:hover {
            background-color: #666;
        }

        .table.dark-mode td {
            color: #f4f4f4;
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

        .dark-mode .dropdown {
            background-color: #444;
        }

        .dark-mode .dropdown a {
            color: #f4f4f4;
        }

        .dark-mode .dropdown a:hover {
            background-color: #666;
        }

        .actions {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .actions button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
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
                color: black;
            }
        }
    </style>
</head>
<body>


<div class="header">
    <a href='accueil.php'><img src="awb logo desktop.png" alt="Attijariwafa Bank Logo"></a>
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
    <h1>Liste des tâches terminées :</h1>
    <div class="actions">
    <form method="post">
        <button type="submit" name="download_pdf" class="btn btn-primary">
            <img src="pdf.jpg" alt="Ajouter" style="width: 20px; height: 20px; vertical-align: middle;">
            Télécharger PDF
        </button>
    </form>
</div>

    <div class="table-container">
        <table id="taskTable" class="table">
            <thead>
            <tr>
                <th>Nom de l'employé</th>
                <th>Email de l'employé</th>
                <th>Matricule de l'employé</th>
                <th>Titre de la tâche</th>
                <th>Description de la tâche</th>
                <th>Date d'archivage</th>
                <th>Statut</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($queryResult && mysqli_num_rows($queryResult) > 0) {
                while ($line = mysqli_fetch_assoc($queryResult)) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($line['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($line['employee_email']); ?></td>
                        <td><?php echo htmlspecialchars($line['employee_matricule']); ?></td>
                        <td><?php echo htmlspecialchars($line['task_title']); ?></td>
                        <td><?php echo htmlspecialchars($line['task_description']); ?></td>
                        <td><?php echo htmlspecialchars($line['archived_at']); ?></td>
                        <td><?php echo htmlspecialchars($line['task_status']); ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7">Aucune tâche trouvée.</td>
                </tr>
                <?php
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            document.querySelector('.header').classList.add('dark-mode');
            document.querySelector('.sidebar').classList.add('dark-mode');
            document.querySelector('.content').classList.add('dark-mode');
            document.querySelector('.table-container').classList.add('dark-mode');
            var tables = document.querySelectorAll('.table');
            tables.forEach(function(table) {
                table.classList.add('dark-mode');
            });
            var tds = document.querySelectorAll('.table td');
            tds.forEach(function(td) {
                td.classList.add('dark-mode');
            });
        }
    });

    function toggleDropdown() {
        document.getElementById("dropdown").classList.toggle("show");
    }

    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        document.querySelector('.header').classList.toggle('dark-mode');
        document.querySelector('.sidebar').classList.toggle('dark-mode');
        document.querySelector('.content').classList.toggle('dark-mode');
        document.querySelector('.table-container').classList.toggle('dark-mode');
        var tables = document.querySelectorAll('.table');
        tables.forEach(function(table) {
            table.classList.toggle('dark-mode');
        });
        var tds = document.querySelectorAll('.table td');
        tds.forEach(function(td) {
            td.classList.toggle('dark-mode');
        });

        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
        } else {
            localStorage.setItem('darkMode', 'disabled');
        }
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

   
</script>

</body> 
</html>