<?php
session_start();
include "koneksi.php";

// Debug error
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Proteksi: hanya admin (level 1)
if (!isset($_SESSION['level']) || $_SESSION['level'] != 1) {
    echo "Akses ditolak!";
    exit;
}

// Token hanya dibuat saat buka form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Proses simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
        echo "<script>alert('Form sudah disubmit atau token tidak valid.'); window.location='input_penggunaan.php';</script>";
        exit;
    }

    unset($_SESSION['form_token']);

    $id_pelanggan = mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']);
    $bulan        = (int)$_POST['bulan'];
    $tahun        = (int)$_POST['tahun'];
    $kwh_meter    = (int)$_POST['kwh_meter'];

    // Validasi dasar
    if (empty($id_pelanggan) || empty($bulan) || empty($tahun) || $kwh_meter === '') {
        echo "<script>alert('Semua kolom wajib diisi.'); window.location='input_penggunaan.php';</script>";
        exit;
    }

    // Validasi range bulan & tahun
    if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
        echo "<script>alert('Bulan atau tahun tidak valid.'); window.location='input_penggunaan.php';</script>";
        exit;
    }

    // Cek duplikat
    $cek_duplikat = mysqli_query($koneksi, "SELECT * FROM penggunaan 
        WHERE id_pelanggan='$id_pelanggan' AND bulan='$bulan' AND tahun='$tahun'");
    if (mysqli_num_rows($cek_duplikat) > 0) {
        echo "<script>alert('Data penggunaan bulan ini sudah ada!'); window.location='input_penggunaan.php';</script>";
        exit;
    }

    // Ambil KWH sebelumnya
    $prev = mysqli_query($koneksi, "SELECT kwh_meter FROM penggunaan 
        WHERE id_pelanggan='$id_pelanggan' AND 
        (tahun < '$tahun' OR (tahun = '$tahun' AND bulan < '$bulan'))
        ORDER BY tahun DESC, bulan DESC LIMIT 1");

    $kwh_lama = 0;
    if (mysqli_num_rows($prev) > 0) {
        $data_prev = mysqli_fetch_assoc($prev);
        $kwh_lama = (int)$data_prev['kwh_meter'];
    }

    // Validasi logika KWH
    if ($kwh_meter < $kwh_lama) {
        echo "<script>alert('KWH bulan ini tidak boleh lebih kecil dari bulan sebelumnya ($kwh_lama).'); window.location='input_penggunaan.php';</script>";
        exit;
    }

    // Insert
    $insert = mysqli_query($koneksi, "INSERT INTO penggunaan (id_pelanggan, bulan, tahun, kwh_meter) 
                                      VALUES ('$id_pelanggan', '$bulan', '$tahun', '$kwh_meter')");
    if ($insert) {
        echo "<script>alert('Data berhasil disimpan!'); window.location='input_penggunaan.php';</script>";
    } else {
        die("Gagal insert penggunaan: " . mysqli_error($koneksi));
    }
}

// NOTE: Logic dan tampilan digabung dalam satu file karena aplikasi masih sederhana.
// Jika dikembangkan lebih lanjut, pemisahan logic dan view akan diprioritaskan.

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Pemakaian Listrik</title>
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
    <div class="d-flex justify-content-center align-items-start min-vh-100" style="padding-top: 130px;">
        <div class="form-box">
            <h3 class="text-center mb-4">Input Pemakaian Listrik</h3>
            <form method="post" action="">
                <input type="hidden" name="form_token" value="<?php echo $_SESSION['form_token']; ?>">

                <div class="mb-3">
                    <label class="form-label">Pilih Pelanggan:</label>
                    <select class="form-select" name="id_pelanggan" required>
                        <option value="" disabled selected>-- Pilih Pelanggan --</option>
                        <?php
                        $pelanggan = mysqli_query($koneksi, "SELECT * FROM pelanggan");
                        while ($p = mysqli_fetch_assoc($pelanggan)) {
                            echo "<option value='{$p['id_pelanggan']}'>{$p['nama_pelanggan']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bulan:</label>
                    <input type="number" name="bulan" min="1" max="12" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tahun:</label>
                    <input type="number" name="tahun" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">KWH Meter Bulan Ini:</label>
                    <input type="number" name="kwh_meter" class="form-control" required>
                </div>

                <div class="text-center">
                    <button type="submit" name="simpan" class="btn btn-custom px-5">Simpan</button>
                </div>
            </form>

            <hr class="my-4">
            <h4 class="text-center mb-3">Data Pemakaian</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover bg-white text-dark">
                    <thead>
                        <tr class="table-secondary text-center">
                            <th>No</th>
                            <th>Nama Pelanggan</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>KWH Meter</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($koneksi, "SELECT penggunaan.*, pelanggan.nama_pelanggan 
                            FROM penggunaan 
                            JOIN pelanggan ON penggunaan.id_pelanggan = pelanggan.id_pelanggan 
                            ORDER BY tahun DESC, bulan DESC");
                        while ($data = mysqli_fetch_assoc($query)) {
                            echo "<tr class='text-center'>
                                    <td>{$no}</td>
                                    <td>{$data['nama_pelanggan']}</td>
                                    <td>{$data['bulan']}</td>
                                    <td>{$data['tahun']}</td>
                                    <td>{$data['kwh_meter']}</td>
                                    <td>
                                        <a href='edit_penggunaan.php?id={$data['id_penggunaan']}' class='btn btn-sm btn-warning'>Edit</a>
                                        <a href='hapus_penggunaan.php?id={$data['id_penggunaan']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin hapus data ini?')\">Hapus</a>
                                    </td>
                                </tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    document.querySelector("form").addEventListener("submit", function () {
        setTimeout(function () {
            const btn = document.querySelector("[name='simpan']");
            if (btn) btn.disabled = true;
        }, 100);
    });
    </script>
</body>
</html>
