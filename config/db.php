<?php
$conn = new mysqli("localhost", "root", "", "timetable_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->cconnect_error);
}
?>