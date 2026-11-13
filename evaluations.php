<?php
/**
 * evaluations.php - READ (SELECT)
 * Lists all evaluations with optional search and faculty filter.
 */
require_once 'includes/init.php';

$q   = trim($_GET['q'] ?? '');
$fid = (int)($_GET['faculty_id'] ?? 0);

// Build the WHERE clause only with the filters the user used
$where  = [];
$types  = '';
$params = [];

if ($q !== '') {
    $where[] = "(f.full_name LIKE ? OR s.full_name LIKE ? OR e.subject LIKE ?)";
    $like = '%' . $q . '%';
    $types .= 'sss';
    array_push($params, $like, $like, $like);
}
if ($fid > 0) {
    $where[] = "e.faculty_id = ?";
    $types .= 'i';
    $params[] = $fid;
}

$sql = "SELECT e.*, f.full_name AS faculty_name, s.full_name AS student_name
        FROM evaluations e
        JOIN faculty f  ON e.faculty_id = f.faculty_id
        JOIN students s ON e.student_id = s.student_id"
     . ($where ? " WHERE " . implode(' AND ', $where) : '')
     . " ORDER BY e.date_evaluated DESC, e.eval_id DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$faculty_list = $conn->query("SELECT faculty_id, full_name FROM faculty ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Evaluations';
$active = 'evaluations';
require 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
  <h2 class="mb-0">Evaluations</h2>
  <a href="evaluation_form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Evaluation</a>
</div>

<!-- Search / filter -->
<form class="row g-2 mb-3" method="get">
  <div class="col-md-5">
    <input type="text" name="q" class="form-control" placeholder="Search faculty, student or subject" value="<?= e($q) ?>">
  </div>
  <div class="col-md-4">
    <select name="faculty_id" class="form-select">
      <option value="0">All faculty</option>
      <?php foreach ($faculty_list as $f): ?>
        <option value="<?= (int)$f['faculty_id'] ?>" <?= $fid === (int)$f['faculty_id'] ? 'selected' : '' ?>>
          <?= e($f['full_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3 d-flex gap-2">
    <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-search"></i> Search</button>
    <a href="evaluations.php" class="btn btn-outline-secondary">Reset</a>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>Faculty</th><th>Student</th><th>Subject</th>
          <th class="text-center">Teaching</th><th class="text-center">Comm.</th>
          <th class="text-center">Punctual</th><th class="text-center">Prof.</th>
          <th>Average</th><th>Date</th><th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r):
          $avg = average_rating($r['teaching_skill'], $r['communication'], $r['punctuality'], $r['professionalism']); ?>
        <tr>
          <td><?= e($r['faculty_name']) ?></td>
          <td><?= e($r['student_name']) ?></td>
          <td>
            <?= e($r['subject']) ?>
            <?php if (!empty($r['comments'])): ?>
              <i class="bi bi-chat-left-text text-muted ms-1" title="<?= e($r['comments']) ?>"></i>
            <?php endif; ?>
          </td>
          <td class="text-center"><?= (int)$r['teaching_skill'] ?></td>
          <td class="text-center"><?= (int)$r['communication'] ?></td>
          <td class="text-center"><?= (int)$r['punctuality'] ?></td>
          <td class="text-center"><?= (int)$r['professionalism'] ?></td>
          <td><span class="badge text-bg-<?= rating_color($avg) ?>"><?= number_format($avg, 2) ?></span></td>
          <td class="text-nowrap"><?= e(date('M d, Y', strtotime($r['date_evaluated']))) ?></td>
          <td class="text-end text-nowrap">
            <a href="evaluation_form.php?id=<?= (int)$r['eval_id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i> Edit</a>
            <form action="evaluation_delete.php" method="post" class="d-inline"
                  onsubmit="return confirm('Delete this evaluation?');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$r['eval_id'] ?>">
              <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="10" class="text-center text-muted py-4">No evaluations found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
