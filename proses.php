<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}
include 'koneksi.php';

define('AES_KEY', hash('sha256', 'KunciRahasiaPinkAdmin99!', true));
define('AES_IV', substr(hash('sha256', 'InisialisasiVektorPink', true), 0, 16));

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM secure_contents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan!");
}

$decrypted_simetris = openssl_decrypt(base64_decode($data['symmetric_data']), 'AES-256-CBC', AES_KEY, 0, AES_IV);

$privateKey = $_SESSION['private_key'];
$decoded_asymmetric_ciphertext = base64_decode($data['asymmetric_data']);
openssl_private_decrypt($decoded_asymmetric_ciphertext, $decrypted_asimetris, $privateKey);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Dekripsi - Pink Secure Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffe6f0 0%, #ffc2d1 100%);
            margin: 0;
            padding: 50px 20px;
            color: #4a4a4a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(219, 112, 147, 0.2);
            width: 600px;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        h2 { color: #d81b60; text-align: center; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #888; font-size: 13px; margin-bottom: 25px; }
        .result-box {
            background: #fffaf0;
            border: 1px solid #ffb6c1;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            color: #880e4f;
            font-weight: 500;
            font-size: 14px;
        }
        h4 { color: #ad1457; margin-bottom: 8px; font-weight: 600; }
        .back {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: white;
            background: linear-gradient(135deg, #ff69b4 0%, #ff1493 100%);
            padding: 12px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.4);
            transition: 0.3s;
        }
        .back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 105, 180, 0.6);
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>🔓 Hasil Dekripsi Data 🔓</h2>
        <div class="subtitle">Judul: <b><?php echo htmlspecialchars($data['title']); ?></b></div>
        <hr style="border: 0; border-top: 1px solid #ffe4e1; margin: 20px 0;">

        <h4>1. Hasil Dekripsi Simetris (AES-256):</h4>
        <div class="result-box">
            <?php echo htmlspecialchars($decrypted_simetris); ?>
        </div>

        <h4>2. Hasil Dekripsi Asimetris (RSA Private Key):</h4>
        <div class="result-box">
            <?php echo htmlspecialchars($decrypted_asimetris); ?>
        </div>

        <a href="dashboard.php" class="back">&larr; Kembali ke Dashboard</a>
    </div>
</body>
</html>