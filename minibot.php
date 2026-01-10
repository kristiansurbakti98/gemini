echo putih . "[*] Selesai. Menutup koneksi...\n" . reset;
<?php

error_reporting(0);
date_default_timezone_set('Asia/Jakarta');

// PERBAIKAN: Menghapus tanda kutip ganda yang berlebih di akhir baris ini
$query = getenv('TELEGRAM_QUERY') ?: "user=%7B%22id%22%3A6943141614%2C%22first_name%22%3A%22.%22%2C%22last_name%22%3A%22%22%2C%22username%22%3A%22taksrlamanya%22%2C%22language_code%22%3A%22id%22%2C%22allows_write_to_pm%22%3Atrue%2C%22photo_url%22%3A%22https%3A%5C%2F%5C%2Ft.me%5C%2Fi%5C%2Fuserpic%5C%2F320%5C%2FIpkB25hdzCGgDMPwnpRWwWbhRxkORXm_JwltMVcrrrfyV6X7PYz9y70fHgBjKtii.svg%22%7D&chat_instance=-8601949613310626623&chat_type=sender&auth_date=1767205626&signature=3ZZ_2icNsraEnhjYxRJlFgc3fCOjmpx_GsZ4O8Ufi6wzwWMkN_9mLf-N4_U2EeXF4iFA3p92RHs2Gqf1JS7lCg&hash=1d1f2a818d1c52996a477ddc5468051581c5c50a33645f4d01ab022b622e459a";

const hijau  = "\033[0;32m";
const putih  = "\033[0;37m";
const cyan   = "\033[0;36m";
const merah  = "\033[0;31m";
const kuning = "\033[0;33m";
const reset  = "\033[0m";

function skibidixxx($url, $method = 'GET', $data = [], $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function timer($detik, $pesan) {
    for ($i = $detik; $i > 0; $i--) {
        echo "\r" . putih . $pesan . " " . kuning . $i . " detik... " . reset;
        sleep(1);
    }
    echo "\r" . str_repeat(" ", 60) . "\r";
}

// 1. SETUP HEADERS (Identitas Aplikasi Telegram)
$ua_telegram = "Mozilla/5.0 (Linux; Android 13; SM-G998B Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/120.0.6099.144 Mobile Safari/537.36 Telegram-Android/10.3.2";

$headers = [
    "User-Agent: " . $ua_telegram,
    "Accept: application/json, text/plain, */*",
    "X-Requested-With: org.telegram.messenger",
    "Origin: https://gemifaucet-frontend.vercel.app",
    "Referer: https://gemifaucet-frontend.vercel.app/",
    "Content-Type: application/json"
];

echo putih . "[*] Mencoba Login ke GemiFaucet...\n";
$login_payload = json_encode([
    "initData" => $query,
    "timezone" => "Asia/Jakarta",
    "timezoneOffset" => 420
]);

$login = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/auth/telegram", "POST", $login_payload, $headers);
$json = json_decode($login, true);

if (isset($json["token"])) {
    $token = $json["token"];
    echo hijau . "[+] Login Berhasil!\n";
    echo putih . "[+] User : " . cyan . $json["user"]["firstName"] . "\n";
    echo putih . "[+] Saldo: " . hijau . $json["user"]["balance"] . " Poin\n\n";

    $headers[] = "Authorization: Bearer " . $token;

    // CATATAN: Jika ingin jalankan di GitHub Actions, ganti while(true) jadi satu kali eksekusi saja
    while(true) {
        echo putih . "--- [ SIKLUS MULAI ] ---\n";

        // FITUR 1: KLAIM POIN
        echo putih . "[*] Mencoba Klaim Poin Utama...\n";
        $claim_point = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/faucet/claim", "POST", json_encode([]), $headers);
        $resPoint = json_decode($claim_point, true);

        if (isset($resPoint["balance"])) {
            echo hijau . "[OK] Poin Berhasil Diklaim! Saldo Baru: " . $resPoint["balance"] . "\n";
        } else {
            echo merah . "[!] Faucet Poin sedang Cooldown.\n";
        }

        // FITUR 2: KLAIM ENERGY
        echo putih . "[*] Memulai Sesi Energy...\n";
        $start_session = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/energy/start-session", "POST", json_encode(["adNetwork" => "autofaucet_energy", "adType" => "rewarded"]), $headers);
        $resSession = json_decode($start_session, true);

        if (isset($resSession["sessionToken"])) {
            timer(15, "[!] Menunggu Verifikasi Sesi...");
            
            $claim_energy = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/energy/earn", "POST", json_encode(["sessionToken" => $resSession["sessionToken"]]), $headers);
            $resEnergy = json_decode($claim_energy, true);

            if (isset($resEnergy["energyAdded"])) {
                echo hijau . "[OK] Energy + " . $resEnergy["energyAdded"] . " | Total Energy: " . $resEnergy["newEnergy"] . "\n";
            }
        } else {
            echo merah . "[!] Sesi Energy Gagal/Limit.\n";
        }

        echo putih . "--- [ SIKLUS SELESAI ] ---\n";
        timer(60, "[*] Jeda antar siklus..."); 
        echo "\n";
    }
} else {
    echo merah . "[-] Gagal Login!\n";
    echo $login . "\n"; // Menampilkan respon error dari server
}
