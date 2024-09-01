<?php
require 'vendor/autoload.php';
require 'function/random.php'; // Memuat fungsi generateRandomEmail, generateRandomString, dll.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fungsi untuk menampilkan pilihan dan membaca input pengguna
function promptUserChoice($prompt) {
    echo $prompt . "\n";
    echo "Masukkan pilihan (1 atau 2): ";
    $choice = trim(fgets(STDIN));
    return $choice;
}

// Fungsi untuk menggantikan placeholder dalam body email
function replacePlaceholders($bodyTemplate, $email) {
    $bodyTemplate = str_replace('{email}', $email, $bodyTemplate);
    // Menggantikan placeholder dengan nilai acak
    $bodyTemplate = preg_replace_callback('/\{hurufangkarandom,(\d+)\}/', function($matches) {
        return generateRandomString('hurufangkarandom', (int)$matches[1]);
    }, $bodyTemplate);
    $bodyTemplate = preg_replace_callback('/\{angkarandom,(\d+)\}/', function($matches) {
        return generateRandomString('angkarandom', (int)$matches[1]);
    }, $bodyTemplate);
    $bodyTemplate = preg_replace_callback('/\{hurufrandom,(\d+)\}/', function($matches) {
        return generateRandomString('hurufrandom', (int)$matches[1]);
    }, $bodyTemplate);
    $bodyTemplate = str_replace('{useragent}', getRandomUserAgent(), $bodyTemplate);
    $bodyTemplate = str_replace('{ip}', getRandomIp(), $bodyTemplate);
    // Menggantikan placeholder negara dan kota
    list($country, $city) = getRandomCountryAndCity();
    $bodyTemplate = str_replace('{negara}', $country, $bodyTemplate);
    $bodyTemplate = str_replace('{kota}', $city, $bodyTemplate);
    $bodyTemplate = str_replace('{device}', getRandomDevice(), $bodyTemplate);
    $bodyTemplate = str_replace('{link}', getRandomLink(), $bodyTemplate);

    return $bodyTemplate;
}

// Fungsi untuk menambahkan custom headers
function addCustomHeaders($mail) {
    $mail->addCustomHeader('Return-Path', '01000191a452f6e2-39b3d15a-1fb5-414b-8d54-f4805c1b4ac5-000000@amazonses.com');
    $mail->addCustomHeader('Feedback-ID', '::1.us-east-1.gYAFoJyo4oTqX7ts4hgZNt1Vfx3sRVkOYmFPJAePrnY=:AmazonSES');
    $mail->addCustomHeader('X-SES-Outgoing', date('Y-m-d H:i:s'));

}

// Fungsi untuk memilih konfigurasi SMTP secara bergiliran
function getSmtpConfig($configs) {
    $index = rand(0, count($configs) - 1);
    return $configs[$index];
}

// Memuat konfigurasi SMTP dari file eksternal
$configs = require 'smtp_config.php';

