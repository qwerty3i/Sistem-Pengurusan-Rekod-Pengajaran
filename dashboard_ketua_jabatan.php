<?php
session_start();

// Set header untuk mencegah cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Semak jika pengguna telah log masuk dan adalah ketua jabatan
if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ketua bahagian') {
    echo "<script>
            alert('Sila log masuk sebagai Ketua Bahagian.');
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

$id = $_SESSION['id'];

include('database.php');

// Fetch the pensyarah record
$sql = "SELECT * FROM pensyarah WHERE id_users = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $pensyarah = $result->fetch_assoc();
} else {
    echo "No record found!";
    exit();
}
$stmt->close();

// Fetch deadlines
$deadline_query = "SELECT serahan_no, deadline_date FROM deadlines WHERE serahan_no IN (1, 2)";
$deadline_stmt = $conn->prepare($deadline_query);
if (!$deadline_stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$deadline_stmt->execute();
$deadline_result = $deadline_stmt->get_result();

$serahan_dates = array();
if ($deadline_result && $deadline_result->num_rows > 0) {
    while ($row = $deadline_result->fetch_assoc()) {
        if ($row['serahan_no'] == 1) {
            $serahan_dates['serahan1_date'] = $row['deadline_date'];
        } elseif ($row['serahan_no'] == 2) {
            $serahan_dates['serahan2_date'] = $row['deadline_date'];
        }
    }
} else {
    $serahan_dates['serahan1_date'] = 'N/A';
    $serahan_dates['serahan2_date'] = 'N/A';
}
$deadline_stmt->close();

// Fetch courses for current jabatan
$sesi_query = "SELECT DISTINCT sesi 
               FROM kursus 
               WHERE pensyarah_id = ?
               ORDER BY sesi DESC";
$stmt = $conn->prepare($sesi_query);
$stmt->bind_param("i", $pensyarah['id']);
$stmt->execute();
$sesi_result = $stmt->get_result();

// Get selected session or set default to latest
$selected_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : '';
if (empty($selected_sesi) && $sesi_result->num_rows > 0) {
    $sesi_result->data_seek(0);
    $latest_sesi = $sesi_result->fetch_assoc();
    $selected_sesi = $latest_sesi['sesi'];
}

// Fetch courses for the jabatan with document status
$sql = "SELECT k.*, 
        SUM(CASE WHEN d.serahan_no = 1 AND d.status = 'Checked' THEN 1 ELSE 0 END) as checked_docs_1,
        SUM(CASE WHEN d.serahan_no = 1 THEN 1 ELSE 0 END) as total_docs_1,
        SUM(CASE WHEN d.serahan_no = 2 AND d.status = 'Checked' THEN 1 ELSE 0 END) as checked_docs_2,
        SUM(CASE WHEN d.serahan_no = 2 THEN 1 ELSE 0 END) as total_docs_2
        FROM kursus k 
        LEFT JOIN dokumen d ON k.id = d.kursus_id
        WHERE k.pensyarah_id = ? 
        AND k.sesi = ?
        GROUP BY k.id
        ORDER BY k.semester ASC, k.nama_kursus ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $pensyarah['id'], $selected_sesi);
