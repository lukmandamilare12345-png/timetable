<?php
include 'includes/header.php';

function count_rows($conn, $table) {
    return $conn->query("SELECT COUNT(*) AS c FROM $table")->fetch_assoc()['c'];
}
?>
<h1>Dashboard</h1>
<div class="cards">
    <div class="card"><h2><?=count_rows($conn, 'courses') ?></h2><p>Courses</p></div>
    <div class="card"><h2><?=count_rows($conn, 'lecturers') ?></h2><p>Lecturers</p></div>
    <div class="card"><h2><?=count_rows($conn, 'halls') ?></h2><p>Lecture Halls</p></div>
    <div class="card"><h2><?=count_rows($conn, 'timetable') ?></h2><p>Scheduled Lectures</p></div>
</div>
<?php include 'includes/footer.php'; ?>