<?php
include 'db.php';

// Requête SQL pour récupérer le nombre de tâches par statut depuis la table tasks
$sql = "
    SELECT 
        task_status, 
        COUNT(*) as count 
    FROM tasks 
    GROUP BY task_status
";
$result = $conn->query($sql);

$taskCounts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $taskCounts[$row['task_status']] = $row['count'];
    }
}

// Requête SQL pour récupérer le nombre de tâches terminées depuis la table archive_tasks
$sqlArchive = "
    SELECT 
        COUNT(*) as count 
    FROM archive_tasks 
    WHERE task_status = 'done'
";
$resultArchive = $conn->query($sqlArchive);
$completedTasksCount = 0;
if ($resultArchive->num_rows > 0) {
    $row = $resultArchive->fetch_assoc();
    $completedTasksCount = $row['count'];
}

// Requête SQL pour récupérer les tâches par mois et par statut pour le graphique
$sqlGraph = "
    SELECT 
        MONTH(created_at) as month,
        task_status, 
        COUNT(*) as count 
    FROM tasks 
    GROUP BY MONTH(created_at), task_status

    UNION ALL

    SELECT 
        MONTH(archived_at) as month,
        task_status, 
        COUNT(*) as count 
    FROM archive_tasks
    WHERE task_status = 'done'
    GROUP BY MONTH(archived_at), task_status
";
$resultGraph = $conn->query($sqlGraph);

$taskCountsGraph = [];
if ($resultGraph->num_rows > 0) {
    while ($row = $resultGraph->fetch_assoc()) {
        $taskCountsGraph[$row['month']][$row['task_status']] = $row['count'];
    }
}

if (isset($_POST['month'])) {
    $month = intval($_POST['month']);

    // Requête SQL pour récupérer les tâches par jour pour le mois donné
    $sqlDayGraph = "
        SELECT 
            DAY(created_at) as day,
            task_status, 
            COUNT(*) as count 
        FROM tasks 
        WHERE MONTH(created_at) = $month
        GROUP BY DAY(created_at), task_status

        UNION ALL

        SELECT 
            DAY(archived_at) as day,
            task_status, 
            COUNT(*) as count 
        FROM archive_tasks
        WHERE MONTH(archived_at) = $month AND task_status = 'done'
        GROUP BY DAY(archived_at), task_status
    ";
    $resultDayGraph = $conn->query($sqlDayGraph);

    $taskCountsDayGraph = [];
    if ($resultDayGraph->num_rows > 0) {
        while ($row = $resultDayGraph->fetch_assoc()) {
            $taskCountsDayGraph[$row['day']][$row['task_status']] = $row['count'];
        }
    }

    echo json_encode($taskCountsDayGraph);
    exit;
}

