<?php
session_start();
include('database.php');

// Ensure user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = intval($_SESSION['id']);

// Retrieve the Jabatan for the current Ketua Jabatan
$ketua_query = "SELECT jabatan, fakulti FROM pensyarah 
                WHERE ketua_jabatan = 'yes' AND id_users = ? 
                LIMIT 1";
$stmt = $conn->prepare($ketua_query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$ketua_result = $stmt->get_result();

if ($ketua_result->num_rows > 0) {
    $ketua = $ketua_result->fetch_assoc();
    $current_jabatan = $ketua['jabatan'];
    $current_fakulti = $ketua['fakulti'];
} else {
    echo "<p>Ketua Jabatan tidak ditemui atau tidak mempunyai akses.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Pensyarah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .content {
            margin-top: 20px;
            padding: 20px;
        }
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #2c3e50;
            color: white;
            font-weight: 500;
        }
        .badge {
            padding: 8px 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include('sidebar_ketua_jabatan.php'); ?>
    
    <div class="content container">
        <div class="table-container">
            <h2 class="text-center mb-4">Senarai Pensyarah <?php echo htmlspecialchars($current_jabatan); ?></h2>

            <?php
            // Get selected session or set default
            $sesi_query = "SELECT DISTINCT sesi FROM kursus ORDER BY sesi DESC";
            $sesi_result = $conn->query($sesi_query);
            
            $selected_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : '';
            if (empty($selected_sesi) && $sesi_result->num_rows > 0) {
                $latest_sesi = $sesi_result->fetch_assoc();
                $selected_sesi = $latest_sesi['sesi'];
            }
            ?>

            <!-- Session selector -->
            <div class="d-flex justify-content-end mb-3">
                <form method="GET" class="d-flex align-items-center">
                    <label for="sesi" class="me-2">Sesi:</label>
                    <select name="sesi" id="sesi" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
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

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Pensyarah</th>
                            <th>Jumlah Kursus</th>
                            <th>Status Dokumen</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch pensyarah and their courses
                        $sql = "SELECT p.id, p.nama_pensyarah,
                               COUNT(DISTINCT k.id) as jumlah_kursus,
                               COUNT(DISTINCT d.dokumen_id) as total_docs,
                               SUM(CASE WHEN d.status = 'Checked' THEN 1 ELSE 0 END) as checked_docs
                               FROM pensyarah p
                               LEFT JOIN kursus k ON p.id = k.pensyarah_id AND k.sesi = ?
                               LEFT JOIN dokumen d ON k.id = d.kursus_id
                               WHERE p.jabatan = ? AND p.fakulti = ?
                               GROUP BY p.id
                               ORDER BY p.nama_pensyarah";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sss", $selected_sesi, $current_jabatan, $current_fakulti);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['nama_pensyarah']) . "</td>";
                            echo "<td>" . $row['jumlah_kursus'] . " kursus</td>";
                            echo "<td>";
                            if ($row['total_docs'] > 0) {
                                $percentage = round(($row['checked_docs'] / $row['total_docs']) * 100);
                                $badge_class = $percentage == 100 ? 'bg-success' : 
                                             ($percentage > 0 ? 'bg-warning' : 'bg-danger');
                                echo "<span class='badge {$badge_class}'>{$percentage}% Lengkap</span>";
                            } else {
                                echo "<span class='badge bg-secondary'>Tiada Dokumen</span>";
                            }
                            echo "</td>";
                            echo "<td>
                                    <a href='lihat_kursus.php?pensyarah_id=" . $row['id'] . "&sesi=" . $selected_sesi . "' 
                                       class='btn btn-primary btn-sm'>Lihat Kursus</a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
