<?php

declare(strict_types=1);

function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "titip";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        http_response_code(500);
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
