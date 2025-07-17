<?php
session_start();
include "koneksi.php";

// Hanya untuk admin
if (!isset($_SESSION['level']) || $_SESSION['level'] != 1) {
    echo "Akses ditolak!";
    exit;
}

// Proses konfirmasi lunas
if (isset($_GET['konfirmasi'])) {
    $id = $_GET['konfirmasi'];
    $update = mysqli_query($koneksi, "UPDATE tagihan SET status_pembayaran='Lunas' WHERE id_tagihan='$id'");
    if ($update) {
        echo "<script>alert('Tagihan telah dikonfirmasi lunas!'); window.location='verifikasi_admin.php';</script>";
    } else {
        echo "<script>alert('Gagal mengkonfirmasi tagihan!'); window.location='verifikasi_admin.php';</script>";
    }
    exit;
}

// Ambil semua tagihan yg menunggu konfirmasi
$data = mysqli_query($koneksi, "SELECT t.*, p.nama_pelanggan 
    FROM tagihan t 
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
    WHERE t.status_pembayaran='Menunggu Konfirmasi' 
    ORDER BY t.tahun DESC, t.bulan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lora', serif;
            background-color: #847389;
            color: #fff;
        }
        .container-box {
            background-color: #fff;
            color: #2c2c2c;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .bukti-preview {
            max-height: 100px;
            border: 2px solid #ccc;
            border-radius: 6px;
        }
        .btn-glow {
            background-color: #9e80a6;
            color: #fff;
            border: none;
            font-weight: 500;
            border-radius: 8px;
            padding: 6px 16px;
        }
        .btn-glow:hover {
            background-color: #a495a8;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="container-box">
            <h3 class="mb-4 text-center">Verifikasi Pembayaran Pengguna</h3>

            <?php if (mysqli_num_rows($data) == 0): ?>
                <div class="alert alert-success text-center">Tidak ada tagihan yang menunggu konfirmasi.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Pelanggan</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Tagihan</th>
                                <th>Bukti Transfer</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) : ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $row['nama_pelanggan']; ?></td>
                                <td><?= $row['bulan']; ?></td>
                                <td><?= $row['tahun']; ?></td>
                                <td>Rp<?= number_format($row['jumlah_tagihan'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($row['bukti_transfer']) : ?>
                                        <a href="bukti_pembayaran/<?= $row['bukti_transfer']; ?>" target="_blank">
                                            <img src="bukti_pembayaran/<?= $row['bukti_transfer']; ?>" class="bukti-preview">
                                        </a>
                                    <?php else : ?>
                                        <span class="text-danger">Belum upload</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['bukti_transfer']) : ?>
                                        <a href="bukti_pembayaran/<?= $row['bukti_transfer']; ?>" 
                                           target="_blank" class="btn btn-info btn-sm mb-1">Lihat Bukti</a><br>
                                        <a href="verifikasi_admin.php?konfirmasi=<?= $row['id_tagihan']; ?>" 
                                           onclick="return confirm('Yakin konfirmasi tagihan ini sudah lunas?')"
                                           class="btn btn-glow btn-sm mt-1">Konfirmasi Lunas</a>
                                    <?php else : ?>
                                        <span class="text-muted">Menunggu bukti</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
