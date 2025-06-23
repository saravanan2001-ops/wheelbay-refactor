<?php
session_start(); // Start the session

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");
header("Pragma: no-cache");

// Redirect to login page
header("Location: login.php");
exit();
?>