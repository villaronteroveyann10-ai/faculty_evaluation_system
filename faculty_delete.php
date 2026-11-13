<?php
/**
 * faculty_delete.php - DELETE a faculty member (POST only).
 * Their evaluations are removed automatically (ON DELETE CASCADE).
 */
require_once 'includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('faculty.php');
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    flash('success', 'Faculty member deleted.');
}
redirect('faculty.php');
