<?php
include 'db.php';
include 'vendor/autoload.php'; // Assurez-vous que le chemin de PHPMailer est correct

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

$sql = "SELECT user_id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: login.php');
    exit();
}

$row = $result->fetch_assoc();
$admin_id = $row['user_id'];

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT tasks.*, employees.full_name AS employee_name, employees.email AS employee_email FROM tasks INNER JOIN employees ON tasks.employee_id = employees.employee_id";
$filters = [];
$params = [];

if ($status_filter) {
    $filters[] = "tasks.task_status = ?";
    $params[] = $status_filter;
}

if ($search_query) {
    $filters[] = "(tasks.task_title LIKE ? OR employees.full_name LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($filters) {
    $query .= " WHERE " . implode(" AND ", $filters);
}

$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}

$stmt->execute();
$queryResult = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_ids'])) {
    $taskIds = $_POST['task_ids'];
    
    foreach ($taskIds as $taskId) {
        $sql = "SELECT tasks.*, employees.full_name AS employee_name, employees.email AS employee_email FROM tasks INNER JOIN employees ON tasks.employee_id = employees.employee_id WHERE tasks.task_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $taskResult = $stmt->get_result();
        
        if ($taskResult->num_rows === 1) {
            $task = $taskResult->fetch_assoc();
            
            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'elcourtzd@gmail.com'; // Remplacez par votre adresse e-mail
                $mail->Password = 'czvn fbhr ezki oztn'; // Remplacez par votre mot de passe ou mot de passe d'application
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('support@eattijariwafa.ma', 'attijariwafa bank');
                $mail->addAddress($task['employee_email'], $task['employee_name']);

                $mail->isHTML(true);
                $mail->Subject = 'Rappel de tâche';
                $mail->Body = 'Bonjour ' . htmlspecialchars($task['employee_name']) . ',<br><br> Ceci est un rappel pour la tâche suivante : <br>'
                            . 'Titre : ' . htmlspecialchars($task['task_title']) . '<br>'
                            . 'Description : ' . htmlspecialchars($task['task_description']) . '<br>'
                            . 'Date d\'échéance : ' . htmlspecialchars($task['due_date']) . '<br><br>'
                            . 'Merci.';
                $mail->AltBody = 'Bonjour ' . htmlspecialchars($task['employee_name']) . ',\n\n Ceci est un rappel pour la tâche suivante : \n'
                            . 'Titre : ' . htmlspecialchars($task['task_title']) . '\n'
                            . 'Description : ' . htmlspecialchars($task['task_description']) . '\n'
                            . 'Date d\'échéance : ' . htmlspecialchars($task['due_date']) . '\n\n'
                            . 'Merci.';

                $mail->send();
            } catch (Exception $e) {
                echo "Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
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

.search-container {
    position: relative;
    width: 100%;
}

.search-container .form-control {
    padding-right: 40px; /* Laisser de l'espace pour l'icône à droite */
}

.search-icon {
    position: absolute;
    right: 10px; /* Positionner l'icône à droite avec un espacement */
    top: 50%;
    transform: translateY(-50%); /* Centrer verticalement */
    height: 20px; /* Ajuster la taille selon les besoins */
    width: 20px; /* Ajuster la taille selon les besoins */
    cursor: pointer;
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
            color: #f4f4f4;
        }

        .small-header {
            display: none;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
        }

        .small-header .btn {
            margin-right: 10px;
        }

        .dark-mode .small-header {
            background-color: #333;
        }
        .form-group {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    margin-bottom: 15px; /* Ajustez cet espace selon vos besoins */
}

.form-group input[type="text"] {
    flex: 1;
    max-width: 300px; /* Limite la largeur de la barre de recherche */
    margin-right: 10px; /* Espace entre la barre de recherche et le bouton */
    padding: 10px; /* Ajustez la hauteur de la barre de recherche */
    border: 1px solid #ccc;
    border-radius: 4px;
}

.form-group button {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    background-color: #007bff;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.form-group button:hover {
    background-color: #0056b3; /* Couleur du bouton au survol */
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
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
                document.querySelector('.header').classList.add('dark-mode');
                document.querySelector('.sidebar').classList.add('dark-mode');
                document.querySelectorAll('.table').forEach(function(table) {
                    table.classList.add('dark-mode');
                });
                document.querySelector('.small-header').classList.add('dark-mode');
            }
        });

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
            var smallHeader = document.querySelector('.small-header');
            smallHeader.classList.toggle('dark-mode');

            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        }

        function toggleSelectAll(source) {
            checkboxes = document.getElementsByName('task_ids[]');
            for(var i=0, n=checkboxes.length;i<n;i++) {
                checkboxes[i].checked = source.checked;
            }
            toggleSmallHeader();
        }

        function toggleSmallHeader() {
            var checkboxes = document.querySelectorAll('input[name="task_ids[]"]:checked');
            var smallHeader = document.querySelector('.small-header');
            if (checkboxes.length > 0) {
                smallHeader.style.display = "block";
            } else {
                smallHeader.style.display = "none";
            }
        }

        function sendReminder() {
            document.getElementById('taskForm').submit();
        }
    </script>
