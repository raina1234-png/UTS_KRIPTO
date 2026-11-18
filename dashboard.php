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
$stmtKey = $conn->prepare("SELECT rsa_public_key, rsa_private_key FROM admins WHERE username = ?");
$stmtKey->bind_param("s", $user_logged);
$stmtKey->execute();
$adminData = $stmtKey->get_result()->fetch_assoc();
$publicKey = $adminData['rsa_public_key'];
$privateKey = $adminData['rsa_private_key'];

$total_data = $conn->query("SELECT COUNT(*) as total FROM secure_contents")->fetch_assoc()['total'];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

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
    header("Location: dashboard.php?page=dashboard");
    exit;
}

$contents = $conn->query("SELECT * FROM secure_contents ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>uCrypto Dashboard - Aesthetic Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #fff0f5;
            --sidebar-bg: rgba(255, 255, 255, 0.85);
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
            margin: 0;
            color: var(--text-main);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Glassmorphism */
        .sidebar {
            width: 250px;
            background: var(--sidebar-bg);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
            box-shadow: 5px 0 25px rgba(255, 182, 193, 0.2);
        }

        .sidebar-brand {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.5px;
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .nav-links li {
            margin-bottom: 12px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 14px;
            transition: 0.3s ease;
        }

        .nav-links a.active, .nav-links a:hover {
            background: linear-gradient(135deg, #ff69b4 0%, #ff1493 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 105, 180, 0.4);
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 35px 45px;
            overflow-y: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--card-bg);
            padding: 20px 30px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 25px rgba(255, 182, 193, 0.15);
        }

        .top-bar h1 {
            font-size: 22px;
            color: var(--primary-dark);
            margin: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff0f5;
            padding: 8px 18px;
            border-radius: 30px;
            border: 1px solid var(--border-color);
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 24px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 25px rgba(255, 182, 193, 0.15);
            position: relative;
            overflow: hidden;
        }

        .metric-card::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 80px;
            background: radial-gradient(circle, rgba(255,105,180,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .metric-card h4 {
            margin: 0 0 8px 0;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
        }

        .metric-card .value {
            font-size: 26px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        /* Content Card / Forms / Tables */
        .content-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(255, 182, 193, 0.2);
            margin-bottom: 30px;
        }

        .content-card h3 {
            margin-top: 0;
            color: var(--primary-dark);
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #ad1457;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: #fffaf0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-pink);
            background: #fff;
            box-shadow: 0 0 10px rgba(255, 105, 180, 0.25);
        }

        .btn-submit {
            background: linear-gradient(135deg, #ff69b4 0%, #ff1493 100%);
            color: white;
            border: none;
            padding: 13px 28px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(255, 105, 180, 0.4);
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(255, 105, 180, 0.6);
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: 16px;
            overflow: hidden;
        }

        table, th, td { border: 1px solid #ffd1dc; }
        th { background: #ffe4e1; color: var(--primary-dark); padding: 14px; font-size: 13px; font-weight: 600; text-align: left; }
        td { padding: 14px; font-size: 13px; background: rgba(255,255,255,0.8); }
        tr:nth-child(even) td { background: #fffaf0; }

        code {
            background: #fce4ec;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            color: #ad1457;
            word-break: break-all;
            font-family: monospace;
        }

        .action-link {
            color: white;
            text-decoration: none;
            font-weight: 600;
            background: linear-gradient(135deg, #ff69b4 0%, #ff1493 100%);
            padding: 7px 14px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(255, 105, 180, 0.3);
            transition: 0.3s;
            display: inline-block;
        }

        .action-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.5);
        }

        .badge-status {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #c8e6c9;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                🌸 uCrypto
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php?page=dashboard" class="<?php echo ($page == 'dashboard') ? 'active' : ''; ?>">📊 Dashboard</a></li>
                <li><a href="dashboard.php?page=aes" class="<?php echo ($page == 'aes') ? 'active' : ''; ?>">🔒 Enkripsi AES</a></li>
                <li><a href="dashboard.php?page=rsa" class="<?php echo ($page == 'rsa') ? 'active' : ''; ?>">🔑 Kunci RSA</a></li>
                <li><a href="logout.php" style="color: #c2185b; margin-top: 40px; background: #ffebee;">🚪 Keluar</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar Header -->
            <div class="top-bar">
                <div>
                    <h1>Good day, <?php echo htmlspecialchars($_SESSION['admin']); ?>! ✨</h1>
                    <p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 13px;">Kelola sistem pengamanan data simetris & asimetris admin.</p>
                </div>
                <div class="user-profile">
                    <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #ff69b4 0%, #ff1493 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 13px; box-shadow: 0 3px 10px rgba(255,105,180,0.4);">A</div>
                    <span style="font-size: 14px; font-weight: 600; color: #d81b60;"><?php echo htmlspecialchars($_SESSION['admin']); ?></span>
                </div>
            </div>

            <?php if ($page == 'dashboard'): ?>
                <!-- METRIC CARDS -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h4>📁 Total Data Terenkripsi</h4>
                        <div class="value"><?php echo $total_data; ?> Berkas</div>
                        <span class="badge-status" style="display:inline-block; margin-top:10px;">● System Secure</span>
                    </div>
                    <div class="metric-card">
                        <h4>🔒 Algoritma Simetris</h4>
                        <div class="value" style="font-size: 22px;">AES-256-CBC</div>
                        <span style="font-size: 11px; color: var(--text-muted); display:block; margin-top:12px;">High Speed Block Cipher</span>
                    </div>
                    <div class="metric-card">
                        <h4>🔑 Algoritma Asimetris</h4>
                        <div class="value" style="font-size: 22px;">RSA 2048-bit</div>
                        <span style="font-size: 11px; color: var(--text-muted); display:block; margin-top:12px;">Public/Private Key Pair</span>
                    </div>
                </div>

                <!-- FORM TAMBAH DATA -->
                <div class="content-card">
                    <h3>➕ Tambah & Enkripsi Data Baru</h3>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group full">
                                <label>Judul / Perihal Informasi</label>
                                <input type="text" name="title" class="form-control" placeholder="Contoh: Kunci API Production Gateway" required>
                            </div>
                            <div class="form-group">
                                <label>Data untuk Enkripsi Simetris (AES-256)</label>
                                <input type="text" name="simetris_input" class="form-control" placeholder="Contoh: Laporan Keuangan Q1" required>
                            </div>
                            <div class="form-group">
                                <label>Data untuk Enkripsi Asimetris (RSA Public Key)</label>
                                <input type="text" name="asimetris_input" class="form-control" placeholder="Contoh: Token Master Rahasia" required>
                            </div>
                            <div class="form-group full">
                                <button type="submit" name="tambah" class="btn-submit">Simpan & Amankan Data</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TABEL DATA -->
                <div class="content-card">
                    <h3>📋 Daftar Ciphertext Tersimpan di Database</h3>
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 50px;">No</th>
                                <th>Judul Informasi</th>
                                <th>Ciphertext Simetris (AES)</th>
                                <th>Ciphertext Asimetris (RSA)</th>
                                <th style="text-align: center; width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while($row = $contents->fetch_assoc()): ?>
                            <tr>
                                <td style="text-align: center; font-weight:600; color:#ad1457;"><?php echo $no++; ?></td>
                                <td><b><?php echo htmlspecialchars($row['title']); ?></b></td>
                                <td><code><?php echo htmlspecialchars($row['symmetric_data']); ?></code></td>
                                <td><code><?php echo htmlspecialchars($row['asymmetric_data']); ?></code></td>
                                <td style="text-align: center;">
                                    <a href="proses.php?id=<?php echo $row['id']; ?>" class="action-link">🔓 Dekripsi</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($page == 'aes'): ?>
                <!-- MENU ENKRIPSI AES -->
                <div class="content-card">
                    <h3>🔒 Modul Kriptografi Simetris (AES-256-CBC)</h3>
                    <p style="line-height: 1.7; font-size: 14px; color: #555;">
                        Kriptografi simetris yang diimplementasikan menggunakan algoritma standar industri <b>AES (Advanced Encryption Standard)</b> dengan panjang kunci 256-bit dalam mode operasi **CBC (Cipher Block Chaining)**. 
                        Metode ini sangat cepat dan efisien untuk mengenkripsi data berukuran besar pada panel admin dengan menggunakan satu kunci rahasia bersama (*secret key*) yang diturunkan melalui fungsi hash SHA-256.
                    </p>
                    <div style="background: #fffaf0; border: 1px solid var(--border-color); padding: 18px; border-radius: 14px; margin-top: 25px;">
                        <b style="color: #ad1457;">Status Sistem:</b> <span class="badge-status">Aktif & Terproteksi</span>
                    </div>
                </div>

            <?php elseif ($page == 'rsa'): ?>
                <!-- MENU KUNCI RSA -->
                <div class="content-card">
                    <h3>🔑 Manajemen Kunci Asimetris (RSA 2048-bit)</h3>
                    <p style="line-height: 1.7; font-size: 14px; color: #555;">
                        Sistem ini menggunakan sepasang kunci asimetris berbasis algoritma **RSA** berukuran 2048-bit yang terasosiasi secara unik untuk setiap administrator.
                    </p>
                    <h4 style="color: #ad1457; margin-top: 20px; font-size: 14px;">Public Key (Kunci Publik - Untuk Enkripsi):</h4>
                    <textarea class="form-control" rows="5" readonly style="font-family: monospace; font-size: 11px; background: #fff;"><?php echo htmlspecialchars($publicKey); ?></textarea>
                    
                    <h4 style="color: #ad1457; margin-top: 20px; font-size: 14px;">Private Key (Kunci Privat - Tersimpan Aman di Sesi Admin):</h4>
                    <textarea class="form-control" rows="5" readonly style="font-family: monospace; font-size: 11px; background: #fff;"><?php echo htmlspecialchars($privateKey); ?></textarea>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>