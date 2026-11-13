<?php
/**
 * student_delete.php - DELETE a student (POST only).
 * Their evaluations are removed automatically (ON DELETE CASCADE).
 */
require_once 'includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('students.php');
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    flash('success', 'Student deleted.');
}
redirect('students.php');
