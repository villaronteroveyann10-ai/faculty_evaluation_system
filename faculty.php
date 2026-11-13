<?php
/**
 * faculty.php - READ (SELECT)
 * Lists all faculty members with their evaluation count.
 */
require_once 'includes/init.php';

$rows = $conn->query(
    "SELECT f.*, COUNT(e.eval_id) AS total_evals
     FROM faculty f
     LEFT JOIN evaluations e ON e.faculty_id = f.faculty_id
     GROUP BY f.faculty_id
     ORDER BY f.full_name"
)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Faculty';
$active = 'faculty';
require 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h2 class="mb-0">Faculty</h2>
  <a href="faculty_form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Faculty</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr><th>Name</th><th>Department</th><th>Position</th><th>Email</th><th class="text-center">Evals</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['full_name']) ?></td>
          <td><?= e($r['department']) ?></td>
          <td><?= e($r['position']) ?></td>
          <td><?= e($r['email']) ?></td>
          <td class="text-center"><?= (int)$r['total_evals'] ?></td>
          <td class="text-end text-nowrap">
            <a href="faculty_form.php?id=<?= (int)$r['faculty_id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
            <form action="faculty_delete.php" method="post" class="d-inline"
                  onsubmit="return confirm('Delete this faculty member? All of their evaluations will also be deleted.');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$r['faculty_id'] ?>">
              <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No faculty yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
