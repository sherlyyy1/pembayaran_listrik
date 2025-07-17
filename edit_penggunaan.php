<?php
session_start();
include "koneksi.php";

// Proteksi hanya admin
if (!isset($_SESSION['level']) || $_SESSION['level'] != 1) {
    echo "Akses ditolak!";
    exit;
}

// Validasi ID penggunaan
if (!isset($_GET['id'])) {
    echo "ID tidak ditemukan.";
    exit;
}

$id_penggunaan = (int)$_GET['id'];

// Ambil data penggunaan
$data = mysqli_query($koneksi, "SELECT * FROM penggunaan WHERE id_penggunaan = '$id_penggunaan'");
if (mysqli_num_rows($data) == 0) {
    echo "Data tidak ditemukan.";
    exit;
}
$row = mysqli_fetch_assoc($data);

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelanggan = mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']);
    $bulan        = (int)$_POST['bulan'];
    $tahun        = (int)$_POST['tahun'];
    $kwh_meter    = trim($_POST['kwh_meter']);
    $tahun_sekarang = (int)date("Y");

    // Validasi input
    if (empty($id_pelanggan) || empty($bulan) || empty($tahun) || $kwh_meter === '') {
        echo "<script>alert('Semua kolom wajib diisi.'); window.location='edit_penggunaan.php?id=$id_penggunaan';</script>";
        exit;
    }

    if ($tahun < 2000 || $tahun > $tahun_sekarang + 1) {
        echo "<script>alert('Tahun tidak valid!'); window.location='edit_penggunaan.php?id=$id_penggunaan';</script>";
        exit;
    }

    if (!is_numeric($kwh_meter) || (int)$kwh_meter < 0) {
        echo "<script>alert('KWH Meter tidak boleh negatif!'); window.location='edit_penggunaan.php?id=$id_penggunaan';</script>";
        exit;
    }

    $kwh_meter = (int)$kwh_meter;

    // Update data penggunaan
    $update = mysqli_query($koneksi, "UPDATE penggunaan SET 
        id_pelanggan = '$id_pelanggan',
        bulan = '$bulan',
        tahun = '$tahun',
        kwh_meter = '$kwh_meter'
        WHERE id_penggunaan = '$id_penggunaan'");

    if ($update) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='input_penggunaan.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal update data!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pemakaian Listrik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lora&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lora', serif;
            background-color: #847389;
            color: #fff;
        }
        .form-box {
            background-color: #fff;
            color: #2c2c2c;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 600px;
        }
        .btn-custom {
            background-color: #9e80a6;
            color: #fff;
            border: none;
        }
        .btn-custom:hover {
            background-color: #a194a1;
        }
    </style>
</head>
<body>
<div class="d-flex justify-content-center align-items-start min-vh-100" style="padding-top: 100px;">
    <div class="form-box">
        <h3 class="text-center mb-4">Edit Pemakaian Listrik</h3>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Pilih Pelanggan:</label>
                <select class="form-select" name="id_pelanggan" required>
                    <?php
                    $pelanggan = mysqli_query($koneksi, "SELECT * FROM pelanggan");
                    while ($p = mysqli_fetch_assoc($pelanggan)) {
                        $selected = ($p['id_pelanggan'] == $row['id_pelanggan']) ? 'selected' : '';
                        echo "<option value='{$p['id_pelanggan']}' $selected>{$p['nama_pelanggan']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Bulan:</label>
                <input type="number" name="bulan" min="1" max="12" class="form-control" required value="<?= $row['bulan'] ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Tahun:</label>
                <input type="number" name="tahun" min="2000" max="<?= date('Y') + 1 ?>" class="form-control" required value="<?= $row['tahun'] ?>">
            </div>

            <div class="mb-4">
                <label class="form-label">KWH Meter Bulan Ini:</label>
                <input type="number" name="kwh_meter" min="0" class="form-control" required value="<?= $row['kwh_meter'] ?>">
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-custom px-5">Update</button>
                <a href="input_penggunaan.php" class="btn btn-secondary px-4 ms-2">Kembali</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
