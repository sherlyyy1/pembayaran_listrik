<?php
session_start();
include "koneksi.php";

if ($_SESSION['level'] != 1) {
    echo "Akses ditolak!";
    exit;
}

$tagihan = mysqli_query($koneksi, "SELECT * FROM view_tagihan WHERE status_pembayaran='Menunggu Konfirmasi'");

// Proses konfirmasi
if (isset($_GET['konfirmasi'])) {
    $id = $_GET['konfirmasi'];
    mysqli_query($koneksi, "UPDATE tagihan SET status_pembayaran='Lunas' WHERE id_tagihan='$id'");
    echo "<script>alert('Tagihan telah dikonfirmasi!'); window.location='konfirmasi_tagihan.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran</title>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Lora', serif;
            background-color: #847389;
            color: #2c2c2c;
        }
        .card-custom {
            background-color: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .btn-konfirmasi {
            background-color: #9e80a6;
            color: #fff;
            border: none;
        }
        .btn-konfirmasi:hover {
            background-color: #a495a8;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card card-custom">
            <h3 class="text-center mb-4">Konfirmasi Pembayaran</h3>
            <table class="table table-hover table-bordered align-middle">
                <thead class="text-center table-light">
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Bulan</th>
                        <th>Tahun</th>
                        <th>KWH</th>
                        <th>Tagihan (Rp)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php while ($t = mysqli_fetch_array($tagihan)) { ?>
                    <tr>
                        <td><?= $t['nama_pelanggan'] ?></td>
                        <td><?= $t['bulan'] ?></td>
                        <td><?= $t['tahun'] ?></td>
                        <td><?= $t['jumlah_kwh'] ?></td>
                        <td><?= number_format($t['jumlah_tagihan']) ?></td>
                        <td>
                            <a href="konfirmasi_tagihan.php?konfirmasi=<?= $t['id_tagihan'] ?>" 
                               class="btn btn-sm btn-konfirmasi"
                               onclick="return confirm('Konfirmasi pembayaran ini?')">
                                Konfirmasi
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
