<?php
session_start();
include "koneksi.php";

$step = 1;
$username = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Cek username
    if (isset($_POST['check_username'])) {
        $username = trim($_POST['username']);
        $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username'");

        if (mysqli_num_rows($cek) > 0) {
            $step = 2;
        } else {
            $error = "Username tidak ditemukan!";
        }
    }

    // Step 2: Update password
    if (isset($_POST['reset_password'])) {
        $username = $_POST['username'];
        $new_pass = trim($_POST['new_password']);

        if (strlen($new_pass) < 4) {
            $error = "Password minimal 4 karakter.";
            $step = 2;
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($koneksi, "UPDATE user SET password='$hashed' WHERE username='$username'");
            echo "<script>alert('Password berhasil direset! Silakan login kembali.'); window.location='login.php';</script>";
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #eee;
            font-family: 'Lora', serif;
        }
        .box {
            background: white;
            padding: 30px;
            max-width: 400px;
            margin: 100px auto;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .btn-custom {
            background-color: #9e80a6;
            color: white;
            border: none;
        }
        .btn-custom:hover {
            background-color: #a194a1;
        }
    </style>
</head>
<body>
<div class="box">
    <h4 class="text-center mb-4">Lupa Password</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($step == 1): ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Masukkan Username:</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <button type="submit" name="check_username" class="btn btn-custom w-100">Lanjut</button>
        </form>
    <?php elseif ($step == 2): ?>
        <form method="post">
            <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
            <div class="mb-3">
                <label class="form-label">Password Baru:</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <button type="submit" name="reset_password" class="btn btn-custom w-100">Reset Password</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
