<?php
include_once 'config/db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (isset($_POST['delete'])) {
      $s = $conn->prepare("DELETE FROM cohorts WHERE id=?");
      $s->bind_param("i", $_POST['id']);
    } elseif (!empty($_POST['id'])) {
      $s = $conn->prepare("UPDATE cohorts SET name=?, size=? WHERE id=?");
      $s->bind_param("sii", $_POST['name'], $_POST['size'], $_POST['id']);
    } else {
      $s = $conn->prepare("INSERT INTO cohorts (name, size) VALUES (?, ?)");
      $s->bind_param("si", $_POST['name'], $_POST['size']);
    }
    $s->execute();
    header('Location: cohorts.php'); exit;
  } catch (Exception $e) {
    $err = 'Could not save, or this cohort has courses.';
  }
}
$edit = null;
if (isset($_GET['edit'])) {
  $s = $conn->prepare("SELECT * FROM cohorts WHERE id=?");
  $s->bind_param("i", $_GET['edit']); $s->execute();
  $edit = $s->get_result()->fetch_assoc();
}
include 'includes/header.php';
$rows = $conn->query("SELECT * FROM cohorts ORDER BY name");
?>
<h1>Student Cohorts</h1>
<?php if ($err) echo "<p style='color:#b83232'>$err</p>"; ?>
<form method="post">
  <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
  <input name="name" placeholder="e.g. Year 2" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
  <input name="size" type="number" min="1" placeholder="Number of students" value="<?= $edit['size'] ?? '' ?>" required>
  <button><?= $edit ? 'Save Changes' : 'Add Cohort' ?></button>
  <?php if ($edit): ?><a class="btn" href="cohorts.php">Cancel</a><?php endif; ?>
</form>
<br>
<table>
  <tr><th>Name</th><th>Size</th><th>Action</th></tr>
  <?php while ($r = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= $r['size'] ?></td>
    <td>
      <a class="btn" href="cohorts.php?edit=<?= $r['id'] ?>">Edit</a>
      <form method="post" onsubmit="return confirm('Delete this cohort?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button name="delete" class="danger">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>