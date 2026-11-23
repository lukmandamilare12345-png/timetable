<?php
include_once 'config/db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (isset($_POST['delete'])) {
      $s = $conn->prepare("DELETE FROM courses WHERE id=?");
      $s->bind_param("i", $_POST['id']);
    } elseif (!empty($_POST['id'])) {
      $s = $conn->prepare("UPDATE courses SET code=?, title=?, lecturer_id=?, cohort_id=?, sessions_per_week=?, duration_hours=? WHERE id=?");
      $s->bind_param("ssiiiii", $_POST['code'], $_POST['title'], $_POST['lecturer_id'], $_POST['cohort_id'], $_POST['sessions'], $_POST['duration'], $_POST['id']);
    } else {
      $s = $conn->prepare("INSERT INTO courses (code, title, lecturer_id, cohort_id, sessions_per_week, duration_hours) VALUES (?, ?, ?, ?, ?, ?)");
      $s->bind_param("ssiiii", $_POST['code'], $_POST['title'], $_POST['lecturer_id'], $_POST['cohort_id'], $_POST['sessions'], $_POST['duration']);
    }
    $s->execute();
    header('Location: courses.php'); exit;
  } catch (Exception $e) {
    $err = 'Could not save or delete this course.';
  }
}
$edit = null;
if (isset($_GET['edit'])) {
  $s = $conn->prepare("SELECT * FROM courses WHERE id=?");
  $s->bind_param("i", $_GET['edit']); $s->execute();
  $edit = $s->get_result()->fetch_assoc();
}
include 'includes/header.php';
$lecturers = $conn->query("SELECT * FROM lecturers ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$cohorts = $conn->query("SELECT * FROM cohorts ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$rows = $conn->query("SELECT c.*, l.name AS lecturer, h.name AS cohort, h.size
  FROM courses c
  JOIN lecturers l ON c.lecturer_id = l.id
  JOIN cohorts h ON c.cohort_id = h.id
  ORDER BY c.code");
?>
<h1>Courses</h1>
<?php if ($err) echo "<p style='color:#b83232'>$err</p>"; ?>
<form method="post">
  <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
  <input name="code" placeholder="Code" value="<?= htmlspecialchars($edit['code'] ?? '') ?>" required>
  <input name="title" placeholder="Course title" value="<?= htmlspecialchars($edit['title'] ?? '') ?>" required>
  <select name="lecturer_id" required>
    <option value="">Lecturer</option>
    <?php foreach ($lecturers as $l): ?>
      <option value="<?= $l['id'] ?>" <?= ($edit['lecturer_id'] ?? 0) == $l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="cohort_id" required>
    <option value="">Cohort</option>
    <?php foreach ($cohorts as $c): ?>
      <option value="<?= $c['id'] ?>" <?= ($edit['cohort_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?> (<?= $c['size'] ?>)</option>
    <?php endforeach; ?>
  </select>
  <input name="sessions" type="number" min="1" max="5" value="<?= $edit['sessions_per_week'] ?? 1 ?>" title="Sessions per week" required>
  <input name="duration" type="number" min="1" max="4" value="<?= $edit['duration_hours'] ?? 1 ?>" title="Hours per session" required>
  <button><?= $edit ? 'Save Changes' : 'Add Course' ?></button>
  <?php if ($edit): ?><a class="btn" href="courses.php">Cancel</a><?php endif; ?>
</form>
<br>
<table>
  <tr><th>Code</th><th>Title</th><th>Lecturer</th><th>Cohort</th><th>Sessions/wk</th><th>Hours</th><th>Action</th></tr>
  <?php while ($r = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= htmlspecialchars($r['code']) ?></td>
    <td><?= htmlspecialchars($r['title']) ?></td>
    <td><?= htmlspecialchars($r['lecturer']) ?></td>
    <td><?= htmlspecialchars($r['cohort']) ?> (<?= $r['size'] ?>)</td>
    <td><?= $r['sessions_per_week'] ?></td>
    <td><?= $r['duration_hours'] ?></td>
    <td>
      <a class="btn" href="courses.php?edit=<?= $r['id'] ?>">Edit</a>
      <form method="post" onsubmit="return confirm('Delete this course?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button name="delete" class="danger">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>