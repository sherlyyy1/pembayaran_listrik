<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $cek = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");

    if (!$cek) {
        die("❌ Query error: " . mysqli_error($koneksi));
    }

    $data = mysqli_fetch_assoc($cek);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['level'] = $data['id_level'];

        if ($data['id_level'] == 1) {
            header("Location: dashboard_admin.php");
            exit;
        } else {
            header("Location: dashboard_user.php");
            exit;
        }
    } else {
        echo "<script>alert('Username atau password salah! Jika lupa password, klik \"Lupa Password\".'); window.location='login.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Lora', serif;
            background: linear-gradient(to bottom right, #b8adbb, #f5f5f5);
            min-height: 100vh;
        }
        .login-box {
            background-color: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 100px auto;
        }
        .btn-login {
            background-color: #9e80a6;
            color: white;
            border: none;
        }
        .btn-login:hover {
            background-color: #a194a9;
        }
        .forgot {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h3 class="text-center mb-4">Login</h3>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="text-center">
                <button type="submit" name="login" class="btn btn-login px-5">Login</button>
            </div>
        </form>
        <div class="forgot">
            <a href="lupa_password.php">Lupa Password?</a>
        </div>
    </div>
</body>
</html>
