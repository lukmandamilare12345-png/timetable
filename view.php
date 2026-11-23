<?php
include 'includes/header.php';
$days = ['Mon', 'Tue', 'Wed'];
$hours = [8, 9, 10];
$palette = ['#dbeafe', '#dcfce7', '#fef3c7', '#fce7f3', '#ede9fe', '#ffedd5'];

$fl = (int)($_GET['lecturer'] ?? 0);
$fc = (int)($_GET['cohort'] ?? 0);
$fh = (int)($_GET['hall'] ?? 0);
$where = [];
if ($fl) $where[] = "c.lecturer_id=$fl";
if ($fc) $where[] = "c.cohort_id=$fc";
if ($fh) $where[] = "t.hall_id=$fh";

$q = $conn->query("SELECT t.day, t.start_hour, c.code, c.duration_hours, c.cohort_id,
                          h.name AS hall, l.name AS lecturer, k.name AS cohort
  FROM timetable t
  JOIN courses c ON t.course_id = c.id
  JOIN halls h ON t.hall_id = h.id
  JOIN lecturers l ON c.lecturer_id = l.id
  JOIN cohorts k ON c.cohort_id = k.id"
  . ($where ? " WHERE " . implode(' AND ', $where) : ""));

$grid = [];
while ($r = $q->fetch_assoc()) {
  for ($k = $r['start_hour']; $k < $r['start_hour'] + $r['duration_hours']; $k++)
    $grid[$r['day']][$k][] = $r;
}

$lecs = $conn->query("SELECT * FROM lecturers ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$cohs = $conn->query("SELECT * FROM cohorts ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$hals = $conn->query("SELECT * FROM halls ORDER BY name")->fetch_all(MYSQLI_ASSOC);
function colour($id, $p) { return $p[$id % count($p)]; }
?>
<h1>Timetable</h1>

<form method="get" class="filters">
  <select name="lecturer" onchange="this.form.submit()">
    <option value="0">All lecturers</option>
    <?php foreach ($lecs as $x): ?>
      <option value="<?= $x['id'] ?>" <?= $fl == $x['id'] ? 'selected' : '' ?>><?= htmlspecialchars($x['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="cohort" onchange="this.form.submit()">
    <option value="0">All cohorts</option>
    <?php foreach ($cohs as $x): ?>
      <option value="<?= $x['id'] ?>" <?= $fc == $x['id'] ? 'selected' : '' ?>><?= htmlspecialchars($x['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="hall" onchange="this.form.submit()">
    <option value="0">All halls</option>
    <?php foreach ($hals as $x): ?>
      <option value="<?= $x['id'] ?>" <?= $fh == $x['id'] ? 'selected' : '' ?>><?= htmlspecialchars($x['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <a class="btn" href="view.php">Reset</a>
  <button type="button" onclick="window.print()">Print</button>
</form>
<br>
<div class="legend">
  <?php foreach ($cohs as $x): ?>
    <span style="background:<?= colour($x['id'], $palette) ?>"><?= htmlspecialchars($x['name']) ?></span>
  <?php endforeach; ?>
</div>

<table>
  <tr><th>Time</th><?php foreach ($days as $d) echo "<th>$d</th>"; ?></tr>
  <?php foreach ($hours as $h): ?>
  <tr>
    <td><?= sprintf('%02d:00 - %02d:00', $h, $h + 1) ?></td>
    <?php foreach ($days as $d): ?>
    <td>
      <?php foreach ($grid[$d][$h] ?? [] as $e): ?>
        <div class="slot" style="background:<?= colour($e['cohort_id'], $palette) ?>">
          <strong><?= htmlspecialchars($e['code']) ?></strong> · <?= htmlspecialchars($e['hall']) ?><br>
          <small><?= htmlspecialchars($e['lecturer']) ?></small>
        </div>
      <?php endforeach; ?>
    </td>
    <?php endforeach; ?>
  </tr>
  <?php endforeach; ?>
</table>
<?php include 'includes/footer.php'; ?>