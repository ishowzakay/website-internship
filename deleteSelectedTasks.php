<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['task_ids']) && is_array($_POST['task_ids'])) {
        $task_ids = $_POST['task_ids'];

        // Prepare a statement with placeholders
        $placeholders = implode(',', array_fill(0, count($task_ids), '?'));
        $sql = "DELETE FROM tasks WHERE task_id IN ($placeholders)";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bind_param(str_repeat('i', count($task_ids)), ...$task_ids);

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Selected tasks have been deleted.';
        } else {
            $_SESSION['message'] = 'Error deleting selected tasks.';
        }

        $stmt->close();
    }
}

header('Location: task.php');
exit();
?>