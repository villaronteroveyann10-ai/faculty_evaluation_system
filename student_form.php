<?php
/**
 * student_form.php - CREATE (INSERT) and UPDATE for students
 * No ?id= -> add new student. ?id=2 -> edit student number 2.
 */
require_once 'includes/init.php';

$id      = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;

$data = ['student_no' => '', 'full_name' => '', 'course' => '', 'year_level' => ''];

if ($is_edit) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    if (!$existing) {
        flash('danger', 'Student not found.');
        redirect('students.php');
    }
    $data = array_merge($data, $existing);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $data['student_no'] = trim($_POST['student_no'] ?? '');
    $data['full_name']  = trim($_POST['full_name'] ?? '');
    $data['course']     = trim($_POST['course'] ?? '');
    $data['year_level'] = trim($_POST['year_level'] ?? '');

    if ($data['student_no'] === '') $errors[] = 'Student number is required.';
    if ($data['full_name'] === '')  $errors[] = 'Full name is required.';
    if ($data['year_level'] !== '' && !in_array((int)$data['year_level'], [1, 2, 3, 4, 5, 6], true)) {
        $errors[] = 'Year level must be between 1 and 6.';
    }

    if (!$errors) {
        $course = $data['course'] === '' ? null : $data['course'];
        $year   = $data['year_level'] === '' ? null : (int)$data['year_level'];
        try {
            if ($is_edit) {
                $stmt = $conn->prepare("UPDATE students SET student_no=?, full_name=?, course=?, year_level=? WHERE student_id=?");
                $stmt->bind_param("sssii", $data['student_no'], $data['full_name'], $course, $year, $id);
                $stmt->execute();
                flash('success', 'Student updated.');
            } else {
                $stmt = $conn->prepare("INSERT INTO students (student_no, full_name, course, year_level) VALUES (?,?,?,?)");
                $stmt->bind_param("sssi", $data['student_no'], $data['full_name'], $course, $year);
                $stmt->execute();
                flash('success', 'Student added.');
            }
            redirect('students.php');
        } catch (mysqli_sql_exception $ex) {
            // student_no is UNIQUE, so a duplicate triggers an error
            $errors[] = 'That student number already exists.';
        }
    }
}

$page_title = $is_edit ? 'Edit Student' : 'Add Student';
$active = 'students';
require 'includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white fw-semibold fs-5"><?= $is_edit ? 'Edit Student' : 'Add Student' ?></div>
      <div class="card-body">
        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger py-2"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Student Number</label>
            <input type="text" name="student_no" class="form-control" maxlength="20" required value="<?= e($data['student_no']) ?>" placeholder="e.g. 2024-0001">
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" maxlength="100" required value="<?= e($data['full_name']) ?>">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label">Course</label>
              <input type="text" name="course" class="form-control" maxlength="50" value="<?= e($data['course']) ?>" placeholder="e.g. BSIT">
            </div>
            <div class="col-md-4">
              <label class="form-label">Year Level</label>
              <input type="number" name="year_level" class="form-control" min="1" max="6" value="<?= e($data['year_level']) ?>">
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $is_edit ? 'Update' : 'Save' ?></button>
            <a href="students.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
