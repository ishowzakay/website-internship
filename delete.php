<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    // Supprimer la tâche
    $sql_delete = "DELETE FROM tasks WHERE task_id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $task_id);
    mysqli_stmt_execute($stmt_delete);

    header('Location: task.php');
    exit();
}
?>