<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'titip';

$con = new mysqli($host, $user, $password, $dbname);

if ($con->connect_error) {
    die("Koneksi gagal: " . $con->connect_error);
}
?>
