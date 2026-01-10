<?php

error_reporting(0);
date_default_timezone_set('Asia/Jakarta');

const hijau  = "\033[0;32m";
const putih  = "\033[0;37m";
const merah  = "\033[0;31m";
const cyan   = "\033[0;36m";
const reset  = "\033[0m";

// Masukkan query Anda di sini (Tetap seperti aslinya sesuai permintaan Anda)
$query = "user=%7B%22id%22%3A6943141614%2C%22first_name%22%3A%22.%22%2C%22last_name%22%3A%22%22%2C%22username%22%3A%22taksrlamanya%22%2C%22language_code%22%3A%22id%22%2C%22allows_write_to_pm%22%3Atrue%2C%22photo_url%22%3A%22https%3A%5C%2F%5C%2Ft.me%5C%2Fi%5C%2Fuserpic%5C%2F320%5C%2FIpkB25hdzCGgDMPwnpRWwWbhRxkORXm_JwltMVcrrrfyV6X7PYz9y70fHgBjKtii.svg%22%7D&chat_instance=-8601949613310626623&chat_type=sender&auth_date=1767205626&signature=3ZZ_2icNsraEnhjYxRJlFgc3fCOjmpx_GsZ4O8Ufi6wzwWMkN_9mLf-N4_U2EeXF4iFA3p92RHs2Gqf1JS7lCg&hash=1d1f2a818d1c52996a477ddc5468051581c5c50a33645f4d01ab022b622e459a";

function skibidixxx($url, $method = 'GET', $data = [], $headers = []) {
    $ch = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 60
    ];
    if (strtoupper($method) === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = $data;
    }
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function timer($seconds) {
    for ($i = $seconds; $i > 0; $i--) {
        echo putih . "[!] Menunggu verifikasi server: " . hijau . "$i detik... \r" . reset;
        sleep(1);
    }
    echo "\r" . str_repeat(" ", 50) . "\r";
}

// --- 1. PROSES LOGIN ---
$auth_headers = [
    "host: gemifaucet-backend-production.up.railway.app",
    "user-agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36",
    "accept: application/json, text/plain",
    "content-type: application/json",
    "x-telegram-init-data: " . $query,
    "origin: https://gemifaucet-frontend.vercel.app",
    "referer: https://gemifaucet-frontend.vercel.app/"
];

$login_payload = json_encode([
    "initData" => $query,
    "timezone" => "Asia/Jakarta",
    "timezoneOffset" => 420
]);

echo putih . "[*] Menghubungkan ke API...\n" . reset;
$login_res = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/auth/telegram", "POST", $login_payload, $auth_headers);
$json = json_decode($login_res, true);

if (isset($json["token"])) {
    echo hijau . "[+] Login Berhasil! User: " . $json["user"]["firstName"] . " | Bal: " . $json["user"]["balance"] . "\n" . reset;
    
    $token = $json["token"];
    $action_headers = [
        "authorization: Bearer " . $token,
        "x-telegram-init-data: " . $query,
        "content-type: application/json",
        "user-agent: Mozilla/5.0 (Linux; Android 12; K) AppleWebKit/537.36",
        "origin: https://gemifaucet-frontend.vercel.app",
        "referer: https://gemifaucet-frontend.vercel.app/"
    ];

    // --- 2. START SESSION ---
    $session_payload = json_encode([
        "adNetwork" => "autofaucet_energy",
        "adType" => "rewarded"
    ]);

    echo putih . "[*] Mengambil Session Token...\n" . reset;
    $start_res = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/energy/start-session", "POST", $session_payload, $action_headers);
    $jsonStart = json_decode($start_res, true);

    if (isset($jsonStart["sessionToken"])) {
        timer(11); // Jeda wajib agar klaim valid

        // --- 3. CLAIM ENERGY ---
        $claim_payload = json_encode(["sessionToken" => $jsonStart["sessionToken"]]);
        $claim_res = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/energy/earn", "POST", $claim_payload, $action_headers);
        $jsonCl = json_decode($claim_res, true);

        if (isset($jsonCl["energyAdded"])) {
            echo hijau . "[ SUCCESS ] Energy +" . $jsonCl["energyAdded"] . " | Total Energy: " . $jsonCl["newEnergy"] . "\n" . reset;
        } else {
            echo merah . "[-] Gagal Klaim: " . $claim_res . "\n" . reset;
        }
    } else {
        echo merah . "[-] Gagal mendapatkan Session Token.\n" . reset;
    }

} else {
    echo merah . "[!] Login Gagal. Respon: " . $login_res . "\n" . reset;
}

echo putih . "[*] Selesai. Menutup koneksi...\n" . reset;
