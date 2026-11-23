<?php
include_once 'config/db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (isset($_POST['delete'])) {
      $s = $conn->prepare("DELETE FROM halls WHERE id=?");
      $s->bind_param("i", $_POST['id']);
    } elseif (!empty($_POST['id'])) {
      $s = $conn->prepare("UPDATE halls SET name=?, capacity=? WHERE id=?");
      $s->bind_param("sii", $_POST['name'], $_POST['capacity'], $_POST['id']);
    } else {
      $s = $conn->prepare("INSERT INTO halls (name, capacity) VALUES (?, ?)");
      $s->bind_param("si", $_POST['name'], $_POST['capacity']);
    }
    $s->execute();
    header('Location: halls.php'); exit;
  } catch (Exception $e) {
    $err = 'Could not save, or this hall is in use.';
  }
}
$edit = null;
if (isset($_GET['edit'])) {
  $s = $conn->prepare("SELECT * FROM halls WHERE id=?");
  $s->bind_param("i", $_GET['edit']); $s->execute();
  $edit = $s->get_result()->fetch_assoc();
}
include 'includes/header.php';
$rows = $conn->query("SELECT * FROM halls ORDER BY name");
?>
<h1>Lecture Halls</h1>
<?php if ($err) echo "<p style='color:#b83232'>$err</p>"; ?>
<form method="post">
  <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
  <input name="name" placeholder="Hall name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
  <input name="capacity" type="number" min="1" placeholder="Capacity" value="<?= $edit['capacity'] ?? '' ?>" required>
  <button><?= $edit ? 'Save Changes' : 'Add Hall' ?></button>
  <?php if ($edit): ?><a class="btn" href="halls.php">Cancel</a><?php endif; ?>
</form>
<br>
<table>
  <tr><th>Name</th><th>Capacity</th><th>Action</th></tr>
  <?php while ($r = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= $r['capacity'] ?></td>
    <td>
      <a class="btn" href="halls.php?edit=<?= $r['id'] ?>">Edit</a>
      <form method="post" onsubmit="return confirm('Delete this hall?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button name="delete" class="danger">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>