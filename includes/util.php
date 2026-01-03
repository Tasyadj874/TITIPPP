<?php

declare(strict_types=1);

function slugify_username(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';
    $value = trim($value, '_');
    return $value;
}

function generate_temp_password(int $length = 10): string
{
    $bytes = random_bytes(max(8, $length));
    $pw = rtrim(strtr(base64_encode($bytes), '+/', 'Aa'), '=');
    return substr($pw, 0, $length);
}

function unique_username(mysqli $conn, string $table, string $base): string
{
    $base = slugify_username($base);
    if ($base === '') {
        $base = 'user';
    }

    $candidate = $base;
    $i = 1;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $candidate);
        $stmt->execute();
        $res = $stmt->get_result();
        $found = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$found) {
            return $candidate;
        }

        $i++;
        $candidate = $base . $i;
    }
}
