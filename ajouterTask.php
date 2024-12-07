<?php
include 'db.php';
include 'vendor/autoload.php'; 
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$sql_employees = "SELECT employee_id, full_name, email FROM employees";
$result_employees = mysqli_query($conn, $sql_employees);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $task_title = mysqli_real_escape_string($conn, $_POST['task_title']);
    $task_description = mysqli_real_escape_string($conn, $_POST['task_description']);
    $task_status = mysqli_real_escape_string($conn, $_POST['task_status']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    
    $today = date('Y-m-d');
    
    if ($due_date < $today) {
        $error_message = "La date d'échéance doit être supérieure à la date d'aujourd'hui.";
    } else {
        $check_user_sql = "SELECT * FROM employees WHERE employee_id = ?";
        $stmt_check = mysqli_prepare($conn, $check_user_sql);
        mysqli_stmt_bind_param($stmt_check, "i", $selected_user_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $employee = mysqli_fetch_assoc($result_check);
            $employee_name = $employee['full_name'];
            $employee_email = $employee['email'];
            
            $sql = "INSERT INTO tasks (employee_id, task_title, task_description, task_status, due_date) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "issss", $selected_user_id, $task_title, $task_description, $task_status, $due_date);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Tâche ajoutée avec succès pour l'employé: " . htmlspecialchars($employee_name);
                
                $mail = new PHPMailer(true);
                
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'elcourtzd@gmail.com'; 
                    $mail->Password = 'czvn fbhr ezki oztn'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
    
     
                    // Recipients
                    $mail->setFrom('no-reply@attijariwafabank.com', 'Task Manager');
                    $mail->addAddress($employee_email, $employee_name);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Nouvelle tache assignee : " . htmlspecialchars($task_title);
                    $mail->Body    = "<p>Bonjour " . htmlspecialchars($employee_name) . ",</p>
                                      <p>Une nouvelle tâche vous a été assignée.</p>
                                      <p><strong>Titre de la tâche :</strong> " . htmlspecialchars($task_title) . "</p>
                                      <p><strong>Description de la tâche :</strong> " . htmlspecialchars($task_description) . "</p>
                                      <p><strong>Statut de la tâche :</strong> " . htmlspecialchars($task_status) . "</p>
                                      <p><strong>Date d'échéance :</strong> " . htmlspecialchars($due_date) . "</p>
                                      <p>Cordialement,<br>Votre équipe de gestion de tâches</p>";
                  

                    $mail->send();
                    $success_message .= " Un email a été envoyé à l'employé.";
                } catch (Exception $e) {
                    $error_message = "La tâche a été ajoutée, mais l'email n'a pas pu être envoyé. Erreur de Mailer: {$mail->ErrorInfo}";
                }
                
                header('Location: task.php');
                exit(); // Ensure no further code runs after the redirect
            } else {
                $error_message = "Une erreur s'est produite: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        } else {
            $error_message = "L'utilisateur sélectionné n'existe pas.";
        }
        
        mysqli_stmt_close($stmt_check);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Tâche</title>
    <style>/* style.css */
/* style.css */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f2f2f2;
    color: #333;
}

header {
    background-color: #e94e1b;
    padding: 20px 0; /* Agrandir le header */
    display: flex;
    justify-content: flex-start; /* Alignement à gauche */
    align-items: center;
    color: white;
    padding: 20px 40px; /* Ajouter plus de padding */
}

header .logo img {
    width: 180px; /* Agrandir l'image du logo */
}

main {
    display: flex;
    justify-content: center;
    align-items: center;
    height: calc(100vh - 100px); /* Ajuster la hauteur pour tenir compte de la taille du header */
    padding: 20px; /* Ajouter du padding pour éviter que le formulaire touche le header */
}

.task-container {
    background-color: #fff;
    padding: 30px; /* Augmenter le padding pour un meilleur espacement intérieur */
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 100%;
}

h1 {
    color: #e94e1b;
    font-size: 24px;
    margin-bottom: 20px;
    text-align: center;
}

form {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 5px;
    color: #333;
}

input[type="text"],
textarea,
select,
input[type="date"] {
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

input[type="text"]:focus,
textarea:focus,
select:focus,
input[type="date"]:focus {
    border-color: #e94e1b;
    outline: none;
}

button {
    background-color: #e94e1b;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #d84315;
}

p {
    text-align: center;
    color: #e94e1b;
}

select option[value="null"] {
    color: #999;
}</style>
</head>
<body>
    <header>
        <div class="logo">
            <a href='task.php'><img src="awb logo desktop.png" alt="Attijariwafa Bank Logo"></a>
        </div>
    </header>
    <main>
        <div class="task-container">
            <h1>Ajouter une Tâche</h1>
            <?php
            if (isset($success_message)) {
                echo '<p style="color: green;">' . htmlspecialchars($success_message) . '</p>';
            } elseif (isset($error_message)) {
                echo '<p style="color: red;">' . htmlspecialchars($error_message) . '</p>';
            }
            ?>
            <form action="" method="post">
                <label for="user_id">Nom de l'employé</label>
                <select name="user_id" id="user_id" required>
                    <option value="">Choisir un employé</option>
                    <?php
                    if ($result_employees && mysqli_num_rows($result_employees) > 0) {
                        while ($row = mysqli_fetch_assoc($result_employees)) {
                            echo '<option value="' . htmlspecialchars($row['employee_id']) . '">' . htmlspecialchars($row['full_name']) . '</option>';
                        }
                    } else {
                        echo '<option value="">Aucun employé trouvé</option>';
                    }
                    ?>
                </select>

                <label for="task_title">Titre de la Tâche</label>
                <input type="text" name="task_title" id="task_title" required>
                
                <label for="task_description">Description de la Tâche</label>
                <textarea name="task_description" id="task_description" required></textarea>
                
                <label for="task_status">Statut de la Tâche</label>
                <select name="task_status" id="task_status" required>
                    <option value="todo">À faire</option>
                    <option value="in_progress">En cours</option>
                    <option value="done">Terminé</option>
                </select>
                
                <label for="due_date">Date d'échéance</label>
                <input type="date" name="due_date" id="due_date" required>
                
                <button type="submit" name="Submit">Ajouter la Tâche</button>
            </form>
        </div>
    </main>
</body>
</html>