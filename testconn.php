<?php
$conn = new mysqli("localhost", "root", "", "notepad_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!";
$conn->close();
?>