<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('AWB3.jpg');
            background-size: cover; 
            background-repeat: no-repeat; 
            background-position: center;
        }
        .container {
            text-align: center;
            background: linear-gradient(to bottom right, #e94e1b, #f6a623);
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 90%;
        }
        .container h1 {
            color: #ffffff;
            font-size: 2em;
            margin-bottom: 20px;
        }
        .container p {
            color: #ffffff;
            font-size: 1.2em;
            margin-bottom: 30px;
        }
        .container button {
            background-color: #000000;
            color: #ffffff;
            border: none;
            padding: 15px 30px;
            font-size: 1em;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .container button:hover {
            background-color: #333333;
        }
        .logo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .logo img {
            height: 50px;
        }
        .profile {
            position: relative;
            display: flex;
            align-items: center;
        }
        .profile img {
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
        }
        .menu-icon {
            font-size: 24px;
            cursor: pointer;
            margin-left: 10px;
            color: #ffffff;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 45px;
            right: 0;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            overflow: hidden;
        }
        .dropdown-menu a {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
        }
        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="awb logo desktop.png" alt="Attijariwafa Bank Logo">
          
        </div>
        <h1>Bienvenue dans le Système de Gestion des Tâches</h1>
        <p>Gérez toutes vos tâches efficacement.</p>
        <button onclick="window.location.href='login.php'">s'authentifier</button>
    </div>
    
   
</body>
</html>