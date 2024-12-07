<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php'; // Include Composer autoload
include 'db.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Vérifiez si l'utilisateur est authentifié
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$employee_id = $_SESSION['employee_id'];

// Fonction pour mettre à jour le statut d'une tâche et gérer les tâches archivées
function updateTaskStatus($conn, $employee_id, $task_id, $task_status) {
    $conn->begin_transaction();
    try {
        $updateQuery = "UPDATE tasks SET task_status = ? WHERE task_id = ? AND employee_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        if (!$updateStmt) {
            throw new Exception("Query preparation error: " . $conn->error);
        }

        $updateStmt->bind_param('sii', $task_status, $task_id, $employee_id);
        if (!$updateStmt->execute()) {
            throw new Exception("Query execution error: " . $updateStmt->error);
        }

        if ($task_status === 'done') {
            $selectQuery = "SELECT * FROM tasks WHERE task_id = ? AND employee_id = ?";
            $selectStmt = $conn->prepare($selectQuery);
            if (!$selectStmt) {
                throw new Exception("Query preparation error: " . $conn->error);
            }

            $selectStmt->bind_param('ii', $task_id, $employee_id);
            if (!$selectStmt->execute()) {
                throw new Exception("Query execution error: " . $selectStmt->error);
            }

            $result = $selectStmt->get_result();
            if ($result->num_rows > 0) {
                $task = $result->fetch_assoc();

                $insertQuery = "INSERT INTO archive_tasks (task_id, user_id, task_title, task_description, task_status, due_date) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                if (!$insertStmt) {
                    throw new Exception("Query preparation error: " . $conn->error);
                }

                $insertStmt->bind_param(
                    'iissss',
                    $task['task_id'],
                    $task['employee_id'],
                    $task['task_title'],
                    $task['task_description'],
                    $task['task_status'],
                    $task['due_date']
                );
                if (!$insertStmt->execute()) {
                    throw new Exception("Query execution error: " . $insertStmt->error);
                }

                $deleteQuery = "DELETE FROM tasks WHERE task_id = ? AND employee_id = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                if (!$deleteStmt) {
                    throw new Exception("Query preparation error: " . $conn->error);
                }

                $deleteStmt->bind_param('ii', $task_id, $employee_id);
                if (!$deleteStmt->execute()) {
                    throw new Exception("Query execution error: " . $deleteStmt->error);
                }
            }
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        return false;
    }
}

// Fonction pour envoyer une notification de tâche terminée
function sendCompletionNotification($task) {
    $adminEmail = 'z.elcourt9832@uca.ac.ma'; // Remplacer par l'adresse email de l'administrateur

    // Créer une nouvelle instance de PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Paramètres du serveur
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Serveur SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'elcourtzd@gmail.com'; 
        $mail->Password = 'czvn fbhr ezki oztn'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinataires
        $mail->setFrom('noreply@attijariwafaba.com', 'Mailer');
        $mail->addAddress($adminEmail);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = "Tâche terminée: " . $task['task_title'];
        $mail->Body    = "Bonjour,<br><br>L'employé " . $task['full_name'] . " a terminé la tâche suivante:<br><br>";
        $mail->Body   .= "Titre: " . $task['task_title'] . "<br>";
        $mail->Body   .= "Description: " . $task['task_description'] . "<br>";
        $mail->Body   .= "Date d'échéance: " . $task['due_date'] . "<br><br>";
        $mail->Body   .= "Merci.";

        $mail->send();
        error_log("Email envoyé avec succès à $adminEmail");
    } catch (Exception $e) {
        error_log("L'email n'a pas pu être envoyé. Erreur de Mailer: {$mail->ErrorInfo}");
    }
}

// Traiter la requête POST pour mettre à jour le statut d'une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_POST['task_status'])) {
    $task_id = intval($_POST['task_id']);
    $task_status = $_POST['task_status'];

    if (updateTaskStatus($conn, $employee_id, $task_id, $task_status)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Fonction pour envoyer des rappels par e-mail pour les tâches dont l'échéance est demain
function sendTaskReminders($conn) {
    $sql = "SELECT tasks.task_title, tasks.task_description, tasks.due_date, employees.email 
            FROM tasks 
            INNER JOIN employees ON tasks.employee_id = employees.employee_id 
            WHERE tasks.task_status != 'done' AND tasks.due_date = CURDATE() + INTERVAL 1 DAY";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Créer une nouvelle instance de PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Paramètres du serveur
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Serveur SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'elcourtzd@gmail.com'; 
                $mail->Password = 'czvn fbhr ezki oztn'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Destinataires
                $mail->setFrom('noreply@attijariwafaba.com', 'admin');
                $mail->addAddress($row['email']);

                // Contenu
                $mail->isHTML(true);
                $mail->Subject = "Rappel de tâche: " . $row['task_title'];
                $mail->Body    = "Bonjour,<br><br>Ceci est un rappel pour la tâche suivante qui doit être terminée demain:<br><br>";
                $mail->Body   .= "Titre: " . $row['task_title'] . "<br>";
                $mail->Body   .= "Description: " . $row['task_description'] . "<br>";
                $mail->Body   .= "Date d'échéance: " . $row['due_date'] . "<br><br>";
                $mail->Body   .= "Merci de compléter cette tâche à temps.";

                $mail->send();
                error_log("Rappel envoyé avec succès à " . $row['email']);
            } catch (Exception $e) {
                error_log("Le rappel n'a pas pu être envoyé. Erreur de Mailer: {$mail->ErrorInfo}");
            }
        }
    } else {
        error_log("Aucun rappel à envoyer.");
    }
}