$stmt->execute();
$courses_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ketua Jabatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-container {
            margin-top: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        /* Serahan Table Styling */
        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .table-dark th {
            background-color: #2c3e50;
            color: white;
            font-weight: 500;
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        /* Profile Info Styling */
        .profile-info {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        .profile-info h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: 600;
            color: #4a90e2;
            margin-bottom: 5px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.1em;
            color: #333;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }

        /* Course Table Styling */
        .table-responsive {
            margin-top: 20px;
        }

        .table-hover tbody tr:hover {
            background-color: #f5f8fa;
        }

        .table-light th {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        /* Badge Styling */
        .badge {
            padding: 8px 12px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        .bg-danger {
            background-color: #dc3545 !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }

        /* Session Selector Styling */
        .sesi-select {
            display: flex;
            align-items: center;
        }

        .sesi-select label {
            color: #4a90e2;
            font-weight: 600;
            margin-right: 10px;
        }

        .form-select-sm {
            padding: 0.25rem 2rem 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 4px;
            border: 1px solid #ced4da;
            background-color: white;
            cursor: pointer;
        }

        .form-select-sm:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }

        /* Alert Styling */
        .alert-info {
            background-color: #e1f5fe;
            color: #0288d1;
            border: none;
            border-radius: 8px;
            padding: 15px 20px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .form-container {
                padding: 0 10px;
            }

            .profile-info {
                padding: 20px;
            }

            .table td, .table th {
                padding: 10px;
            }

            .badge {
                padding: 5px 8px;
            }
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
    <?php include('sidebar_ketua_jabatan.php'); ?>
    <div class="content container form-container">
        <!-- Logo Section -->
        <div class="logo">
            <img src="UnipSAS-LOGO.png" alt="Logo" width="550">
        </div>

        <!-- Serahan Table -->
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Serahan 1</th>
                    <th>Serahan 2</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php 
                            if (isset($serahan_dates['serahan1_date']) && $serahan_dates['serahan1_date'] != 'N/A') {
                                $date = new DateTime($serahan_dates['serahan1_date']);
                                echo $date->format('d/m/Y');
                            } else {
                                echo 'N/A';
                            }
                        ?>
                    </td>
                    <td>
                        <?php 
                            if (isset($serahan_dates['serahan2_date']) && $serahan_dates['serahan2_date'] != 'N/A') {
                                $date = new DateTime($serahan_dates['serahan2_date']);
                                echo $date->format('d/m/Y');
                            } else {
                                echo 'N/A';
                            }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Profile Information -->
        <div class="profile-info">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Maklumat Ketua Jabatan</h2>
            </div>
            
            <div class="info-item">
                <div class="info-label">Nama Ketua Jabatan</div>
                <div class="info-value"><?php echo htmlspecialchars($pensyarah['nama_pensyarah']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Jabatan</div>
                <div class="info-value"><?php echo htmlspecialchars($pensyarah['jabatan']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Fakulti</div>
                <div class="info-value"><?php echo htmlspecialchars($pensyarah['fakulti']); ?></div>
            </div>
        </div>

        <!-- Maklumat Kursus -->
        <div class="profile-info mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Maklumat Kursus</h2>
                <div class="sesi-select">
                    <form method="GET" class="d-flex align-items-center">
                        <label for="sesi" class="me-2">Sesi:</label>
                        <select name="sesi" id="sesi" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php
                            $sesi_result->data_seek(0);
                            while ($sesi = $sesi_result->fetch_assoc()) {
                                $selected = ($sesi['sesi'] == $selected_sesi) ? 'selected' : '';
                                echo "<option value='{$sesi['sesi']}' {$selected}>{$sesi['sesi']}</option>";
                            }
                            ?>
                        </select>
                    </form>
                </div>
            </div>
            
            <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Kod Kursus</th>
                                <th>Nama Kursus</th>
                                <th>Program</th>
                                <th>Semester</th>
                                <th>Serahan 1</th>
                                <th>Serahan 2</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['kod_kursus']); ?></td>
                                    <td><?php echo htmlspecialchars($course['nama_kursus']); ?></td>
                                    <td><?php echo htmlspecialchars($course['program']); ?></td>
                                    <td><?php echo htmlspecialchars($course['semester']); ?></td>
                                    <td>
                                        <?php 
                                            $checked_1 = $course['checked_docs_1'] ?? 0;
                                            $total_1 = $course['total_docs_1'] ?? 0;
                                            $badge_class_1 = $total_1 > 0 ? 
                                                ($checked_1 == $total_1 ? 'bg-success' : 
                                                ($checked_1 > 0 ? 'bg-warning' : 'bg-danger')) : 
                                                'bg-secondary';
                                            echo "<span class='badge {$badge_class_1}'>{$checked_1}/{$total_1} Dokumen</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $checked_2 = $course['checked_docs_2'] ?? 0;
                                            $total_2 = $course['total_docs_2'] ?? 0;
                                            $badge_class_2 = $total_2 > 0 ? 
                                                ($checked_2 == $total_2 ? 'bg-success' : 
                                                ($checked_2 > 0 ? 'bg-warning' : 'bg-danger')) : 
                                                'bg-secondary';
                                            echo "<span class='badge {$badge_class_2}'>{$checked_2}/{$total_2} Dokumen</span>";
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Tiada kursus yang didaftarkan untuk sesi ini.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
