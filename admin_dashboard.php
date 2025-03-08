<?php
session_start();

// Set header untuk mencegah cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Semak jika pengguna telah log masuk dan adalah admin
if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>
            alert('Sila log masuk sebagai admin.');
            window.location.replace('login.php');
          </script>";
    exit();
}

// Semak jika sesi telah tamat (30 minit)
$timeout = 1800; // 30 minit dalam saat
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    echo "<script>
            alert('Sesi anda telah tamat. Sila log masuk semula.');
            window.location.replace('login.php');
          </script>";
    exit();
}

// Kemaskini masa aktiviti terakhir
$_SESSION['LAST_ACTIVITY'] = time();

include('database.php');
include('sidebar.php');

// Dapatkan senarai sesi
$sesi_query = "SELECT sesi_id FROM sesi ORDER BY sesi_id DESC";
$sesi_result = $conn->query($sesi_query);

// Dapatkan sesi yang dipilih atau gunakan sesi terkini sebagai default
$selected_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : null;
if (!$selected_sesi) {
    $current_sesi_query = "SELECT sesi_id FROM sesi ORDER BY sesi_id DESC LIMIT 1";
    $selected_sesi = $conn->query($current_sesi_query)->fetch_assoc()['sesi_id'];
}

// Statistik Keseluruhan untuk sesi yang dipilih
$total_pensyarah_query = "SELECT COUNT(DISTINCT p.id) as total 
                         FROM pensyarah p 
                         JOIN kursus k ON p.id = k.pensyarah_id 
                         WHERE k.sesi = '$selected_sesi'";
$total_kursus_query = "SELECT COUNT(*) as total FROM kursus WHERE sesi = '$selected_sesi'";
$total_dokumen_query = "SELECT COUNT(d.dokumen_id) as total 
                       FROM dokumen d 
                       JOIN kursus k ON d.kursus_id = k.id 
                       WHERE k.sesi = '$selected_sesi'";

$total_pensyarah = $conn->query($total_pensyarah_query)->fetch_assoc()['total'];
$total_kursus = $conn->query($total_kursus_query)->fetch_assoc()['total'];
$total_dokumen = $conn->query($total_dokumen_query)->fetch_assoc()['total'];

// Status Penghantaran Mengikut Sesi Terkini
$current_sesi_query = "SELECT sesi FROM kursus ORDER BY sesi DESC LIMIT 1";
$current_sesi = $conn->query($current_sesi_query)->fetch_assoc()['sesi'];

// Statistik Penghantaran
$submission_stats_query = "
    SELECT 
        d.serahan_no,
        COUNT(CASE WHEN d.path_dokumen != '' THEN 1 END) as submitted,
        COUNT(*) as total
    FROM dokumen d
    JOIN kursus k ON d.kursus_id = k.id
    WHERE k.sesi = '$selected_sesi'
    GROUP BY d.serahan_no
";
$submission_stats = $conn->query($submission_stats_query);

// Statistik Semakan
$checking_stats_query = "
    SELECT 
        d.serahan_no,
        COUNT(CASE WHEN d.status = 'Checked' THEN 1 END) as checked,
        COUNT(*) as total
    FROM dokumen d
    JOIN kursus k ON d.kursus_id = k.id
    WHERE k.sesi = '$selected_sesi'
    GROUP BY d.serahan_no
";
$checking_stats = $conn->query($checking_stats_query);

// Statistik Pengesahan
$verification_stats_query = "
    SELECT 
        pk.no_serahan,
        COUNT(CASE WHEN pk.Status_pengesahan = 'telah disahkan' THEN 1 END) as verified,
        COUNT(DISTINCT k.id) as total_courses
    FROM kursus k
    LEFT JOIN pengesahan_kursus pk ON k.id = pk.kursus_id
    WHERE k.sesi = '$selected_sesi'
    GROUP BY pk.no_serahan
