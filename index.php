<?php
/**
 * index.php - Dashboard
 * Shows totals, the average rating per faculty (with a Chart.js bar chart)
 * and the most recent evaluations.  (Enhancement feature)
 */
require_once 'includes/init.php';

// ---- Totals (SELECT) -------------------------------------------------
$total_faculty = $conn->query("SELECT COUNT(*) AS n FROM faculty")->fetch_assoc()['n'];
$total_students = $conn->query("SELECT COUNT(*) AS n FROM students")->fetch_assoc()['n'];
$total_evals = $conn->query("SELECT COUNT(*) AS n FROM evaluations")->fetch_assoc()['n'];

$overall = $conn->query(
    "SELECT AVG((teaching_skill + communication + punctuality + professionalism) / 4) AS avg_all
     FROM evaluations"
)->fetch_assoc()['avg_all'];

// ---- Average rating per faculty (GROUP BY) ---------------------------
$ranking = $conn->query(
    "SELECT f.faculty_id, f.full_name, f.department,
            COUNT(e.eval_id) AS total_evals,
            AVG((e.teaching_skill + e.communication + e.punctuality + e.professionalism) / 4) AS avg_rating
     FROM faculty f
     LEFT JOIN evaluations e ON e.faculty_id = f.faculty_id
     GROUP BY f.faculty_id, f.full_name, f.department
     ORDER BY avg_rating DESC, f.full_name ASC"
)->fetch_all(MYSQLI_ASSOC);

// ---- Latest 5 evaluations --------------------------------------------
$recent = $conn->query(
    "SELECT e.*, f.full_name AS faculty_name, s.full_name AS student_name
     FROM evaluations e
     JOIN faculty f  ON e.faculty_id = f.faculty_id
     JOIN students s ON e.student_id = s.student_id
     ORDER BY e.date_evaluated DESC, e.eval_id DESC
     LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

// Data for the chart (only faculty that have at least one evaluation)
$chart_labels = [];
$chart_values = [];
foreach ($ranking as $r) {
    if ($r['total_evals'] > 0) {
        $chart_labels[] = $r['full_name'];
        $chart_values[] = round((float)$r['avg_rating'], 2);
    }
}

$page_title = 'Dashboard';
$active = 'dashboard';
require 'includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <h2 class="mb-0">Dashboard</h2>
  <a href="evaluation_form.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Evaluation</a>
</div>

<!-- Summary cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card stat-card bg-primary text-white h-100"><div class="card-body d-flex justify-content-between align-items-center">
      <div><div class="small text-uppercase">Faculty</div><div class="fs-2 fw-bold"><?= (int)$total_faculty ?></div></div>
      <i class="bi bi-person-workspace"></i>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card bg-success text-white h-100"><div class="card-body d-flex justify-content-between align-items-center">
      <div><div class="small text-uppercase">Students</div><div class="fs-2 fw-bold"><?= (int)$total_students ?></div></div>
      <i class="bi bi-people-fill"></i>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card bg-warning text-dark h-100"><div class="card-body d-flex justify-content-between align-items-center">
      <div><div class="small text-uppercase">Evaluations</div><div class="fs-2 fw-bold"><?= (int)$total_evals ?></div></div>
      <i class="bi bi-clipboard-check-fill"></i>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card stat-card bg-dark text-white h-100"><div class="card-body d-flex justify-content-between align-items-center">
      <div><div class="small text-uppercase">Overall Average</div>
        <div class="fs-2 fw-bold"><?= $overall !== null ? number_format((float)$overall, 2) : '-' ?></div></div>
      <i class="bi bi-star-fill"></i>
    </div></div>
  </div>
</div>

<div class="row g-4">
  <!-- Ranking table -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-white fw-semibold">Faculty Ranking (average rating)</div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr><th>Faculty</th><th>Department</th><th class="text-center">Evals</th><th>Average</th></tr>
          </thead>
          <tbody>
          <?php foreach ($ranking as $r): ?>
            <tr>
              <td><?= e($r['full_name']) ?></td>
              <td><?= e($r['department']) ?></td>
              <td class="text-center"><?= (int)$r['total_evals'] ?></td>
              <td>
                <?php if ($r['total_evals'] > 0):
                    $avg = (float)$r['avg_rating']; ?>
                  <span class="badge text-bg-<?= rating_color($avg) ?>">
                    <?= number_format($avg, 2) ?> &middot; <?= e(rating_label($avg)) ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted">No evaluations yet</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$ranking): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No faculty yet. <a href="faculty_form.php">Add one</a>.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Chart -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-white fw-semibold">Average Rating Chart</div>
      <div class="card-body">
        <?php if ($chart_labels): ?>
          <canvas id="ratingChart" height="260"></canvas>
        <?php else: ?>
          <p class="text-muted mb-0">The chart appears once there is at least one evaluation.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Recent evaluations -->
<div class="card mt-4">
  <div class="card-header bg-white d-flex justify-content-between">
    <span class="fw-semibold">Recent Evaluations</span>
    <a href="evaluations.php" class="small">View all</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Faculty</th><th>Student</th><th>Subject</th><th>Average</th><th>Date</th></tr>
      </thead>
      <tbody>
      <?php foreach ($recent as $r):
          $avg = average_rating($r['teaching_skill'], $r['communication'], $r['punctuality'], $r['professionalism']); ?>
        <tr>
          <td><?= e($r['faculty_name']) ?></td>
          <td><?= e($r['student_name']) ?></td>
          <td><?= e($r['subject']) ?></td>
          <td><span class="badge text-bg-<?= rating_color($avg) ?>"><?= number_format($avg, 2) ?></span></td>
          <td class="text-nowrap"><?= e(date('M d, Y', strtotime($r['date_evaluated']))) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$recent): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No evaluations yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
// Chart.js is loaded only when there is data to show (printed by footer.php)
if ($chart_labels) {
    ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  // Bar chart of average rating per faculty (scale 0 to 5)
  new Chart(document.getElementById('ratingChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($chart_labels) ?>,
      datasets: [{
        label: 'Average rating',
        data: <?= json_encode($chart_values) ?>,
        backgroundColor: 'rgba(13, 110, 253, 0.7)',
        borderRadius: 6
      }]
    },
    options: {
      scales: { y: { min: 0, max: 5, ticks: { stepSize: 1 } } },
      plugins: { legend: { display: false } }
    }
  });
</script>
<?php
    $extra_scripts = ob_get_clean();
}
require 'includes/footer.php';
?>
