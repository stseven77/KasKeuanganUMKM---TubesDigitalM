<?php
// Konfigurasi Kredensial Database Supabase (PostgreSQL)
// Ganti bagian di dalam tanda kutip dengan data dari Project Settings > Database di Supabase Anda
$host     = "db.odzszopvspcedihhdqvq.supabase.co"; // Contoh host dari Supabase
$port     = "5432";                     // Port default PostgreSQL Supabase
$dbname   = "postgres";                 // Nama database default Supabase
$user     = "postgres";                 // Username default database Supabase
$password = "AkuGay123!!";   // Password database yang Anda buat saat pertama kali bikin project

try {
    // Membuat string koneksi khusus PostgreSQL untuk PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    // Inisialisasi koneksi PDO
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mengaktifkan handling error berupa exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query otomatis berupa array asosiatif
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Mematikan emulasi prepared statements untuk keamanan
    ]);

    // Berhasil terhubung (bisa dihapus/dikomentari jika sudah masuk tahap production)
    // echo "Koneksi ke database Supabase berhasil!";

} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan program dan tampilkan pesan error
    http_response_code(500);
    echo "Koneksi database gagal: " . $e->getMessage();
    exit();
}
?>