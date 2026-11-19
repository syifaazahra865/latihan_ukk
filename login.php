
<?php
session_start();
require_once 'config.php';

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Mencari akun berdasarkan username
    $query = "SELECT * FROM tb_login WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        // Memeriksa status akun
        if ($data['status'] == 'aktif') {

            // Memeriksa password
            if ($password === $data['password']) {

                // Membuat session login
                $_SESSION['username'] = $data['username'];
                $_SESSION['level'] = $data['level'];
                $_SESSION['id_akun'] = $data['id_akun'];

                // Memperbarui waktu login terakhir
                $update = "UPDATE tb_login
                           SET last_login = NOW()
                           WHERE id_login = ?";
                $stmt_update = mysqli_prepare($conn, $update);
                mysqli_stmt_bind_param(
                    $stmt_update,
                    "i",
                    $data['id_login']
                );
                mysqli_stmt_execute($stmt_update);

                // Mengarahkan berdasarkan level
                if ($data['level'] == 'super_admin') {
                    header("Location: admin/dashboard_superadmin.php");
                    exit;
                } elseif ($data['level'] == 'kasir') {
                    header("Location: dashboard_kasir.php");
                    exit;
                } else {
                    $error = "Level akun tidak memiliki akses.";
                }

            } else {
                $error = "Username atau password salah!";
            }

        } else {
            $error = "Akun tidak aktif!";
        }

    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cashless</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #eee8d2;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            display: flex;
            width: 850px;
            min-height: 500px;
            background: #fffaf5;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .left {
            width: 50%;
            background: #142b17;
            color: #f4ead5;
            padding: 50px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-size: 35px;
            margin-bottom: 20px;
        }

        .left h1 {
            font-size: 30px;
            margin-bottom: 15px;
        }

        .left p {
            font-size: 14px;
            line-height: 1.6;
            color: #d5d0bd;
        }

        .right {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .login-box {
            width: 100%;
            max-width: 320px;
        }

        .login-box h2 {
            color: #24351f;
            margin-bottom: 10px;
        }

        .login-box p {
            color: #777;
            font-size: 13px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 7px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #d5c7b6;
            border-radius: 7px;
            outline: none;
        }

        input:focus {
            border-color: #b87545;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: #b87545;
            color: white;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #965d35;
        }

        .error {
            background: #ffe0e0;
            color: #b00020;
            padding: 10px;
            border-radius: 5px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        @media (max-width: 700px) {
            .container {
                width: 95%;
                flex-direction: column;
            }

            .left, .right {
                width: 100%;
            }

            .left {
                padding: 30px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="left">
        <div class="logo">☕</div>
        <h1>Cafe Sekolah</h1>
        <p>
            Sistem Pembayaran Cashless
        </p>
        <br>
        <p>
            Tap kartu, bayar seketika — tanpa antre,
            tanpa uang receh.
        </p>
    </div>

    <div class="right">
        <div class="login-box">

            <h2>Login</h2>
            <p>Masuk ke akun Anda</p>

            <?php if ($error != '') { ?>
                <div class="error">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <form method="POST" action="">

                <label>Username</label>
                <input
                    type="text"
                    name="username"
                    placeholder="Masukkan username"
                    required
                >

                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >

                <button type="submit" name="login">
                    Masuk
                </button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
