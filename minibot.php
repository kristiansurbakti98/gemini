<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');

// ================= [ KONFIGURASI ] =================
// Pastikan Query ID ini diambil paling baru dari Telegram Desktop
$query = "query_id=AAHu7tcdAwAAAO7u1x0QwfED&user=%7B%22id%22%3A6943141614%2C%22first_name%22%3A%22.%22%2C%22last_name%22%3A%22%22%2C%22username%22%3A%22taksrlamanya%22%2C%22language_code%22%3A%22en%22%2C%22allows_write_to_pm%22%3Atrue%2C%22photo_url%22%3A%22https%3A%5C%2F%5C%2Ft.me%5C%2Fi%5C%2Fuserpic%5C%2F320%5C%2FIpkB25hdzCGgDMPwnpRWwWbhRxkORXm_JwltMVcrrrfyV6X7PYz9y70fHgBjKtii.svg%22%7D&auth_date=1768193391&signature=NpDpngw-PzlRfmV8_-nDx9pnSdelLEfc80qBw1ftlQlBN5tfXdyVILTEfpB1ZGBWqeRr0txzH9sM-sj_lW14AA&hash=644ef919595ddf9761d394dbb6bc43a76c93c7cc0f118af59116f8620ef24764"; 
// ===================================================

const hijau  = "\033[0;32m";
const putih  = "\033[0;37m";
const kuning = "\033[0;33m";
const merah  = "\033[0;31m";
const cyan   = "\033[0;36m";
const reset  = "\033[0m";

function request($url, $method = 'GET', $data = [], $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Cookie handling menggunakan path absolut untuk stabilitas sesi
    $cookie_file = __DIR__ . '/gemi_perfect.txt';
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function timer($detik, $pesan) {
    for ($i = $detik; $i > 0; $i--) {
        echo "\r" . putih . $pesan . " " . kuning . $i . " detik... " . reset;
        sleep(1);
    }
    echo "\r" . str_repeat(" ", 85) . "\r";
}

// User-Agent yang konsisten dengan browser Telegram Desktop
$ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";
$base_h = [
    "Content-Type: application/json",
    "X-Telegram-Init-Data: $query",
    "User-Agent: $ua",
    "Accept: application/json",
    "Origin: https://gemifaucet-backend-production.up.railway.app",
    "Referer: https://gemifaucet-backend-production.up.railway.app/",
    "Sec-Fetch-Dest: empty",
    "Sec-Fetch-Mode: cors",
    "Sec-Fetch-Site: same-origin"
];

echo cyan . "=== GemiFaucet Perfect Session Sync ===\n" . reset;

// 1. LOGIN & BINDING (Langkah krusial untuk token awal)
$login = request("https://gemifaucet-backend-production.up.railway.app/api/auth/telegram", "POST", ["initData" => $query], $base_h);

if (isset($login['token'])) {
    $token = $login['token'];
    $auth_h = $base_h;
    $auth_h[] = "Authorization: Bearer " . $token;

    // Jabat tangan sesi agar profil tersinkron (Menghindari Saldo 0)
    request("https://gemifaucet-backend-production.up.railway.app/api/auth/bot-status", "GET", [], $auth_h);
    
    while (true) {
        // AMBIL DATA PROFIL TERBARU
        $profile = request("https://gemifaucet-backend-production.up.railway.app/api/auth/profile", "GET", [], $auth_h);
        $userData = $profile['user'] ?? $profile;
        $saldo = $userData['balance'] ?? $userData['points'] ?? 0;
        
        echo "\n" . putih . "[+] Akun: " . cyan . ($userData['firstName'] ?? 'User') . reset . " | Saldo Saat Ini: " . hijau . $saldo . reset . "\n";

        // LANGKAH 1: START
        echo "[*] Memulai Siklus (/start)... ";
        request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/start", "POST", ["initData" => $query], $auth_h);
        echo hijau . "Done\n" . reset;

        // LANGKAH 2: HEARTBEAT (Menjaga keaktifan sesi)
        for ($i = 1; $i <= 2; $i++) {
            echo "[*] Heartbeat $i... ";
            request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/heartbeat", "POST", [], $auth_h);
            echo hijau . "Sent\n" . reset;
            timer(40, "[#] Mengumpulkan Energi Sesi");
        }

        // LANGKAH 3: SINKRONISASI AKHIR (PENTING sebelum klaim)
        echo "[*] Sinkronisasi Sesi Terakhir... ";
        request("https://gemifaucet-backend-production.up.railway.app/api/auth/bot-status", "GET", [], $auth_h);
        echo hijau . "OK\n" . reset;
        sleep(5);

        // LANGKAH 4: COMPLETE CYCLE (Penambahan Saldo)
        echo "[*] Mencoba Klaim Saldo Akhir... ";
        $complete = request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/complete-cycle", "POST", [], $auth_h);

        if (isset($complete['newBalance']) || isset($complete['balance'])) {
            $new_saldo = $complete['newBalance'] ?? $complete['balance'];
            echo hijau . "SUKSES! Saldo Bertambah: " . $new_saldo . reset . "\n";
        } else {
            echo merah . "GAGAL KLAIM!\n" . reset;
            echo putih . "[Pesan Server] " . json_encode($complete) . "\n";
        }

        timer(30, "[*] Jeda Antar Siklus (Anti-Ban)");
    }
} else {
    echo merah . "[-] Gagal Login. Query ID Salah atau Expired.\n" . reset;
}
