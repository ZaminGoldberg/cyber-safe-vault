<?php
$host = "127.0.0.1";
$db = "kiber_safe";
$user = "postgres";
$pass = "YOUR_PASSWORD_HERE"; 

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
} catch (PDOException $e) {
    die("Baza xətası: " . $e->getMessage());
}
?>