</head>
<body>
    <div class="header">
        <a href='accueil.php'><img src="awb logo desktop.png" alt="Attijariwafa Bank Logo"></a>
        <div>
            <span class="menu-toggle" onclick="toggleDropdown()" title="menu">☰</span>
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
        <div class="small-header">
            <button class="btn btn-warning" onclick="sendReminder()">Envoyer un rappel</button>
            <button class="btn btn-danger" onclick="confirmDelete(<?= htmlspecialchars($task['id']) ?>)">Supprimer</button>
            </div>
        <h1>Liste des tâches :</h1>
        <form method="get" class="mb-4">
            <label for="status">Filtrer par statut : </label>
            <select name="status" id="status" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="todo" <?php if ($status_filter == 'todo') echo 'selected'; ?>>À faire</option>
                <option value="in_progress" <?php if ($status_filter == 'in_progress') echo 'selected'; ?>>En cours</option>
            </select>
        </form>
        <form method="get" action="">
    <div class="form-group">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Rechercher par titre de tâche ou nom d'employé" value="<?php echo htmlspecialchars($search_query); ?>">
            <div class="input-group-append">
                <span class="input-group-text">
                    <img src="rechercher.png" alt="Rechercher" onclick="this.closest('form').submit()" style="cursor:pointer; height: 20px; width: 20px;">
                </span>
            </div>
        </div>
    </div>
</form>


        <form method="post" id="taskForm">
            <input type="checkbox" onClick="toggleSelectAll(this)" /> Tout sélectionner<br/>
            <a href="ajouterTask.php" class="btn btn-primary">
                <img src="ajoutertache.png" alt="Ajouter" style="width: 20px; height: 20px;"> Ajouter une tâche
            </a>
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nom de l'employé</th>
                            <th>Titre de la tâche</th>
                            <th>Description de la tâche</th>
                            <th>Date d'échéance</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($queryResult && $queryResult->num_rows > 0) {
                            while ($line = $queryResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($line['employee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($line['task_title']); ?></td>
                                    <td><?php echo htmlspecialchars($line['task_description']); ?></td>
                                    <td><?php echo htmlspecialchars($line['due_date']); ?></td>
                                    <td><?php echo htmlspecialchars($line['task_status']); ?></td>
                                    <td>
                                        <a href="editTask.php?id=<?php echo $line['task_id']; ?>" title="modifier">
                                            <img src="edit.png" alt="Modifier" style="width: 20px; height: 20px;">
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $line['task_id']; ?>)" title="supprimer">
                                            <img src="supprimer.png" alt="Supprimer" style="width: 20px; height: 20px;">
                                        </a>
                                    </td>
                                    <td><input type="checkbox" name="task_ids[]" value="<?php echo $line['task_id']; ?>" onclick="toggleSmallHeader()"></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7">Aucune tâche trouvée.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <div id="dropdown" class="dropdown">
        <a href="profil.php">Profil</a>
        <a href="logout.php">Déconnexion</a>
    </div>
</body>
</html>