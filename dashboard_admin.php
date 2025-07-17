<?php
session_start();
include "koneksi.php";

// FIXED: Validasi session aman
if (!isset($_SESSION['level']) || $_SESSION['level'] != 1) {
    echo "Akses ditolak!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>

    <!-- Font Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Lora', serif;
            background-color: #847389;
            color: #fff;
        }
        .dashboard-box {
            background-color: #fff;
            color: #2c2c2c;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .btn-glow {
            font-weight: 500;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="dashboard-box text-center">
            <h2 class="mb-4">Dashboard Admin</h2>
            <p class="mb-4">Silakan pilih aksi yang ingin dilakukan:</p>

            <!-- Baris atas: Input & Verifikasi -->
            <div class="d-flex justify-content-center gap-4 mb-3">
                <a href="input_penggunaan.php" class="btn btn-glow"
                   style="background-color: #9e80a6; color: #fff; border: none; width: 210px;">
                    Input Pemakaian
                </a>
                <a href="verifikasi_admin.php" class="btn btn-glow"
                   style="background-color: #9e80a6; color: #fff; border: none; width: 230px;">
                    Verifikasi Pembayaran
                </a>
            </div>

            <!-- Baris tengah: Tambah Pelanggan -->
            <div class="mb-3">
                <a href="tambah_pelanggan.php" class="btn btn-glow"
                   style="background-color: #9e80a6; color: #fff; border: none; width: 200px;">
                    Tambah Pelanggan
                </a>
            </div>

            <!-- Baris bawah: Logout -->
            <div>
                <a href="logout.php" class="btn btn-danger"
                   onclick="return confirm('Yakin mau logout?')" style="width: 100px;">
                    Logout
                </a>
            </div>
        </div>
    </div>
</body>
</html>
