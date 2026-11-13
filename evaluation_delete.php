<?php
/**
 * evaluation_delete.php - DELETE
 * Receives a POST request from the Delete button (with CSRF token).
 */
require_once 'includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('evaluations.php');
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM evaluations WHERE eval_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    flash('success', 'Evaluation deleted.');
}
redirect('evaluations.php');
