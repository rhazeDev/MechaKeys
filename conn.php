
<?php
$conn = new mysqli("localhost", "root", "", "mechakeys");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>