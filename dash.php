<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Attijariwafa Bank</title>
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

        .resource-section {
            margin: 30px 0;
        }

        .resource-section h2 {
            color: #e94e1b;
        }

        .resource-section .card {
            margin-bottom: 20px;
        }
    </style>
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
    <h1>Bienvenue chez Attijariwafa Bank</h1>

<div id="dropdown" class="dropdown">
    <a href="profil2.php">Profil</a>
    <a href="logout.php">Déconnexion</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        if (localStorage.getItem('darkMode') === 'enabled') {
            enableDarkMode();
        }
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

</body>
</html>
