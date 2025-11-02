<?php
session_start();

$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

session_destroy();

if ($user_role === 'customer') {
    header("Location: /mechakeys/client/index.php");
} else {
    header("Location: /mechakeys/login.php");
}
exit;
