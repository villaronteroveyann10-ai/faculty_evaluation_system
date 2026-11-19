<?php
/**
 * includes/header.php
 * Opening HTML, Bootstrap 5 CDN and the navigation bar.
 * Set $page_title and $active (dashboard|evaluations|faculty|students)
 * before including this file.
 */
$page_title = $page_title ?? 'Faculty Evaluation System';
$active     = $active ?? '';

function nav_class(string $name, string $active): string
{
    return 'nav-link' . ($name === $active ? ' active fw-semibold' : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($page_title) ?> | Faculty Evaluation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6fb; }
    .stat-card { border: 0; border-radius: 1rem; }
    .stat-card .bi { font-size: 2rem; opacity: .85; }
    .card { border: 0; border-radius: 1rem; box-shadow: 0 2px 12px rgba(30, 41, 59, .06); }
    .table thead th { white-space: nowrap; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="bi bi-mortarboard-fill me-1"></i> Faculty Evaluation
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="<?= nav_class('dashboard', $active) ?>" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="<?= nav_class('evaluations', $active) ?>" href="evaluations.php">Evaluations</a></li>
        <li class="nav-item"><a class="<?= nav_class('faculty', $active) ?>" href="faculty.php">Faculty</a></li>
        <li class="nav-item"><a class="<?= nav_class('students', $active) ?>" href="students.php">Students</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
<?php show_flash(); ?>