";
$verification_stats = $conn->query($verification_stats_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <style>
        .dashboard-container {
            padding: 20px;
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            margin: 0;
            color: #2c3e50;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }

        .progress-section {
            margin-top: 30px;
        }

        .progress-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .progress-bar {
            background: #eee;
            height: 20px;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #3498db;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .deadline-info {
            margin-top: 10px;
            color: #666;
        }

        .sesi-selector {
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .sesi-selector select {
            padding: 8px 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }

        .sesi-selector button {
            padding: 8px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .sesi-selector button:hover {
            background: #2980b9;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .current-sesi {
            font-size: 1.2em;
            color: #2c3e50;
            font-weight: bold;
        }
    </style>
    <script type="text/javascript">
        // Mencegah back button selepas log keluar
        window.history.forward();
        function noBack() {
            window.history.forward();
        }
    </script>
</head>
<body onload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard Admin</h1>
            <div class="current-sesi">
                Sesi: <?php echo $selected_sesi; ?>
            </div>
        </div>

        <!-- Pemilih Sesi -->
        <div class="sesi-selector">
            <form method="GET" action="">
                <label for="sesi">Pilih Sesi:</label>
                <select name="sesi" id="sesi" onchange="this.form.submit()">
                    <?php while($sesi = $sesi_result->fetch_assoc()): ?>
                        <option value="<?php echo $sesi['sesi_id']; ?>" 
                                <?php echo ($sesi['sesi_id'] == $selected_sesi) ? 'selected' : ''; ?>>
                            <?php echo $sesi['sesi_id']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <!-- Statistik Keseluruhan -->
        <div class="stats-overview">
            <div class="stat-card">
                <h3>Jumlah Pensyarah</h3>
                <div class="number"><?php echo $total_pensyarah; ?></div>
                <div class="description">Aktif dalam sesi ini</div>
            </div>
            <div class="stat-card">
                <h3>Jumlah Kursus</h3>
                <div class="number"><?php echo $total_kursus; ?></div>
                <div class="description">Berdaftar dalam sesi ini</div>
            </div>
            <div class="stat-card">
                <h3>Jumlah Dokumen</h3>
                <div class="number"><?php echo $total_dokumen; ?></div>
                <div class="description">Untuk sesi ini</div>
            </div>
        </div>

        <!-- Status Penghantaran -->
        <div class="progress-section">
            <h2>Status Penghantaran</h2>
            <?php while($stat = $submission_stats->fetch_assoc()): ?>
                <div class="progress-card">
                    <h3>Serahan <?php echo $stat['serahan_no']; ?></h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($stat['submitted']/$stat['total']*100); ?>%"></div>
                    </div>
                    <p><?php echo $stat['submitted']; ?> daripada <?php echo $stat['total']; ?> dokumen telah dihantar</p>
                    <?php
                    $deadline_query = "SELECT deadline_date FROM deadlines WHERE serahan_no = " . $stat['serahan_no'];
                    $deadline = $conn->query($deadline_query)->fetch_assoc();
                    if($deadline):
                    ?>
                        <div class="deadline-info">
                            Tarikh Akhir: <?php echo date('d/m/Y', strtotime($deadline['deadline_date'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Status Semakan -->
        <div class="progress-section">
            <h2>Status Semakan</h2>
            <?php while($stat = $checking_stats->fetch_assoc()): ?>
                <div class="progress-card">
                    <h3>Serahan <?php echo $stat['serahan_no']; ?></h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($stat['checked']/$stat['total']*100); ?>%"></div>
                    </div>
                    <p><?php echo $stat['checked']; ?> daripada <?php echo $stat['total']; ?> dokumen telah disemak</p>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Status Pengesahan -->
        <div class="progress-section">
            <h2>Status Pengesahan</h2>
            <?php while($stat = $verification_stats->fetch_assoc()): ?>
                <div class="progress-card">
                    <h3>Serahan <?php echo $stat['no_serahan']; ?></h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo ($stat['verified']/$stat['total_courses']*100); ?>%"></div>
                    </div>
                    <p><?php echo $stat['verified']; ?> daripada <?php echo $stat['total_courses']; ?> kursus telah disahkan</p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html> 