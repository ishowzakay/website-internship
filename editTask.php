<?php
include 'db.php';
session_start();

// Inclure PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    // Fetch task information
    $sql_task = "SELECT * FROM tasks WHERE task_id = ?";
    $stmt_task = mysqli_prepare($conn, $sql_task);
    mysqli_stmt_bind_param($stmt_task, "i", $task_id);
    mysqli_stmt_execute($stmt_task);
    $result_task = mysqli_stmt_get_result($stmt_task);
    $task = mysqli_fetch_assoc($result_task);

    if (!$task) {
        echo "Tâche non trouvée.";
        exit();
    }

    $sql_users = "SELECT employee_id, full_name, email FROM employees";
    $result_users = mysqli_query($conn, $sql_users);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $selected_employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
        $task_title = mysqli_real_escape_string($conn, $_POST['task_title']);
        $task_description = mysqli_real_escape_string($conn, $_POST['task_description']);
        $task_status = mysqli_real_escape_string($conn, $_POST['task_status']);
        $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);

        // Update task information
        $sql_update = "UPDATE tasks SET employee_id = ?, task_title = ?, task_description = ?, task_status = ?, due_date = ? WHERE task_id = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "issssi", $selected_employee_id, $task_title, $task_description, $task_status, $due_date, $task_id);

        if (mysqli_stmt_execute($stmt_update)) {
            // Fetch the employee email
            $sql_email = "SELECT email FROM employees WHERE employee_id = ?";
            $stmt_email = mysqli_prepare($conn, $sql_email);
            mysqli_stmt_bind_param($stmt_email, "i", $selected_employee_id);
            mysqli_stmt_execute($stmt_email);
            $result_email = mysqli_stmt_get_result($stmt_email);
            $employee = mysqli_fetch_assoc($result_email);

            // Send email notification
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'elcourtzd@gmail.com'; 
                $mail->Password = 'czvn fbhr ezki oztn'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
            
                //Recipients
                $mail->setFrom('elcourtzd@gmail.com', 'attijariwafa bank');
                $mail->addAddress($employee['email']); // Add a recipient
            
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Mise à jour de la tâche';
                $mail->Body    = "La tâche <strong>$task_title</strong> a été mise à jour.<br><br>
                                  <strong>Description:</strong> $task_description<br>
                                  <strong>Statut:</strong> $task_status<br>
                                  <strong>Date d'échéance:</strong> $due_date";
            
                $mail->send();
                header('Location: task.php');
                exit();
            } catch (Exception $e) {
                echo "Erreur lors de l'envoi de l'email: {$mail->ErrorInfo}";
            }
            
        } else {
            echo "Erreur lors de la modification de la tâche: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt_update);
    }
} else {
    echo "ID de la tâche non fourni.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Tâche</title>
    <style>
        /* Réinitialisation de certains styles par défaut */
        body, h1, form, input, select, textarea, label, button {
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
        .task-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
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

        input[type="text"], input[type="date"], select, textarea {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            width: calc(100% - 22px);
        }

        textarea {
            height: 100px;
            resize: vertical;
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

            .task-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="task.php"><img src="awb logo desktop.png" alt="Attijariwafa Bank Logo"></a>
        </div>
    </header>
    <main>
        <div class="task-container">
            <h1>Modifier une Tâche</h1>
            <form method="POST">
                <label for="employee_id">Nom de l'employé</label>
                <select name="employee_id" id="employee_id" required>
                    <?php
                    if ($result_users && mysqli_num_rows($result_users) > 0) {
                        while ($user = mysqli_fetch_assoc($result_users)) {
                            echo '<option value="' . htmlspecialchars($user['employee_id']) . '"' . ($task['employee_id'] == $user['employee_id'] ? ' selected' : '') . '>' . htmlspecialchars($user['full_name']) . '</option>';
                        }
                    } else {
                        echo '<option value="">Aucun utilisateur trouvé</option>';
                    }
                    ?>
                </select>

                <label for="task_title">Titre de la Tâche</label>
                <input type="text" name="task_title" id="task_title" value="<?php echo htmlspecialchars($task['task_title']); ?>" required>
                
                <label for="task_description">Description de la Tâche</label>
                <textarea name="task_description" id="task_description" required><?php echo htmlspecialchars($task['task_description']); ?></textarea>
                
                <label for="task_status">Statut de la Tâche</label>
                <select name="task_status" id="task_status" required>
                    <option value="todo" <?php if ($task['task_status'] == 'todo') echo 'selected'; ?>>À faire</option>
                    <option value="in_progress" <?php if ($task['task_status'] == 'in_progress') echo 'selected'; ?>>En cours</option>
                    <option value="done" <?php if ($task['task_status'] == 'done') echo 'selected'; ?>>Terminé</option>
                </select>
                
                <label for="due_date">Date d'échéance</label>
                <input type="date" name="due_date" id="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>" required>
                
                <button type="submit" name="submit">Confirmer</button>
            </form>
        </div>
    </main>
</body>
</html>
