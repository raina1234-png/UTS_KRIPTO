<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}
include 'koneksi.php';

define('AES_KEY', hash('sha256', 'KunciRahasiaPinkAdmin99!', true));
define('AES_IV', substr(hash('sha256', 'InisialisasiVektorPink', true), 0, 16));

function encryptSimetris($data) {
    return base64_encode(openssl_encrypt($data, 'AES-256-CBC', AES_KEY, 0, AES_IV));
}

$user_logged = $_SESSION['admin'];
$stmtKey = $conn->prepare("SELECT rsa_public_key FROM admins WHERE username = ?");
$stmtKey->bind_param("s", $user_logged);
$stmtKey->execute();
$pubKeyResult = $stmtKey->get_result()->fetch_assoc();
$publicKey = $pubKeyResult['rsa_public_key'];

if (isset($_POST['tambah'])) {
    $title = trim($_POST['title']);
    $plain_simetris = trim($_POST['simetris_input']);
    $plain_asimetris = trim($_POST['asimetris_input']);

    $encrypted_simetris = encryptSimetris($plain_simetris);

    openssl_public_encrypt($plain_asimetris, $encrypted_asimetris, $publicKey);
    $encrypted_asimetris_b64 = base64_encode($encrypted_asimetris);

    $stmtInsert = $conn->prepare("INSERT INTO secure_contents (title, symmetric_data, asymmetric_data) VALUES (?, ?, ?)");
    $stmtInsert->bind_param("sss", $title, $encrypted_simetris, $encrypted_asimetris_b64);
    $stmtInsert->execute();
    header("Location: dashboard.php");
    exit;
}

$contents = $conn->query("SELECT * FROM secure_contents ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Pink Secure Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffe6f0 0%, #ffc2d1 100%);
            margin: 0;
            padding: 40px 20px;
            color: #4a4a4a;
            min-height: 100vh;
        }
        .container {
            max-width: 1050px;
            margin: auto;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            padding: 35px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(219, 112, 147, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ffe4e1;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        h2, h3 { color: #d81b60; margin: 0; font-weight: 600; }
        .logout {
            color: #c2185b;
            text-decoration: none;
            font-weight: 600;
            background: #ffebee;
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13px;
            border: 1px solid #ffcdd2;
            transition: 0.3s;
        }
        .logout:hover { background: #ffcdd2; }
        .card-form {
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            border: 1px solid #ffb6c1;
            box-shadow: 0 5px 15px rgba(255, 182, 193, 0.2);
            margin-bottom: 35px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group.full { grid-column: span 2; }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #ad1457;
            font-size: 13px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 11px 15px;
            border: 1px solid #ffb6c1;
            border-radius: 10px;
            box-sizing: border-box;
            background: #fffaf0;
            outline: none;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
        }
        .form-control:focus {
            border-color: #ff69b4;
            background: #fff;
            box-shadow: 0 0 8px rgba(255, 105, 180, 0.3);
        }
        .btn-submit {
            background: linear-gradient(135deg, #ff69b4 0%, #ff1493 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.4);
            transition: 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 105, 180, 0.6);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        table, th, td { border: 1px solid #ffccd5; }
        th { background: #ffb6c1; color: #880e4f; padding: 14px; font-weight: 600; font-size: 13px; }
        td { padding: 12px 14px; background: #fff; font-size: 13px; }
        tr:nth-child(even) td { background: #fffaf0; }
        code {
            background: #fce4ec;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            color: #ad1457;
            word-break: break-all;
        }
        .action-link {
            color: #d81b60;
            text-decoration: none;
            font-weight: 600;
            background: #ffe6f0;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #ffb6c1;
            transition: 0.3s;
        }
        .action-link:hover {
            background: #ff69b4;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2>🌸 Pink Secure Admin Panel</h2>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">Login sebagai: <b><?php echo htmlspecialchars($_SESSION['admin']); ?></b> (AES-256 & RSA Protected)</p>
            </div>
            <a href="logout.php" class="logout">Keluar</a>
        </div>

        <div class="card-form">
            <h3 style="margin-bottom: 15px;">➕ Tambah Data Terenkripsi Baru</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Judul Informasi</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Kunci API Gateway / Catatan Rahasia" required>
                    </div>
                    <div class="form-group">
                        <label>Data untuk Enkripsi Simetris (AES-256)</label>
                        <input type="text" name="simetris_input" class="form-control" placeholder="Contoh: Laporan Keuangan Bulanan" required>
                    </div>
                    <div class="form-group">
                        <label>Data untuk Enkripsi Asimetris (RSA Public Key)</label>
                        <input type="text" name="asimetris_input" class="form-control" placeholder="Contoh: Master Password / Token Rahasia" required>
                    </div>
                    <div class="form-group full">
                        <button type="submit" name="tambah" class="btn-submit">Simpan Data Terenkripsi</button>
                    </div>
                </div>
            </form>
        </div>

        <h3>📋 Daftar Konten Tersimpan di Database</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Ciphertext Simetris (AES)</th>
                    <th>Ciphertext Asimetris (RSA)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while($row = $contents->fetch_assoc()): ?>
                <tr>
                    <td style="text-align: center;"><?php echo $no++; ?></td>
                    <td><b><?php echo htmlspecialchars($row['title']); ?></b></td>
                    <td><code><?php echo htmlspecialchars($row['symmetric_data']); ?></code></td>
                    <td><code><?php echo htmlspecialchars($row['asymmetric_data']); ?></code></td>
                    <td style="text-align: center;">
                        <a href="proses.php?id=<?php echo $row['id']; ?>" class="action-link">Dekripsi</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>