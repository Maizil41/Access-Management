<?php
/*
*******************************************************************************************************************
* Warning!!!, Tidak untuk diperjual belikan!, Cukup pakai sendiri atau share kepada orang lain secara gratis
*******************************************************************************************************************
* Dibuat oleh @Taufik https://t.me/taufik_n_a
*******************************************************************************************************************
* © 2025 AlphaWireless.net By @Taufik
*******************************************************************************************************************
*/
header("Content-Type: application/json");

// Ambil IP pengguna
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Menentukan gateway secara otomatis
if (filter_var($user_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    $ip_parts = explode('.', $user_ip);
    if (count($ip_parts) === 4) {
        $last_octet = (int) $ip_parts[3];
        $gateway_ip = "{$ip_parts[0]}.{$ip_parts[1]}.{$ip_parts[2]}.1";

        // Jika pengguna sudah .1, tetap gunakan IP pengguna
        if ($last_octet === 1) {
            $gateway_ip = $user_ip;
        }
    }
}

// Ambil daftar server dari request
$input = file_get_contents("php://input");
$servers = json_decode($input, true) ?? [];

// Tambahkan gateway ke daftar server
array_unshift($servers, ["name" => "Gateway ($gateway_ip)", "host" => $gateway_ip]);

// Fungsi untuk mengecek status server
function pingServer($host) {
    $pingResult = exec("ping -c 1 -W 1 " . escapeshellarg($host), $output, $status);
    if ($status === 0) {
        preg_match('/time=([\d.]+) ms/', implode(" ", $output), $matches);
        return ["status" => "🟢 Online", "ping" => isset($matches[1]) ? $matches[1] . " ms" : "N/A"];
    } else {
        return ["status" => "🔴 Offline", "ping" => "N/A"];
    }
}

// Ambil status setiap server
$result = [];
foreach ($servers as $server) {
    $status = pingServer($server["host"]);
    $result[] = ["name" => $server["name"], "status" => $status["status"], "ping" => $status["ping"]];
}

echo json_encode($result);
exit;
?>