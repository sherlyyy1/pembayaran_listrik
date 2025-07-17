<?php
session_start();
include "koneksi.php";

// Hanya untuk user (level 2)
if (!isset($_SESSION['level']) || $_SESSION['level'] != 2) {
    echo "Akses ditolak!";
    exit;
}

$id_tagihan = $_GET['id'];

// Ambil data tagihan
$query = mysqli_query($koneksi, "SELECT t.*, p.nama_pelanggan FROM tagihan t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    WHERE t.id_tagihan = '$id_tagihan'");

if (mysqli_num_rows($query) == 0) {
    echo "Tagihan tidak ditemukan!";
    exit;
}

$data = mysqli_fetch_assoc($query);

// Proses upload bukti
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti'])) {
    $file = $_FILES['bukti'];

    // Cek error upload
    if ($file['error'] !== 0) {
        echo "<script>alert('Gagal upload file!'); window.location='bayar.php?id=$id_tagihan';</script>";
        exit;
    }

    $namaFile = $file['name'];
    $tmp      = $file['tmp_name'];
    $ukuran   = $file['size'];
    $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png'];

    if (!in_array($ekstensi, $allowed)) {
        echo "<script>alert('Format file tidak diizinkan! (jpg, jpeg, png)'); window.location='bayar.php?id=$id_tagihan';</script>";
        exit;
    }

    if ($ukuran > 2 * 1024 * 1024) { // 2MB
        echo "<script>alert('Ukuran file maksimal 2MB!'); window.location='bayar.php?id=$id_tagihan';</script>";
        exit;
    }

    $namaBaru = uniqid('bukti_') . '.' . $ekstensi;
    $tujuan   = 'bukti_pembayaran/' . $namaBaru;

    if (!move_uploaded_file($tmp, $tujuan)) {
        echo "<script>alert('Gagal menyimpan file!'); window.location='bayar.php?id=$id_tagihan';</script>";
        exit;
    }

    // Simpan ke DB
    mysqli_query($koneksi, "UPDATE tagihan 
        SET bukti_transfer='$namaBaru', status_pembayaran='Menunggu Konfirmasi' 
        WHERE id_tagihan='$id_tagihan'");

    echo "<script>alert('Bukti berhasil diupload!'); window.location='bayar.php?id=$id_tagihan';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bayar Tagihan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .qr-box img {
            max-width: 300px;
            border: 2px solid #aaa;
        }
        .bukti-img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container bg-white p-4 rounded shadow">
    <h4>Pembayaran Tagihan</h4>
    <p><strong>Nama Pelanggan:</strong> <?= $data['nama_pelanggan']; ?></p>
    <p><strong>Bulan:</strong> <?= $data['bulan']; ?> / <?= $data['tahun']; ?></p>
    <p><strong>Total Tagihan:</strong> Rp<?= number_format($data['jumlah_tagihan'], 0, ',', '.'); ?></p>
    <p><strong>Status:</strong> <?= $data['status_pembayaran']; ?></p>

    <?php if ($data['status_pembayaran'] == 'Belum Bayar') : ?>
        <div class="qr-box text-center my-4">
            <p>Silakan transfer ke QR berikut:</p>
            <img src="img/qris.jpg" alt="QRIS" class="img-fluid">
        </div>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Upload Bukti Pembayaran:</label>
                <input type="file" name="bukti" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload Bukti</button>
        </form>
    <?php elseif ($data['status_pembayaran'] == 'Menunggu Konfirmasi') : ?>
        <div class="alert alert-warning">Bukti sudah diupload. Menunggu konfirmasi admin.</div>
        <?php if ($data['bukti_transfer']) : ?>
            <img src="bukti_pembayaran/<?= $data['bukti_transfer']; ?>" class="bukti-img">
        <?php endif; ?>
    <?php else : ?>
        <div class="alert alert-success">Tagihan sudah lunas.</div>
        <?php if ($data['bukti_transfer']) : ?>
            <p>Bukti Pembayaran:</p>
            <img src="bukti_pembayaran/<?= $data['bukti_transfer']; ?>" class="bukti-img">
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