// Convertir les données en JSON pour les utiliser dans le JavaScript
$taskCountsJson = json_encode($taskCounts);
$taskCountsGraphJson = json_encode($taskCountsGraph);
$completedTasksJson = json_encode($completedTasksCount);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Attijariwafa Bank</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            transition: background-color 0.3s, color 0.3s;
        }

        .dashboard {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .statistic {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .statistic h2 {
            color: #333;
        }

        .statistic p {
            font-size: 24px;
            color: #e94e1b;
            margin: 0;
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

        .chart-container {
            width: 100%;
            max-width: 600px;
            margin: auto;
        }
    </style>
</head>
<body>

<div class="header">
    <a href="accueil.php"><img src="awb logo desktop.png" alt="Attijariwafa Bank Logo"></a>
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
    <h1>Bienvenue chez Attijariwafa Bank</h1>
    
    <!-- Section pour les statistiques clés des tâches -->
    <div class="dashboard">
        
        <div class="statistic">
            <h2>Tâches en Cours</h2>
            <p id="tasks-in-progress">0</p>
        </div>
        <div class="statistic">
            <h2>Tâches Terminées</h2>
            <p id="completed-tasks">0</p>
        </div>
        <div class="statistic">
            <h2>Tâches en Attente</h2>
            <p id="pending-tasks">0</p>
        </div>
    </div>
    <div class="chart-container">
        <canvas id="projectProgressChart" width="400" height="200"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="dailyProgressChart" width="400" height="200"></canvas>
    </div>
</div>

<div id="dropdown" class="dropdown">
    <a href="profil.php">Profil</a>
    <a href="logout.php">Déconnexion</a>
</div>

<script>
    // Check for saved dark mode preference on page load
    document.addEventListener('DOMContentLoaded', (event) => {
        if (localStorage.getItem('darkMode') === 'enabled') {
            enableDarkMode();
        }

        // Mettre à jour les statistiques clés
        const taskCounts = <?php echo $taskCountsJson; ?>;
        const completedTasksCount = <?php echo $completedTasksJson; ?>;
        document.getElementById('tasks-in-progress').innerText = taskCounts['in_progress'] || 0;
        document.getElementById('completed-tasks').innerText = completedTasksCount;
        document.getElementById('pending-tasks').innerText = taskCounts['todo'] || 0;
    });

    function toggleDropdown() {
        document.getElementById("dropdown").classList.toggle("show");
    }

    function toggleDarkMode() {
        if (document.body.classList.contains('dark-mode')) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }
    }

    function enableDarkMode() {
        document.body.classList.add('dark-mode');
        document.querySelector('.header').classList.add('dark-mode');
        document.querySelector('.sidebar').classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
    }

    function disableDarkMode() {
        document.body.classList.remove('dark-mode');
        document.querySelector('.header').classList.remove('dark-mode');
        document.querySelector('.sidebar').classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Mode sombre
    function toggleDarkMode() {
        document.body.classList.toggle("dark-mode");
        document.querySelector(".header").classList.toggle("dark-mode");
        document.querySelector(".sidebar").classList.toggle("dark-mode");
        var dropdown = document.getElementById("dropdown");
        dropdown.classList.toggle("dark-mode");
    }

    // Menu déroulant
    function toggleDropdown() {
        var dropdown = document.getElementById("dropdown");
        dropdown.classList.toggle("show");
    }

    // Données des tâches par statut
    var taskCounts = <?php echo $taskCountsJson; ?>;
    var completedTasksCount = <?php echo $completedTasksJson; ?>;
    var taskCountsGraph = <?php echo $taskCountsGraphJson; ?>;

    // Mettre à jour les statistiques clés des tâches
    document.getElementById("tasks-in-progress").innerText = taskCounts['in_progress'] || 0;
    document.getElementById("completed-tasks").innerText = completedTasksCount || 0;
    document.getElementById("pending-tasks").innerText = taskCounts['todo'] || 0;

    // Préparer les données pour le graphique de progression du projet
    var monthlyLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var taskStatuses = ["todo", "in_progress", "done"];

    var monthlyData = {
        labels: monthlyLabels,
        datasets: taskStatuses.map(function(status) {
            return {
                label: status.charAt(0).toUpperCase() + status.slice(1),
                data: monthlyLabels.map(function(_, index) {
                    return (taskCountsGraph[index + 1] && taskCountsGraph[index + 1][status]) || 0;
                }),
                backgroundColor: status === "done" ? "rgba(75, 192, 192, 0.2)" :
                                status === "in_progress" ? "rgba(255, 159, 64, 0.2)" :
                                "rgba(255, 99, 132, 0.2)",
                borderColor: status === "done" ? "rgba(75, 192, 192, 1)" :
                                status === "in_progress" ? "rgba(255, 159, 64, 1)" :
                                "rgba(255, 99, 132, 1)",
                borderWidth: 1
            };
        })
    };

    // Initialiser le graphique de progression du projet
    var projectProgressChart = new Chart(document.getElementById("projectProgressChart"), {
        type: 'bar',
        data: monthlyData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            onClick: function(e, elements) {
                if (elements.length > 0) {
                    var monthIndex = elements[0].index + 1;
                    updateDailyProgressChart(monthIndex);
                }
            }
        }
    });

    // Fonction pour mettre à jour le graphique quotidien
    function updateDailyProgressChart(month) {
        $.ajax({
            type: 'POST',
            url: '',
            data: { month: month },
            dataType: 'json',
            success: function(data) {
                var dailyLabels = Array.from({ length: 31 }, (_, i) => i + 1);
                var dailyData = {
                    labels: dailyLabels,
                    datasets: taskStatuses.map(function(status) {
                        return {
                            label: status.charAt(0).toUpperCase() + status.slice(1),
                            data: dailyLabels.map(function(day) {
                                return (data[day] && data[day][status]) || 0;
                            }),
                            backgroundColor: status === "done" ? "rgba(75, 192, 192, 0.2)" :
                                            status === "in_progress" ? "rgba(255, 159, 64, 0.2)" :
                                            "rgba(255, 99, 132, 0.2)",
                            borderColor: status === "done" ? "rgba(75, 192, 192, 1)" :
                                            status === "in_progress" ? "rgba(255, 159, 64, 1)" :
                                            "rgba(255, 99, 132, 1)",
                            borderWidth: 1
                        };
                    })
                };
                var dailyProgressChart = new Chart(document.getElementById("dailyProgressChart"), {
                    type: 'bar',
                    data: dailyData,
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    }
</script>


</body>
</html>