<?php
session_start();

// Menyesuaikan jalur (path) menuju file koneksi.php dari folder pages/halaman-awal/
require_once '../../config/koneksi.php'; 

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

                    // Arahkan berdasarkan role (sesuaikan path foldernya jika berada di luar folder ini)
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
?>
<!DOCTYPE html>
<html lang="id" class="light" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk & Daftar - RINFIS Kasir Modern</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-white dark:from-slate-950 dark:via-slate-900 dark:to-slate-900 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex items-center justify-center p-4 sm:p-6 transition-colors duration-300">

    <!-- Container Utama -->
    <div class="w-full max-w-md bg-white/85 dark:bg-slate-900/90 backdrop-blur-xl border border-indigo-100 dark:border-slate-800 shadow-2xl shadow-indigo-100/50 dark:shadow-none rounded-3xl p-8 sm:p-10 relative overflow-hidden transition-all duration-500">
        
        <!-- Efek Cahaya Estetik -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-purple-400/20 dark:bg-purple-900/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-indigo-400/20 dark:bg-indigo-900/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header Logo & Judul -->
        <div class="text-center mb-8 relative z-10">
            <a href="../../index.html" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 border border-indigo-100 dark:border-slate-700 shadow-lg shadow-indigo-200/50 dark:shadow-none mb-4 hover:scale-105 transition-transform overflow-hidden p-2">
                <img src="../../rinfisunguawal.png" alt="Logo RINFIS" class="w-full h-full object-contain">
            </a>
            <h1 id="form-title" class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Selamat Datang Kembali</h1>
            <p id="form-subtitle" class="text-sm text-slate-500 dark:text-slate-400 mt-1.5">Masuk ke akun RINFIS untuk mengelola tokomu</p>
        </div>

        <!-- Notifikasi Pesan PHP -->
        <?php if (!empty($error_message)): ?>
            <div class="mb-4 p-3 rounded-xl bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs font-medium text-center relative z-10">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="mb-4 p-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs font-medium text-center relative z-10">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Tombol Tab Switcher (Login / Register) -->
        <div class="flex p-1 bg-indigo-50/80 dark:bg-slate-800 rounded-2xl mb-6 relative z-10 border border-indigo-100/60 dark:border-slate-700/60">
            <button id="tab-login" type="button" onclick="switchMode('login')" class="flex-1 py-2.5 text-xs font-bold rounded-xl transition-all bg-indigo-600 text-white shadow-sm">
                Masuk
            </button>
            <button id="tab-register" type="button" onclick="switchMode('register')" class="flex-1 py-2.5 text-xs font-bold rounded-xl transition-all text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                Daftar Baru
            </button>
        </div>

        <!-- Form Autentikasi (Self-submit ke file ini sendiri) -->
        <form action="login.php" method="POST" class="space-y-4 relative z-10">
            <input type="hidden" name="action_type" id="action_type" value="login">

            <!-- Input Nama (Hanya tampil saat mode Register) -->
            <div id="name-field" class="hidden space-y-1.5 transition-all duration-300">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama Lengkap / Usaha</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 dark:text-slate-500">
                        <i class="fa-solid fa-user text-sm"></i>
                    </span>
                    <input type="text" name="nama" id="fullname" placeholder="cth: Kedai Kopi Senja" class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-indigo-50/40 dark:bg-slate-800 border border-indigo-100 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white dark:focus:bg-slate-800 transition">
                </div>
            </div>

            <!-- Input Nomor Telepon (Hanya tampil saat mode Register) -->
            <div id="phone-field" class="hidden space-y-1.5 transition-all duration-300">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nomor Telepon / WhatsApp</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 dark:text-slate-500">
                        <i class="fa-solid fa-phone text-sm"></i>
                    </span>
                    <input type="tel" name="no_telepon" id="phone" placeholder="081234567890" class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-indigo-50/40 dark:bg-slate-800 border border-indigo-100 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white dark:focus:bg-slate-800 transition">
                </div>
            </div>

            <!-- Input Email -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 dark:text-slate-500">
                        <i class="fa-solid fa-envelope text-sm"></i>
                    </span>
                    <input type="email" name="email" id="email" required placeholder="nama@email.com" class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-indigo-50/40 dark:bg-slate-800 border border-indigo-100 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white dark:focus:bg-slate-800 transition">
                </div>
            </div>

            <!-- Input Password -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Kata Sandi</label>
                    <a href="lupa-sandi.html" id="forgot-link" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Lupa sandi?</a>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 dark:text-slate-500">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" name="password" id="password" required placeholder="••••••••" class="w-full pl-11 pr-12 py-3.5 rounded-xl bg-indigo-50/40 dark:bg-slate-800 border border-indigo-100 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:bg-white dark:focus:bg-slate-800 transition">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none">
                        <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Tombol Aksi Utama -->
            <button type="submit" id="submit-btn" class="w-full mt-2 py-4 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-200 dark:shadow-none transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center space-x-2">
                <span id="btn-text">Masuk ke Dashboard</span>
                <i id="btn-icon" class="fa-solid fa-arrow-right text-xs"></i>
            </button>
        </form>

        <!-- Footer / Kembali ke Beranda -->
        <div class="mt-8 text-center relative z-10 border-t border-indigo-50 dark:border-slate-800 pt-6">
            <a href="../../index.html" class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda RINFIS
            </a>
        </div>
    </div>

    <!-- Script Interaktif JS -->
    <script>
        function switchMode(mode) {
            const tabLogin = document.getElementById('tab-login');
            const tabRegister = document.getElementById('tab-register');
            const nameField = document.getElementById('name-field');
            const phoneField = document.getElementById('phone-field');
            const fullnameInput = document.getElementById('fullname');
            const phoneInput = document.getElementById('phone');
            const actionType = document.getElementById('action_type');
            const formTitle = document.getElementById('form-title');
            const formSubtitle = document.getElementById('form-subtitle');
            const btnText = document.getElementById('btn-text');
            const forgotLink = document.getElementById('forgot-link');

            actionType.value = mode;

            if (mode === 'login') {
                tabLogin.className = "flex-1 py-2.5 text-xs font-bold rounded-xl transition-all bg-indigo-600 text-white shadow-sm";
                tabRegister.className = "flex-1 py-2.5 text-xs font-bold rounded-xl transition-all text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400";
                nameField.classList.add('hidden');
                phoneField.classList.add('hidden');
                fullnameInput.removeAttribute('required');
                phoneInput.removeAttribute('required');
                formTitle.textContent = "Selamat Datang Kembali";
                formSubtitle.textContent = "Masuk ke akun RINFIS untuk mengelola tokomu";
                btnText.textContent = "Masuk ke Dashboard";
                forgotLink.style.display = "block";
            } else {
                tabRegister.className = "flex-1 py-2.5 text-xs font-bold rounded-xl transition-all bg-indigo-600 text-white shadow-sm";
                tabLogin.className = "flex-1 py-2.5 text-xs font-bold rounded-xl transition-all text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400";
                nameField.classList.remove('hidden');
                phoneField.classList.remove('hidden');
                fullnameInput.setAttribute('required', 'true');
                phoneInput.setAttribute('required', 'true');
                formTitle.textContent = "Buat Akun RINFIS";
                formSubtitle.textContent = "Mulai kelola tokomu dengan mudah dan gratis";
                btnText.textContent = "Daftar Sekarang";
                forgotLink.style.display = "none";
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>