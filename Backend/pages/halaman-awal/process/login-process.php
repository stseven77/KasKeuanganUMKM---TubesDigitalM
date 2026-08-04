<?php
// Pastikan session hanya dimulai jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menggunakan __DIR__ agar path ke koneksi.php pasti tepat
// __DIR__ adalah folder 'process', naik 1 ke 'halaman-awal', naik 1 ke 'pages', naik 1 ke root folder
require_once __DIR__ . '/../../../config/koneksi.php';

$error_message = "";
$success_message = "";

// PROSES JIKA FORM DI-SUBMIT (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. LOGIKA LOGIN
    if ($action_type === 'login') {
        if (empty($email) || empty($password)) {
            $error_message = "Email dan password wajib diisi!";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['paket'] = $user['paket'];
                    $_SESSION['toko_id'] = $user['toko_id'];

                    // Naik 2 tingkat dari halaman-awal menuju root folder
                    switch ($user['role']) {
                        case 'super_admin':
                            header("Location: ../../admin_dashboard.php");
                            break;
                        case 'owner':
                            header("Location: ../../owner_dashboard.php");
                            break;
                        case 'owner_cabang':
                        case 'karyawan':
                            header("Location: ../../kasir_dashboard.php");
                            break;
                        default:
                            header("Location: ../../index.html");
                            break;
                    }
                    exit();
                } else {
                    $error_message = "Gagal Masuk: Email atau kata sandi salah!";
                }
            } catch (PDOException $e) {
                $error_message = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    }

    // 2. LOGIKA REGISTER (PENDAFTARAN)
    elseif ($action_type === 'register') {
        $nama = trim($_POST['nama'] ?? '');
        $no_telepon = trim($_POST['no_telepon'] ?? '');

        if (empty($nama) || empty($email) || empty($no_telepon) || empty($password)) {
            $error_message = "Semua kolom pendaftaran wajib diisi!";
        } else {
            try {
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt_check->execute([$email]);
                
                if ($stmt_check->rowCount() > 0) {
                    $error_message = "Email sudah terdaftar! Gunakan email lain atau masuk.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'owner';
                    $paket = 'free';

                    $stmt_insert = $pdo->prepare("INSERT INTO users (nama, email, no_telepon, password, role, paket) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_insert->execute([$nama, $email, $no_telepon, $hashed_password, $role, $paket]);

                    $success_message = "Pendaftaran berhasil! Silakan masuk.";
                }
            } catch (PDOException $e) {
                $error_message = "Gagal mendaftarkan akun: " . $e->getMessage();
            }
        }
    }
}