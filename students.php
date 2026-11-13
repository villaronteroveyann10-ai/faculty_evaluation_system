<?php
/**
 * students.php - READ (SELECT)
 * Lists all students with how many evaluations they submitted.
 */
require_once 'includes/init.php';

$rows = $conn->query(
    "SELECT s.*, COUNT(e.eval_id) AS total_evals
     FROM students s
     LEFT JOIN evaluations e ON e.student_id = s.student_id
     GROUP BY s.student_id
     ORDER BY s.full_name"
)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Students';
$active = 'students';
require 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h2 class="mb-0">Students</h2>
  <a href="student_form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Student</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr><th>Student No.</th><th>Name</th><th>Course</th><th>Year</th><th class="text-center">Evals</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['student_no']) ?></td>
          <td><?= e($r['full_name']) ?></td>
          <td><?= e($r['course']) ?></td>
          <td><?= e($r['year_level']) ?></td>
          <td class="text-center"><?= (int)$r['total_evals'] ?></td>
          <td class="text-end text-nowrap">
            <a href="student_form.php?id=<?= (int)$r['student_id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
            <form action="student_delete.php" method="post" class="d-inline"
                  onsubmit="return confirm('Delete this student? Their evaluations will also be deleted.');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$r['student_id'] ?>">
              <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No students yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
