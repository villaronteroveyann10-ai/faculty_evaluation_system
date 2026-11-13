<?php
/**
 * evaluation_form.php - CREATE (INSERT) and UPDATE
 * No ?id= in the URL  -> add a new evaluation
 * ?id=5 in the URL    -> edit evaluation number 5
 */
require_once 'includes/init.php';

$id      = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;

// Default (empty) form values
$data = [
    'faculty_id' => '', 'student_id' => '', 'subject' => '',
    'teaching_skill' => '', 'communication' => '', 'punctuality' => '',
    'professionalism' => '', 'comments' => ''
];

// In edit mode, load the existing record (SELECT)
if ($is_edit) {
    $stmt = $conn->prepare("SELECT * FROM evaluations WHERE eval_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    if (!$existing) {
        flash('danger', 'Evaluation not found.');
        redirect('evaluations.php');
    }
    $data = array_merge($data, $existing);
}

$errors = [];

// Form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // Collect and clean the input
    $data['faculty_id']      = (int)($_POST['faculty_id'] ?? 0);
    $data['student_id']      = (int)($_POST['student_id'] ?? 0);
    $data['subject']         = trim($_POST['subject'] ?? '');
    $data['teaching_skill']  = (int)($_POST['teaching_skill'] ?? 0);
    $data['communication']   = (int)($_POST['communication'] ?? 0);
    $data['punctuality']     = (int)($_POST['punctuality'] ?? 0);
    $data['professionalism'] = (int)($_POST['professionalism'] ?? 0);
    $data['comments']        = trim($_POST['comments'] ?? '');

    // Validate
    if ($data['faculty_id'] <= 0) $errors[] = 'Please choose a faculty member.';
    if ($data['student_id'] <= 0) $errors[] = 'Please choose a student.';
    if ($data['subject'] === '')  $errors[] = 'Subject is required.';
    foreach (['teaching_skill', 'communication', 'punctuality', 'professionalism'] as $field) {
        if ($data[$field] < 1 || $data[$field] > 5) {
            $errors[] = 'All ratings must be between 1 and 5.';
            break;
        }
    }

    if (!$errors) {
        $comments = $data['comments'] === '' ? null : $data['comments'];
        try {
            if ($is_edit) {
                // UPDATE existing record
                $stmt = $conn->prepare(
                    "UPDATE evaluations
                     SET faculty_id=?, student_id=?, subject=?, teaching_skill=?, communication=?,
                         punctuality=?, professionalism=?, comments=?
                     WHERE eval_id=?"
                );
                $stmt->bind_param("iisiiiisi",
                    $data['faculty_id'], $data['student_id'], $data['subject'],
                    $data['teaching_skill'], $data['communication'], $data['punctuality'],
                    $data['professionalism'], $comments, $id);
                $stmt->execute();
                flash('success', 'Evaluation updated successfully.');
            } else {
                // INSERT new record
                $stmt = $conn->prepare(
                    "INSERT INTO evaluations
                     (faculty_id, student_id, subject, teaching_skill, communication, punctuality, professionalism, comments)
                     VALUES (?,?,?,?,?,?,?,?)"
                );
                $stmt->bind_param("iisiiiis",
                    $data['faculty_id'], $data['student_id'], $data['subject'],
                    $data['teaching_skill'], $data['communication'], $data['punctuality'],
                    $data['professionalism'], $comments);
                $stmt->execute();
                flash('success', 'Evaluation added successfully.');
            }
            redirect('evaluations.php');
        } catch (mysqli_sql_exception $ex) {
            $errors[] = 'Could not save the evaluation. Make sure the faculty and student still exist.';
        }
    }
}

// Dropdown options
$faculty_list  = $conn->query("SELECT faculty_id, full_name, department FROM faculty ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
$students_list = $conn->query("SELECT student_id, student_no, full_name FROM students ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);

$page_title = $is_edit ? 'Edit Evaluation' : 'New Evaluation';
$active = 'evaluations';
require 'includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header bg-white fw-semibold fs-5">
        <?= $is_edit ? 'Edit Evaluation' : 'New Evaluation' ?>
      </div>
      <div class="card-body">

        <?php if (!$faculty_list || !$students_list): ?>
          <div class="alert alert-warning">
            You need at least one <a href="faculty_form.php">faculty member</a> and one
            <a href="student_form.php">student</a> before you can add an evaluation.
          </div>
        <?php endif; ?>

        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger py-2"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" novalidate>
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Faculty</label>
              <select name="faculty_id" class="form-select" required>
                <option value="">Select faculty...</option>
                <?php foreach ($faculty_list as $f): ?>
                  <option value="<?= (int)$f['faculty_id'] ?>" <?= (int)$data['faculty_id'] === (int)$f['faculty_id'] ? 'selected' : '' ?>>
                    <?= e($f['full_name']) ?> (<?= e($f['department']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Student</label>
              <select name="student_id" class="form-select" required>
                <option value="">Select student...</option>
                <?php foreach ($students_list as $s): ?>
                  <option value="<?= (int)$s['student_id'] ?>" <?= (int)$data['student_id'] === (int)$s['student_id'] ? 'selected' : '' ?>>
                    <?= e($s['full_name']) ?> (<?= e($s['student_no']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Subject</label>
              <input type="text" name="subject" class="form-control" maxlength="100" required value="<?= e($data['subject']) ?>">
            </div>

            <div class="col-md-6"><label class="form-label">Teaching Skill</label><?= rating_select('teaching_skill', $data['teaching_skill']) ?></div>
            <div class="col-md-6"><label class="form-label">Communication</label><?= rating_select('communication', $data['communication']) ?></div>
            <div class="col-md-6"><label class="form-label">Punctuality</label><?= rating_select('punctuality', $data['punctuality']) ?></div>
            <div class="col-md-6"><label class="form-label">Professionalism</label><?= rating_select('professionalism', $data['professionalism']) ?></div>

            <div class="col-12">
              <label class="form-label">Comments (optional)</label>
              <textarea name="comments" rows="3" class="form-control"><?= e($data['comments']) ?></textarea>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary" <?= (!$faculty_list || !$students_list) ? 'disabled' : '' ?>>
              <i class="bi bi-check-lg"></i> <?= $is_edit ? 'Update' : 'Submit' ?>
            </button>
            <a href="evaluations.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
