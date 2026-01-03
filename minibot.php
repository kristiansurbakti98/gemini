<?php

error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
$configFile = "config.json";

const hitam  = "\033[0;30m";
const merah  = "\033[0;31m";
const hijau  = "\033[0;32m";
const kuning = "\033[0;33m";
const biru   = "\033[0;34m";
const cyan   = "\033[0;36m";
const putih  = "\033[0;37m";
const reset  = "\033[0m";

const bg_hitam  = "\033[40m";
const bg_merah  = "\033[41m";
const bg_hijau  = "\033[42m";
const bg_kuning = "\033[43m";
const bg_biru   = "\033[44m";
const bg_ungu   = "\033[45m";
const bg_cyan   = "\033[46m";
const bg_putih  = "\033[47m";

const script_name = "geminifaucet";
const version = "1.0";

function clear() {
    (PHP_OS == "Linux") ? system('clear') : pclose(popen('cls', 'w'));
}


function skibidixxx($url, $method = 'GET', $data = [], $headers = [], &$response_headers = null) {
    while (true) {
        $ch = curl_init();
        $final_headers = [];
        foreach ($headers as $header) {
            $final_headers[] = str_replace('$coki', $coki ?? '', $header);
        }
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 1,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => $final_headers,
            CURLOPT_CONNECTTIMEOUT => 999,
            CURLOPT_TIMEOUT => 999
        ];
        if (strtoupper($method) === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        if ($response) {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers_raw = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            $headers_array = [];
            $header_lines = explode("\r\n", $headers_raw);
            foreach ($header_lines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $headers_array[trim($key)] = trim($value);
                }
            }
            if ($response_headers !== null) {
                $response_headers = $headers_array;
            }

            curl_close($ch);
            return $body;
        } else {
            $error = curl_error($ch);
            curl_close($ch);
            echo "\33[1;" . rand(30, 37) . "mwiwok detok";
            sleep(1);
            echo "\r \r";
            continue;
        }
    }
}
function timer($seconds, $prefix = "[!] please wait") {
    $wait_time = (int)$seconds;
    $frames = ['⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷'];
    $frame_count = count($frames);
    $current_frame = 0;
    $frame_delay = 0.1;
    while ($wait_time > 0) {
        $start_time = microtime(true);
        $frames_shown = 0;
        while ((microtime(true) - $start_time) < 1) {
            $hours = floor($wait_time / 3600);
            $minutes = floor(($wait_time % 3600) / 60);
            $seconds_left = $wait_time % 60;
            $time_formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds_left);
            $spinner = $frames[$current_frame];
            echo putih . $prefix . hijau . " $time_formatted " . putih . $spinner . "\r";
            usleep($frame_delay * 1000000);
            $current_frame = ($current_frame + 1) % $frame_count;
            $frames_shown++;
            if ((microtime(true) - $start_time) >= 1) {
                break;
            }
        }
        $wait_time--;
    }
    echo "\r                                     \r";
}

clear();
$query = "user=%7B%22id%22%3A6943141614%2C%22first_name%22%3A%22.%22%2C%22last_name%22%3A%22%22%2C%22username%22%3A%22taksrlamanya%22%2C%22language_code%22%3A%22id%22%2C%22allows_write_to_pm%22%3Atrue%2C%22photo_url%22%3A%22https%3A%5C%2F%5C%2Ft.me%5C%2Fi%5C%2Fuserpic%5C%2F320%5C%2FIpkB25hdzCGgDMPwnpRWwWbhRxkORXm_JwltMVcrrrfyV6X7PYz9y70fHgBjKtii.svg%22%7D&chat_instance=-8601949613310626623&chat_type=sender&auth_date=1767205626&signature=3ZZ_2icNsraEnhjYxRJlFgc3fCOjmpx_GsZ4O8Ufi6wzwWMkN_9mLf-N4_U2EeXF4iFA3p92RHs2Gqf1JS7lCg&hash=1d1f2a818d1c52996a477ddc5468051581c5c50a33645f4d01ab022b622e459a";


$a = [
	"host: gemifaucet-backend-production.up.railway.app",
	"user-agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36",
	"accept: application/json, text/plain",
	"content-type: application/json",
	"x-telegram-init-data: ".$query,
	"accept-language: id-ID,id;q=0.7",
	"origin: https://gemifaucet-frontend.vercel.app",
	"sec-fetch-site: cross-site",
	"referer: https://gemifaucet-frontend.vercel.app/",
	"priority: u=1, i"
];

$b = [
	"host: gemifaucet-backend-production.up.railway.app",
	"user-agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36",
	"accept: application/json, text/plain",
	"accept-language: id-ID,id;q=0.7",
	"origin: https://gemifaucet-frontend.vercel.app",
	"referer: https://gemifaucet-frontend.vercel.app/",
	"priority: u=1, i"
];

$data = json_encode([
	  "initData" => $query,
	  "timezone" => "Asia/Jakarta",
	  "timezoneOffset" => 420
]);

$login = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/auth/telegram", "POST", $data, $a);
if (strpos($login, "balance") !== false) {
	$json = json_decode($login, true);

	echo putih."login ".cyan.$json["user"]["firstName"]. putih." balance ".cyan.$json["user"]["balance"]."\n\n";

	again:
	while(true) {
	$b[7] = "authorization: Bearer ".$json["token"];
	$b[8] = "x-telegram-init-data: ".$query;
	$b[9] = "content-type: application/json";

	$akun = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/auth/profile", "GET", [], $b);
	$jsonAkun = json_decode($akun, true);

	$data = json_encode([
		  "adNetwork" => "autofaucet_energy",
		  "adType" => "rewarded"
	]);
	$start_ngentod = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/energy/start-session", "POST", $data, $b);
	if (strpos($start_ngentod, "sessionToken") !== false) {
		$jsonStart = json_decode($start_ngentod, true);

		timer(10, "[!] tunggu tod..");
		$data = json_encode([
			  "sessionToken" => $jsonStart["sessionToken"]
		]);
		$claim = skibidixxx("https://gemifaucet-backend-production.up.railway.app/api/autofaucet/energy/earn", "POST", $data, $b);
		if (strpos($claim, "energyAdded") !== false) {
			$jsonCl = json_decode($claim, true);

			echo putih."energyAdded +".hijau.$jsonCl["energyAdded"].putih." newEnergy ".hijau.$jsonCl["newEnergy"]."\n";

		} else {
			// error
			echo $claim."\n";
		}


	} else {
		echo merah."failed get sessionToken!\n";
		goto again;

	}

}	
	
	

} elseif (strpos($login, "error") !== false) {
	$json = json_decode($login, true);
	echo putih."Error: ".merah.$json["error"] ?? "Not found!"."\n";

} else {
	echo putih.$login."\n";
	exit;
}
