<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');

// ================= [ KONFIGURASI ] =================
// Tempelkan Query ID terbaru Anda di sini
$query = "query_id=AAHu7tcdAwAAAO7u1x0XVl6_&user=%7B%22id%22%3A6943141614%2C%22first_name%22%3A%22.%22%2C%22last_name%22%3A%22%22%2C%22username%22%3A%22taksrlamanya%22%2C%22language_code%22%3A%22id%22%2C%22allows_write_to_pm%22%3Atrue%2C%22photo_url%22%3A%22https%3A%5C%2F%5C%2Ft.me%5C%2Fi%5C%2Fuserpic%5C%2F320%5C%2FIpkB25hdzCGgDMPwnpRWwWbhRxkORXm_JwltMVcrrrfyV6X7PYz9y70fHgBjKtii.svg%22%7D&auth_date=1768360690&signature=A9wZJlJCRt2wtkfeCM2tBb807bEVuHJXnp431WxiTK49L_-wJV3d41EkXtqvGGvoe4St5oWFbO_j7P1zxVn9CA&hash=9037aa7691df3d8242e62fd7dd1c5a92d19d58ac4fb0edb25de608af2a1959dd"; 
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
    
    // Cookie handling agar sesi terkunci (Sangat Penting!)
    $cookie_file = __DIR__ . '/session.txt';
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

$ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";
$base_h = [
    "Content-Type: application/json",
    "X-Telegram-Init-Data: $query",
    "User-Agent: $ua",
    "Accept: application/json",
    "Origin: https://gemifaucet-backend-production.up.railway.app",
    "Referer: https://gemifaucet-backend-production.up.railway.app/"
];

echo cyan . "=== GemiFaucet Ultimate Worker (GitHub Edition) ===\n" . reset;

// 1. AUTHENTICATION
$login = request("https://gemifaucet-backend-production.up.railway.app/api/auth/telegram", "POST", ["initData" => $query], $base_h);

if (isset($login['token'])) {
    $token = $login['token'];
    $auth_h = $base_h;
    $auth_h[] = "Authorization: Bearer " . $token;

    // Jabat tangan sesi pertama
    request("https://gemifaucet-backend-production.up.railway.app/api/auth/bot-status", "GET", [], $auth_h);

    // Loop terbatas (5 siklus) agar tidak timeout di GitHub Actions
    for ($cycle = 1; $cycle <= 5; $cycle++) {
        echo "\n" . putih . "--- MEMULAI SIKLUS KE-$cycle ---" . reset . "\n";

        // TAHAP 1: CEK PROFIL & SALDO
        $profile = request("https://gemifaucet-backend-production.up.railway.app/api/auth/profile", "GET", [], $auth_h);
        $u = $profile['user'] ?? $profile;
        $saldo = $u['balance'] ?? $u['points'] ?? 0;
        echo "[+] Saldo Saat Ini: " . hijau . $saldo . reset . "\n";

        // TAHAP 2: CEK & ISI ENERGY (Jika Kurang)
        $status = request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/status", "GET", [], $auth_h);
        $energy = $status['energy'] ?? 0;
        echo "[*] Energi: " . kuning . $energy . reset . "\n";

        if ($energy < 10) {
            echo kuning . "[!] Energi rendah, mengisi ulang..." . reset . "\n";
            request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/energy", "POST", [], $auth_h);
            sleep(5);
        }

        // TAHAP 3: START CYCLE
        echo "[*] Menjalankan Start... ";
        request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/start", "POST", ["initData" => $query], $auth_h);
        echo hijau . "OK\n" . reset;

        // TAHAP 4: HEARTBEAT (Simulasi Aktivitas)
        for ($h = 1; $h <= 2; $h++) {
            echo "[*] Mengirim Heartbeat $h... ";
            request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/heartbeat", "POST", [], $auth_h);
            echo hijau . "Sent\n" . reset;
            timer(rand(35, 45), "[#] Menunggu Sesi Selesai");
        }

        // TAHAP 5: SINKRONISASI SESI (Penting!)
        echo "[*] Sinkronisasi Sesi Akhir... ";
        request("https://gemifaucet-backend-production.up.railway.app/api/auth/bot-status", "GET", [], $auth_h);
        echo hijau . "OK\n" . reset;
        sleep(5);

        // TAHAP 6: COMPLETE CYCLE (Klaim Poin)
        echo "[*] Mencoba Klaim Saldo... ";
        $complete = request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/complete-cycle", "POST", [], $auth_h);

        if (isset($complete['newBalance']) || isset($complete['balance'])) {
            $new = $complete['newBalance'] ?? $complete['balance'];
            echo hijau . "SUKSES! Saldo Baru: $new\n" . reset;
        } else {
            echo merah . "GAGAL KLAIM! Respon: " . json_encode($complete) . "\n" . reset;
        }

        timer(15, "[*] Jeda antar siklus");
    }
    echo cyan . "\n[!] 5 Siklus Selesai. GitHub akan berhenti otomatis.\n" . reset;
} else {
    echo merah . "LOGIN GAGAL! Periksa apakah Query ID sudah expired.\n" . reset;
}
