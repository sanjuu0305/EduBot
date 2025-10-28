<?php
// config.php
// Put this in C:\xampp\htdocs\your_site\config.php

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';        // default XAMPP MySQL user has empty password
$db_name = 'topic_listing_db';
$db_port = 3307;

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($mysqli->connect_errno) {
    http_response_code(500);
    die("Database connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

// helper: safe output
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, "UTF-8");
}
?>
