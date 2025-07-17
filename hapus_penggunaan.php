<?php
session_start();
include "koneksi.php";

if ($_SESSION['level'] != 1) {
    echo "Akses ditolak!";
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete = mysqli_query($koneksi, "DELETE FROM penggunaan WHERE id_penggunaan = $id");

    if ($delete) {
        echo "<script>alert('Data berhasil dihapus!'); window.location='input_penggunaan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data!'); window.location='input_penggunaan.php';</script>";
    }
} else {
    echo "<script>alert('ID tidak ditemukan!'); window.location='input_penggunaan.php';</script>";
}
?>
