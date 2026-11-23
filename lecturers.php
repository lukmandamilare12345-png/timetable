<?php
include_once 'config/db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (isset($_POST['delete'])) {
      $s = $conn->prepare("DELETE FROM lecturers WHERE id=?");
      $s->bind_param("i", $_POST['id']);
    } elseif (!empty($_POST['id'])) {
      $s = $conn->prepare("UPDATE lecturers SET name=?, email=? WHERE id=?");
      $s->bind_param("ssi", $_POST['name'], $_POST['email'], $_POST['id']);
    } else {
      $s = $conn->prepare("INSERT INTO lecturers (name, email) VALUES (?, ?)");
      $s->bind_param("ss", $_POST['name'], $_POST['email']);
    }
    $s->execute();
    header('Location: lecturers.php'); exit;
  } catch (Exception $e) {
    $err = 'Could not save, or this lecturer is assigned to a course.';
  }
}
$edit = null;
if (isset($_GET['edit'])) {
  $s = $conn->prepare("SELECT * FROM lecturers WHERE id=?");
  $s->bind_param("i", $_GET['edit']); $s->execute();
  $edit = $s->get_result()->fetch_assoc();
}
include 'includes/header.php';
$rows = $conn->query("SELECT * FROM lecturers ORDER BY name");
?>
<h1>Lecturers</h1>
<?php if ($err) echo "<p style='color:#b83232'>$err</p>"; ?>
<form method="post">
  <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
  <input name="name" placeholder="Full name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
  <input name="email" type="email" placeholder="Email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>">
  <button><?= $edit ? 'Save Changes' : 'Add Lecturer' ?></button>
  <?php if ($edit): ?><a class="btn" href="lecturers.php">Cancel</a><?php endif; ?>
</form>
<br>
<table>
  <tr><th>Name</th><th>Email</th><th>Action</th></tr>
  <?php while ($r = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= htmlspecialchars($r['email']) ?></td>
    <td>
      <a class="btn" href="lecturers.php?edit=<?= $r['id'] ?>">Edit</a>
      <form method="post" onsubmit="return confirm('Delete this lecturer?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button name="delete" class="danger">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>