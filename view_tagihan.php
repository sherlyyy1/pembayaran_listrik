<?php
session_start();
include "koneksi.php";

// Cek login dan level user
if ($_SESSION['level'] != 2) {
    echo "Akses ditolak!";
    exit;
}

$id_user = $_SESSION['id_user'];
$pelanggan = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_user='$id_user'"));
$id_pelanggan = $pelanggan['id_pelanggan'];

$tagihan = mysqli_query($koneksi, "SELECT * FROM view_tagihan WHERE id_pelanggan='$id_pelanggan'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tagihan Listrik</title>

    <!-- Google Font: Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons (for optional check icon) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- CSS Custom -->
    <style>
        body {
            font-family: 'Lora', serif;
            background-color: #847389;
            color: #2c2c2c;
        }
        .card-custom {
            background-color: #fff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            padding: 40px;
        }
        .table th {
            background-color: #b8adbb;
            color: #333;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fc;
        }
        .premium-heading {
            font-size: 35px;
            font-weight: bold;
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="premium-heading">Tagihan Listrik Pelanggan</div>
        <div class="card card-custom">
            <table class="table table-hover table-bordered align-middle">
                <thead class="text-center">
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Bulan</th>
                        <th>Tahun</th>
                        <th>KWH</th>
                        <th>Tagihan (Rp)</th>
                        <th>Status</th>
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
                            <?php if ($t['status_pembayaran'] == 'Belum Bayar') { ?>
                                <span class="badge bg-danger">Belum Bayar</span>
                            <?php } elseif ($t['status_pembayaran'] == 'Menunggu Konfirmasi') { ?>
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            <?php } else { ?>
                                <span class="badge bg-success">Lunas</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($t['status_pembayaran'] == 'Belum Bayar') { ?>
                                <a href="bayar.php?id=<?= $t['id_tagihan'] ?>" class="btn btn-sm btn-outline-primary">Bayar</a>
                            <?php } elseif ($t['status_pembayaran'] == 'Menunggu Konfirmasi') { ?>
                                <em class="text-muted">Menunggu Admin</em>
                            <?php } else { ?>
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