// Envoyer des rappels
sendTaskReminders($conn);

// Sélectionner les tâches pour l'employé connecté
$query = "
    SELECT tasks.*, employees.full_name AS employee_name 
    FROM tasks 
    INNER JOIN employees ON tasks.employee_id = employees.employee_id
    WHERE tasks.employee_id = ?
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Erreur de préparation de la requête: " . $conn->error);
    exit('Erreur interne du serveur');
}
$stmt->bind_param('i', $employee_id);
$stmt->execute();
$queryResult = $stmt->get_result();
?> 

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Liste des tâches</title>
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
            background-color: #ffc107;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
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
            background-color: #444;
            color: #f4f4f4;
        }
        .dark-mode .dropdown a {
    color: #f4f4f4; /* Couleur blanche pour le texte du dropdown */
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
    <script type='text/javascript'>
        function confirmDelete(id) {
            var confirmDelete = confirm("Êtes-vous sûr de vouloir supprimer cette tâche?");
            if (confirmDelete) {
                window.location.href = "delete.php?id=" + id;
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
            document.body.classList.toggle('dark-mode');
            var header = document.querySelector('.header');
            header.classList.toggle('dark-mode');
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('dark-mode');
            var tables = document.querySelectorAll('.table');
            tables.forEach(function(table) {
                table.classList.toggle('dark-mode');
            });

            // Save the mode to local storage
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }

        // Check the mode on page load
        window.onload = function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
                var header = document.querySelector('.header');
                header.classList.add('dark-mode');
                var sidebar = document.querySelector('.sidebar');
                sidebar.classList.add('dark-mode');
                var tables = document.querySelectorAll('.table');
                tables.forEach(function(table) {
                    table.classList.add('dark-mode');
                });
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <a href='dash.php'><img src="awb logo desktop.png" alt="Attijariwafa Bank Logo"></a>
        <div>
            <span class="menu-toggle" onclick="toggleDropdown()">☰</span>
            <span class="mode-toggle ml-3" onclick="toggleDarkMode()">🌙</span>
        </div>
    </div>
    <div class="sidebar">
        <a href="dash.php">Accueil</a>
        <a href="listeEmploy2.php">Employés</a>
        <a href="task2.php">Tâches</a>
   
    </div>
    <div class="content">
    <h1>Liste des tâches :</h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Titre de la tâche</th>
            <th>Description de la tâche</th>
            <th>Date d'échéance</th>
            <th>Statut</th>
            <th>Changer le statut</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($queryResult && $queryResult->num_rows > 0) {
            while ($line = $queryResult->fetch_assoc()) {
                ?>
                <tr id="task-<?php echo $line['task_id']; ?>">
                    <td><?php echo htmlspecialchars($line['task_title']); ?></td>
                    <td><?php echo htmlspecialchars($line['task_description']); ?></td>
                    <td><?php echo htmlspecialchars($line['due_date']); ?></td>
                    <td class="task-status"><?php echo htmlspecialchars($line['task_status']); ?></td>
                    <td>
                        <select onchange="changeStatus(<?php echo $line['task_id']; ?>, this.value)">
                            <option value="todo" <?php if ($line['task_status'] == 'todo') echo 'selected'; ?>>En attente</option>
                            <option value="in_progress" <?php if ($line['task_status'] == 'in_progress') echo 'selected'; ?>>En cours</option>
                            <option value="done" <?php if ($line['task_status'] == 'done') echo 'selected'; ?>>Terminé</option>
                        </select>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="5">Aucune tâche trouvée.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>

<div id="dropdown" class="dropdown">
    <a href="profil2.php">Profil</a>
    <a href="logout.php">Déconnexion</a>
</div>

<script>

function changeStatus(taskId, status) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'task2.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    var taskRow = document.getElementById("task-" + taskId);
                    if (status === 'done') {
                        taskRow.remove();
                    } else {
                        var statusCell = taskRow.getElementsByClassName("task-status")[0];
                        statusCell.textContent = status;
                    }
                } else {
                    alert('Erreur lors de la mise à jour du statut de la tâche');
                }
            } else if (xhr.readyState === 4) {
                alert('Erreur de réseau ou de serveur');
            }
        };
        xhr.send('task_id=' + taskId + '&task_status=' + status);
    }
</script>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>