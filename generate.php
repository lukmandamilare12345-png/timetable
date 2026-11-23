<?php
include_once 'config/db.php';

// ---- Settings (change here) ----
$days = ['Mon', 'Tue', 'Wed'];
$firstHour = 8;   // 08:00
$lastHour  = 11;  // lectures must end by 11:00
// --------------------------------

function mark(&$u, $s, $hallId, $d, $h, $val) {
  for ($k = $h; $k < $h + $s['dur']; $k++) {
    if ($val) {
      $u['lec'][$s['lecturer_id']][$d][$k] = 1;
      $u['coh'][$s['cohort_id']][$d][$k] = 1;
      $u['hall'][$hallId][$d][$k] = 1;
    } else {
      unset($u['lec'][$s['lecturer_id']][$d][$k],
            $u['coh'][$s['cohort_id']][$d][$k],
            $u['hall'][$hallId][$d][$k]);
    }
  }
  if ($val) $u['course'][$s['course_id']][$d] = 1;
  else unset($u['course'][$s['course_id']][$d]);
}

function solve($i, $sessions, $halls, &$used, &$plan, $days, $first, $last) {
  if ($i == count($sessions)) return true;
  $s = $sessions[$i];
  foreach ($days as $d) {
    if (isset($used['course'][$s['course_id']][$d])) continue; // max 1 per day per course
    for ($h = $first; $h + $s['dur'] <= $last; $h++) {
      foreach ($halls as $hall) {
        if ($hall['capacity'] < $s['size']) continue;           // rule 4
        $ok = true;
        for ($k = $h; $k < $h + $s['dur']; $k++) {
          if (isset($used['lec'][$s['lecturer_id']][$d][$k]) ||  // rule 1
              isset($used['coh'][$s['cohort_id']][$d][$k]) ||    // rule 2
              isset($used['hall'][$hall['id']][$d][$k])) {       // rule 3
            $ok = false; break;
          }
        }
        if (!$ok) continue;
        mark($used, $s, $hall['id'], $d, $h, true);
        $plan[$i] = [$s['course_id'], $hall['id'], $d, $h];
        if (solve($i + 1, $sessions, $halls, $used, $plan, $days, $first, $last)) return true;
        mark($used, $s, $hall['id'], $d, $h, false);            // backtrack
      }
    }
  }
  return false;
}

$msg = ''; $ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $courses = $conn->query("SELECT c.id, c.lecturer_id, c.cohort_id, c.sessions_per_week, c.duration_hours, h.size
                           FROM courses c JOIN cohorts h ON c.cohort_id = h.id");
  $sessions = [];
  while ($c = $courses->fetch_assoc()) {
    for ($n = 0; $n < $c['sessions_per_week']; $n++) {
      $sessions[] = ['course_id' => $c['id'], 'lecturer_id' => $c['lecturer_id'],
                     'cohort_id' => $c['cohort_id'], 'size' => $c['size'], 'dur' => $c['duration_hours']];
    }
  }
  // most constrained first: biggest cohort, longest sessions
  usort($sessions, fn($a, $b) => [$b['size'], $b['dur']] <=> [$a['size'], $a['dur']]);

  $halls = $conn->query("SELECT * FROM halls ORDER BY capacity")->fetch_all(MYSQLI_ASSOC);
  $used = []; $plan = [];

  if (!$sessions || !$halls) {
    $msg = 'Add courses and halls first.';
  } elseif (solve(0, $sessions, $halls, $used, $plan, $days, $firstHour, $lastHour)) {
    $conn->query("DELETE FROM timetable");
    $st = $conn->prepare("INSERT INTO timetable (course_id, hall_id, day, start_hour) VALUES (?, ?, ?, ?)");
    foreach ($plan as $p) { $st->bind_param("iisi", $p[0], $p[1], $p[2], $p[3]); $st->execute(); }
    $ok = true;
    $msg = 'Timetable generated: ' . count($plan) . ' lectures scheduled with no conflicts.';
  } else {
    $msg = 'No conflict-free timetable exists with the current data. Add halls, days or slots, or reduce sessions.';
  }
}
include 'includes/header.php';
?>
<h1>Generate Timetable</h1>
<?php if ($msg): ?>
  <p style="color:<?= $ok ? '#1a7f37' : '#b83232' ?>"><?= $msg ?></p>
<?php endif; ?>
<p>This replaces any existing timetable.</p>
<form method="post"><button>Generate Timetable</button></form>
<?php if ($ok): ?><p><a class="btn" href="view.php">View Timetable</a></p><?php endif; ?>
<?php include 'includes/footer.php'; ?>