<?php
/**
 * db_connect.php
 * Creates the MySQL connection used by every page.
 *
 * LOCAL (XAMPP): keep the defaults below.
 * INFINITYFREE : replace the 4 values with the ones from
 *                Control Panel > MySQL Databases
 *                (host looks like sqlXXX.infinityfree.com).
 */

$host = "localhost";          // InfinityFree: e.g. sql123.infinityfree.com
$user = "root";               // InfinityFree: e.g. if0_12345678
$pass = "";                   // InfinityFree: your database password
$db   = "faculty_eval_db";    // InfinityFree: e.g. if0_12345678_faculty_eval

// Make MySQL errors throw exceptions so we can catch them in the pages
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $ex) {
    die("Database connection failed. Check db_connect.php settings.");
}
