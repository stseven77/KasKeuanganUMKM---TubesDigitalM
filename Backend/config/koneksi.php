<?php
// Konfigurasi Kredensial Database MySQL (InfinityFree)
$host     = "sql307.infinityfree.com"; 
$port     = "3306";                    // Port default MySQL
$dbname   = "if0_42543060_rinfis";     // Nama database dari InfinityFree
$user     = "if0_42543060";            // Username database dari InfinityFree
$password = "rinfisbisnis123";         // Password akun / database Anda

try {
    // Membuat string koneksi MySQL untuk PDO (tanpa sslmode karena menggunakan MySQL biasa)
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    // Inisialisasi koneksi PDO
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mengaktifkan handling error berupa exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query otomatis berupa array asosiatif
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Mematikan emulasi prepared statements untuk keamanan
    ]);

    // Berhasil terhubung (bisa dihapus/dikomentari jika sudah masuk tahap production)
    // echo "Koneksi ke database InfinityFree berhasil!";

} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan program dan tampilkan pesan error
    http_response_code(500);
    echo "Koneksi database gagal: " . $e->getMessage();
    exit();
}
?>