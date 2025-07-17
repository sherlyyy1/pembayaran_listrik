<?php
// koneksi.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pembayaran_listrik_fix"; 

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
