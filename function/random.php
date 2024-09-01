<?php

// Fungsi untuk menghasilkan string acak
function generateRandomString($type, $length) {
    $characters = '';
    
    switch ($type) {
        case 'hurufangkarandom':
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            break;
        case 'angkarandom':
            $characters = '0123456789';
            break;
        case 'hurufrandom':
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'textnumrandom':
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            break;
        default:
            throw new InvalidArgumentException("Tipe string acak tidak valid.");
    }

    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

// Fungsi untuk menghasilkan email acak berdasarkan format
function generateEmailFromFormat($format) {
    return preg_replace_callback('/\{(.*?)\}/', function ($matches) {
        return generateRandomString($matches[1], 5); // Sesuaikan panjang jika diperlukan
    }, $format);
}

// Daftar user agents
function getRandomUserAgent() {
    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Version/15.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Android 11; Mobile; rv:91.0) Gecko/91.0 Firefox/91.0'
    ];
    return $userAgents[array_rand($userAgents)];
}

// Fungsi untuk mendapatkan device acak
function getRandomDevice() {
    $devices = [
        'Windows PC',
        'MacBook Pro',
        'iPhone',
        'Android Phone',
        'iPad',
        'Linux Desktop'
    ];
    return $devices[array_rand($devices)];
}

// Fungsi untuk mendapatkan negara dan kota acak
function getRandomCountryAndCity() {
    $countryCityMap = [
        'United States' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'],
        'Canada' => ['Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Ottawa'],
        'United Kingdom' => ['London', 'Manchester', 'Birmingham', 'Glasgow', 'Liverpool'],
        'Australia' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide'],
        'Germany' => ['Berlin', 'Munich', 'Frankfurt', 'Hamburg', 'Cologne'],
        'France' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice'],
        'Italy' => ['Rome', 'Milan', 'Naples', 'Turin', 'Florence'],
        'Spain' => ['Madrid', 'Barcelona', 'Valencia', 'Seville', 'Bilbao'],
        'Netherlands' => ['Amsterdam', 'Rotterdam', 'Utrecht', 'Eindhoven', 'The Hague'],
        'Brazil' => ['São Paulo', 'Rio de Janeiro', 'Salvador', 'Fortaleza', 'Brasília'],
        'Japan' => ['Tokyo', 'Osaka', 'Kyoto', 'Nagoya', 'Fukuoka'],
        'South Korea' => ['Seoul', 'Busan', 'Incheon', 'Gwangju', 'Daejeon'],
        'India' => ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai'],
        'China' => ['Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu'],
        'Russia' => ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Nizhny Novgorod']
    ];
    
    $country = array_rand($countryCityMap);
    $city = $countryCityMap[$country][array_rand($countryCityMap[$country])];
    
    return [$country, $city];
}

// Fungsi untuk menghasilkan IP acak
function getRandomIp() {
    return implode('.', array_map(function () {
        return rand(0, 255);
    }, range(1, 4)));
}

// Fungsi untuk menghasilkan link acak dari file
function getRandomLink() {
    $linkFile = 'link.txt';
    $links = file($linkFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($links === false || empty($links)) {
        return 'https://example.com'; // Link default jika file tidak ditemukan atau kosong
    }

    return $links[array_rand($links)];
}
