<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');

// ================= [ KONFIGURASI ] =================
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
    
    // GitHub Action bersifat stateless, file ini akan terhapus tiap sesi berakhir
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

$ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";
$base_h = [
    "Content-Type: application/json",
    "X-Telegram-Init-Data: $query",
    "User-Agent: $ua",
    "Accept: application/json",
    "Origin: https://gemifaucet-backend-production.up.railway.app",
    "Referer: https://gemifaucet-backend-production.up.railway.app/",
    "Sec-Fetch-Site: same-origin"
];

echo cyan . "=== GemiFaucet GitHub Cloud Worker ===\n" . reset;

$login = request("https://gemifaucet-backend-production.up.railway.app/api/auth/telegram", "POST", ["initData" => $query], $base_h);

if (isset($login['token'])) {
    $token = $login['token'];
    $auth_h = $base_h;
    $auth_h[] = "Authorization: Bearer " . $token;

    // Jalankan 3 siklus per satu kali trigger GitHub Action agar tidak timeout
    for ($cycle = 1; $cycle <= 3; $cycle++) {
        echo "\n" . putih . "--- SIKLUS $cycle ---" . reset . "\n";
        
        $profile = request("https://gemifaucet-backend-production.up.railway.app/api/auth/profile", "GET", [], $auth_h);
        echo "[+] Saldo: " . hijau . ($profile['user']['balance'] ?? 0) . reset . "\n";

        request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/start", "POST", ["initData" => $query], $auth_h);
        
        echo "[*] Simulasi Heartbeat (Menunggu 80 detik)...\n";
        sleep(40);
        request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/heartbeat", "POST", [], $auth_h);
        sleep(40);
        request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/heartbeat", "POST", [], $auth_h);

        // Sinkronisasi Sesi sebelum klaim
        request("https://gemifaucet-backend-production.up.railway.app/api/auth/bot-status", "GET", [], $auth_h);
        
        $complete = request("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/complete-cycle", "POST", [], $auth_h);

        if (isset($complete['newBalance'])) {
            echo hijau . "SUKSES! Saldo Baru: " . $complete['newBalance'] . reset . "\n";
        } else {
            echo merah . "GAGAL KLAIM SIKLUS INI\n" . reset;
        }
    }
} else {
    echo merah . "LOGIN GAGAL! Periksa Query ID.\n" . reset;
}
