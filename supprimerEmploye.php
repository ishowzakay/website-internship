<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql_delete_tasks = "DELETE FROM tasks WHERE employee_id = ?";
    $stmt_tasks = mysqli_prepare($conn, $sql_delete_tasks);
    if ($stmt_tasks) {
        mysqli_stmt_bind_param($stmt_tasks, 'i', $id);
        mysqli_stmt_execute($stmt_tasks);
    } else {
        $_SESSION['message'] = "Erreur lors de la préparation de la requête de suppression des tâches.";
        header('Location: listeEmploy.php');
        exit();
    }

    $sql_delete_employee = "DELETE FROM employees WHERE employee_id = ?";
    $stmt_employee = mysqli_prepare($conn, $sql_delete_employee);
    if ($stmt_employee) {
        mysqli_stmt_bind_param($stmt_employee, 'i', $id);
        mysqli_stmt_execute($stmt_employee);

        if (mysqli_stmt_affected_rows($stmt_employee) > 0) {
            $_SESSION['message'] = "L'employé a été supprimé avec succès.";
            header('Location: listeEmploy.php');
            exit();
        } else {
            $_SESSION['message'] = "Erreur lors de la suppression de l'employé.";
            header('Location: listeEmploy.php');
            exit();
        }
    } else {
        $_SESSION['message'] = "Erreur lors de la préparation de la requête de suppression de l'employé.";
        header('Location: listeEmploy.php');
        exit();
    }
} else {
    header('Location: listeEmploy.php');
    exit();
}
?>