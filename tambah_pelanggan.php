<?php
session_start();
include "koneksi.php";

// Proteksi akses admin
if ($_SESSION['level'] != 1) {
    echo "Akses ditolak!";
    exit;
}

// Tambah pelanggan
if (isset($_POST['simpan'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama     = trim($_POST['nama_pelanggan']);

    if ($username === '' || $password === '' || $nama === '') {
        echo "<script>alert('Semua kolom wajib diisi!'); window.location='tambah_pelanggan.php';</script>";
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9._]{4,20}$/', $username)) {
        echo "<script>alert('Username harus 4-20 karakter tanpa spasi!'); window.location='tambah_pelanggan.php';</script>";
        exit;
    }

    if (strlen($password) < 6) {
        echo "<script>alert('Password minimal 6 karakter!'); window.location='tambah_pelanggan.php';</script>";
        exit;
    }

    if (!preg_match("/^[a-zA-Z\s.'-]{2,}$/", $nama)) {
        echo "<script>alert('Nama pelanggan minimal 2 huruf dan hanya huruf/karakter valid!'); window.location='tambah_pelanggan.php';</script>";
        exit;
    }

    $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan!'); window.location='tambah_pelanggan.php';</script>";
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    mysqli_query($koneksi, "INSERT INTO user (username, password, id_level) VALUES ('$username', '$password_hash', 2)");
    $id_user = mysqli_insert_id($koneksi);
    mysqli_query($koneksi, "INSERT INTO pelanggan (id_user, nama_pelanggan) VALUES ('$id_user', '$nama')");

    echo "<script>alert('Pelanggan berhasil ditambahkan!'); window.location='tambah_pelanggan.php';</script>";
}

// Update pelanggan
if (isset($_POST['update'])) {
    $id_user  = $_POST['id_user'];
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama_pelanggan']);

    if ($username === '' || $nama === '') {
        echo "<script>alert('Semua kolom wajib diisi!'); window.location='tambah_pelanggan.php?edit=$id_user';</script>";
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9._]{4,20}$/', $username)) {
        echo "<script>alert('Username harus 4-20 karakter tanpa spasi!'); window.location='tambah_pelanggan.php?edit=$id_user';</script>";
        exit;
    }

    if (!preg_match("/^[a-zA-Z\s.'-]{2,}$/", $nama)) {
        echo "<script>alert('Nama pelanggan minimal 2 huruf dan hanya huruf/karakter valid!'); window.location='tambah_pelanggan.php?edit=$id_user';</script>";
        exit;
    }

    $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username' AND id_user != '$id_user'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan oleh pengguna lain!'); window.location='tambah_pelanggan.php?edit=$id_user';</script>";
        exit;
    }

    mysqli_query($koneksi, "UPDATE user SET username='$username' WHERE id_user='$id_user'");
    mysqli_query($koneksi, "UPDATE pelanggan SET nama_pelanggan='$nama' WHERE id_user='$id_user'");

    echo "<script>alert('Pelanggan berhasil diupdate!'); window.location='tambah_pelanggan.php';</script>";
}

// Hapus pelanggan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user='$id' AND id_level=2");
    if (mysqli_num_rows($cek) == 0) {
        echo "<script>alert('Data tidak valid!'); window.location='tambah_pelanggan.php';</script>";
        exit;
    }

    mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id_user = '$id'");
    mysqli_query($koneksi, "DELETE FROM user WHERE id_user = '$id' AND id_level = 2");

    echo "<script>alert('Pelanggan berhasil dihapus!'); window.location='tambah_pelanggan.php';</script>";
}

// Edit mode
$editMode = false;
$edit_id = "";
$edit_username = "";
$edit_nama = "";

if (isset($_GET['edit'])) {
    $editMode = true;
    $edit_id = $_GET['edit'];
    $q = mysqli_query($koneksi, "SELECT user.username, pelanggan.nama_pelanggan 
                                FROM user 
                                JOIN pelanggan ON user.id_user = pelanggan.id_user 
                                WHERE user.id_user = '$edit_id'");

    if (mysqli_num_rows($q) == 0) {
        echo "<script>alert('Data tidak ditemukan!'); window.location='tambah_pelanggan.php';</script>";
        exit;
    }

    $dataEdit = mysqli_fetch_assoc($q);
    $edit_username = $dataEdit['username'];
    $edit_nama     = $dataEdit['nama_pelanggan'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pelanggan</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Lora', serif;
            background-color: #847389;
            color: #fff;
        }
        .form-box, .table-box {
            background-color: #fff;
            color: #2c2c2c;
            border-radius: 15px;
            padding: 85px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 750px;
        }
        .btn-custom {
            background-color: #9e80a6;
            color: #fff;
            border: none;
        }
        .btn-custom:hover {
            background-color: #a194a1;
        }
        a.btn-danger, a.btn-warning {
            font-size: 0.9rem;
            padding: 4px 10px;
        }
    </style>
</head>
<body>
<div class="d-flex flex-column justify-content-center align-items-center min-vh-100" style="padding-top: 100px;">
    <div class="form-box mb-5">
        <h3 class="text-center mb-4"><?= $editMode ? 'Edit Pelanggan' : 'Tambah Pelanggan' ?></h3>
        <form method="post">
            <?php if ($editMode): ?>
                <input type="hidden" name="id_user" value="<?= $edit_id ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Username:</label>
                <input type="text" name="username" class="form-control" required value="<?= $edit_username ?>">
            </div>
            <?php if (!$editMode): ?>
            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <?php endif; ?>
            <div class="mb-4">
                <label class="form-label">Nama Lengkap:</label>
                <input type="text" name="nama_pelanggan" class="form-control" required value="<?= $edit_nama ?>">
            </div>
            <div class="text-center">
                <?php if ($editMode): ?>
                    <button type="submit" name="update" class="btn btn-warning px-5">Update</button>
                <?php else: ?>
                    <button type="submit" name="simpan" class="btn btn-custom px-5">Simpan</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-box">
        <h5 class="mb-4 text-center">Daftar Pelanggan</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $data = mysqli_query($koneksi, "
                    SELECT user.id_user, user.username, pelanggan.nama_pelanggan 
                    FROM user 
                    INNER JOIN pelanggan ON user.id_user = pelanggan.id_user 
                    WHERE user.id_level = 2 
                    ORDER BY user.id_user DESC
                ");
                $no = 1;
                while ($row = mysqli_fetch_assoc($data)) {
                    echo "<tr>
                            <td>{$no}</td>
                            <td>{$row['username']}</td>
                            <td>{$row['nama_pelanggan']}</td>
                            <td>
                                <a href='?edit={$row['id_user']}' class='btn btn-warning'>Edit</a>
                                <a href='?hapus={$row['id_user']}' class='btn btn-danger' onclick=\"return confirm('Yakin mau hapus pelanggan ini?')\">Hapus</a>
                            </td>
                          </tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
