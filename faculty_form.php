<?php
/**
 * faculty_form.php - CREATE (INSERT) and UPDATE for faculty
 * No ?id= -> add new faculty. ?id=3 -> edit faculty number 3.
 */
require_once 'includes/init.php';

$id      = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;

$data = ['full_name' => '', 'department' => '', 'email' => '', 'position' => ''];

if ($is_edit) {
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    if (!$existing) {
        flash('danger', 'Faculty member not found.');
        redirect('faculty.php');
    }
    $data = array_merge($data, $existing);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $data['full_name']  = trim($_POST['full_name'] ?? '');
    $data['department'] = trim($_POST['department'] ?? '');
    $data['email']      = trim($_POST['email'] ?? '');
    $data['position']   = trim($_POST['position'] ?? '');

    if ($data['full_name'] === '')  $errors[] = 'Full name is required.';
    if ($data['department'] === '') $errors[] = 'Department is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (!$errors) {
        $position = $data['position'] === '' ? null : $data['position'];
        if ($is_edit) {
            $stmt = $conn->prepare("UPDATE faculty SET full_name=?, department=?, email=?, position=? WHERE faculty_id=?");
            $stmt->bind_param("ssssi", $data['full_name'], $data['department'], $data['email'], $position, $id);
            $stmt->execute();
            flash('success', 'Faculty member updated.');
        } else {
            $stmt = $conn->prepare("INSERT INTO faculty (full_name, department, email, position) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $data['full_name'], $data['department'], $data['email'], $position);
            $stmt->execute();
            flash('success', 'Faculty member added.');
        }
        redirect('faculty.php');
    }
}

$page_title = $is_edit ? 'Edit Faculty' : 'Add Faculty';
$active = 'faculty';
require 'includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white fw-semibold fs-5"><?= $is_edit ? 'Edit Faculty' : 'Add Faculty' ?></div>
      <div class="card-body">
        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger py-2"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" maxlength="100" required value="<?= e($data['full_name']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Department</label>
            <input type="text" name="department" class="form-control" maxlength="100" required value="<?= e($data['department']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Position</label>
            <input type="text" name="position" class="form-control" maxlength="50" value="<?= e($data['position']) ?>" placeholder="e.g. Instructor">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" maxlength="100" required value="<?= e($data['email']) ?>">
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $is_edit ? 'Update' : 'Save' ?></button>
            <a href="faculty.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
