<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin'] = $row['username'];
            $_SESSION['private_key'] = $row['rsa_private_key'];
            header("Location: dashboard.php");
            exit;
        }
    }
    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - uCrypto Aesthetic Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #fff0f5;
            --card-bg: rgba(255, 255, 255, 0.9);
            --primary-pink: #ff69b4;
            --primary-dark: #d81b60;
            --text-main: #4a4a4a;
            --text-muted: #888888;
            --border-color: #ffc0cb;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-main);
            background-image: radial-gradient(#ffb6c1 1.2px, transparent 1.2px);
            background-size: 24px 24px;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 45px 40px;
            border-radius: 28px;
            box-shadow: 0 15px 35px rgba(255, 105, 180, 0.25);
            width: 400px;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(135deg, #ff69b4 0%, #ff1493 100%);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: var(--primary-dark);
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 700;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ad1457;
            font-size: 13px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 13px 18px;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-sizing: border-box;
            background: #fffaf0;
            outline: none;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary-pink);
            background: #fff;
            box-shadow: 0 0 12px rgba(255, 105, 180, 0.3);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ff69b4 0%, #ff1493 100%);
            border: none;
            color: white;
            border-radius: 14px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(255, 105, 180, 0.4);
            transition: 0.3s ease;
            font-family: 'Poppins', sans-serif;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 105, 180, 0.6);
        }

        .error {
            color: #c2185b;
            font-size: 12px;
            text-align: center;
            margin-bottom: 20px;
            background: #ffebee;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ffcdd2;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2>🌸 uCrypto Admin</h2>
            <p>Sistem Kemanan Data Simetris & Asimetris</p>
        </div>

        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username admin..." required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password..." required>
            </div>
            <button type="submit" name="login" class="btn">Masuk Panel ➔</button>
        </form>
    </div>
</body>
</html>