// Membaca daftar email dari file yang ditentukan oleh pengguna
echo "Masukkan nama file untuk daftar email (misalnya, email.txt): ";
$emailFile = trim(fgets(STDIN));
$recipients = file($emailFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if ($recipients === false) {
    die("Gagal membaca file $emailFile");
}

// Membaca nama pengirim dari file from-name.txt
$fromNameFile = 'from-name.txt';
$fromNames = file($fromNameFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if ($fromNames === false || empty($fromNames)) {
    die("Gagal membaca file from-name.txt atau file kosong");
}

// Membaca alamat email pengirim dari file from-email.txt
$fromEmailFile = 'from-email.txt';
$fromEmails = file($fromEmailFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if ($fromEmails === false || empty($fromEmails)) {
    die("Gagal membaca file from-email.txt atau file kosong");
}

// Format untuk alamat email pengirim
$fromFormat = 'noreply@transfinder.com'; //FROM EMAIL

// Pilihan untuk nama pengirim
$nameChoice = promptUserChoice("Pilih opsi untuk nama pengirim:\n1. From Name tetap\n2. From Name dari file");
$useRandomName = ($nameChoice === '2');

// Pilihan untuk menggunakan custom header
$headerChoice = promptUserChoice("Pilih opsi untuk custom header:\n1. Gunakan custom header\n2. Tidak menggunakan custom header");
$useCustomHeader = ($headerChoice === '1');

// Pilihan untuk subject
$subjectChoice = promptUserChoice("Pilih opsi untuk subject email:\n1. Subject default\n2. Subject dari file");
if ($subjectChoice === '2') {
    echo "Masukkan nama file untuk subject (misalnya, subject.txt): ";
    $subjectFile = trim(fgets(STDIN));
    $subjectContent = file($subjectFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($subjectContent === false || empty($subjectContent)) {
        die("Gagal membaca file $subjectFile atau file kosong");
    }
    $subject = $subjectContent[array_rand($subjectContent)];
} else {
    $subject = 'KIW'; //SUBJECT
}

// Minta input file HTML dari pengguna
echo "Masukkan nama file HTML untuk body email (misalnya, letter.html): ";
$htmlFile = trim(fgets(STDIN));

// Membaca konten HTML dari file yang ditentukan
$bodyTemplate = file_get_contents($htmlFile);
if ($bodyTemplate === false) {
    die("Gagal membaca file $htmlFile");
}

// Mengirim email ke setiap penerima dan mencetak hasilnya
$totalEmails = count($recipients);
$counter = 1; // Untuk menghitung nomor urut
$batchSize = 5; // Mengirim 5 email sekaligus
$fromEmailIndex = 0; // Untuk memulai dari alamat email pertama

foreach ($recipients as $email) {
    // Pilih konfigurasi SMTP
    $config = getSmtpConfig($configs);

    // Membuat instance dari PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi SMTP menggunakan data dari file konfigurasi
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->Port = $config['smtp_port'];
        $mail->SMTPSecure = $config['smtp_encryption'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        
        // Pilih nama pengirim
        if ($useRandomName) {
            $fromName = $fromNames[array_rand($fromNames)]; // Pilih nama pengirim acak dari file
        } else {
            $fromName = 'Service'; // Nama pengirim tetap
        }

        // Pilih alamat email pengirim secara rotasi
        $fromEmail = $fromEmails[$fromEmailIndex];
        $fromEmailIndex = ($fromEmailIndex + 1) % count($fromEmails); // Update index untuk rotasi

        // Menggantikan placeholder dalam body email
        $bodyContent = replacePlaceholders($bodyTemplate, $email);

        // Menyiapkan email
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email);
        $mail->Subject = $subject;
        $mail->isHTML(true);

        // Menambahkan Content-Transfer-Encoding base64
        $mail->Encoding = 'base64';
        $mail->Body = $bodyContent;

        // Menambahkan custom headers jika dipilih
        if ($useCustomHeader) {
            addCustomHeaders($mail);
        }

        // Mengirim email
        if ($mail->send()) {
            echo "[{$counter}/{$totalEmails}] {$email} => send [ from:{$fromEmail} ({$fromName}) SMTP: {$config['smtp_username']} ]\n";
        } else {
            echo "[{$counter}/{$totalEmails}] {$email} => failed [ from:{$fromEmail} ({$fromName}) SMTP: {$config['smtp_username']} ]\n";
        }
    } catch (Exception $e) {
        echo "[{$counter}/{$totalEmails}] {$email} => failed [ from:{$fromEmail} ({$fromName}) SMTP: {$config['smtp_username']} ] - Error: {$mail->ErrorInfo}\n";
    }

    $counter++; // Increment counter after each email

    // Delay setelah mengirim 5 email
    if ($counter % $batchSize === 1) {
        echo "Delay 3 detik...\n";
        sleep(3);
    }
}
