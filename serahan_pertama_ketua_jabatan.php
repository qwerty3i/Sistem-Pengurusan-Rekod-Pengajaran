<!DOCTYPE html>
<?php
// serahan_pertama.php

include('database.php');
session_start();

// Ensure the user is logged in and has the role of 'ketua bahagian'
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'ketua bahagian') {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id']; // User ID from session

// Fetch the record based on ID from the session
$sql = "SELECT * FROM pensyarah WHERE id_users = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $pensyarah = $result->fetch_assoc();
} else {
    echo "No record found!";
    exit();
}

// Set the lecturer ID
$pensyarah_id = $pensyarah['id'];

// Fetch the pensyarah's courses first
$sesi_query = "SELECT DISTINCT k.sesi 
               FROM kursus k 
               WHERE k.pensyarah_id = (SELECT id FROM pensyarah WHERE id_users = ?)
               ORDER BY k.sesi DESC";
$stmt = $conn->prepare($sesi_query);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$sesi_result = $stmt->get_result();

// Get selected session or set default to latest
$selected_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : '';
if (empty($selected_sesi) && $sesi_result->num_rows > 0) {
    $sesi_result->data_seek(0);
    $latest_sesi = $sesi_result->fetch_assoc();
    $selected_sesi = $latest_sesi['sesi'];
}

// Fetch courses for the selected session
$courses_query = "SELECT k.* 
                 FROM kursus k
                 WHERE k.pensyarah_id = (SELECT id FROM pensyarah WHERE id_users = ?) 
                 AND k.sesi = ? 
                 ORDER BY k.semester ASC, k.kod_kursus ASC";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("is", $_SESSION['id'], $selected_sesi);
$stmt->execute();
$courses_result = $stmt->get_result();

// Fetch deadlines
$serahan_pertama_date = '';
$serahan_kedua_date = '';
$deadline_query = "SELECT serahan_no, deadline_date FROM deadlines WHERE serahan_no IN (1, 2)";
$deadline_result = $conn->query($deadline_query);

$formatted_serahan_pertama_date = '';
$formatted_serahan_kedua_date = '';

if ($deadline_result && $deadline_result->num_rows > 0) {
    while ($row = $deadline_result->fetch_assoc()) {
        if ($row['serahan_no'] == 1) {
            $serahan_pertama_date = $row['deadline_date'];
            $date = new DateTime($serahan_pertama_date);
            $formatted_serahan_pertama_date = $date->format('d/m/Y'); // Format to DD/MM/YYYY
        } elseif ($row['serahan_no'] == 2) {
            $serahan_kedua_date = $row['deadline_date'];
            $date = new DateTime($serahan_kedua_date);
            $formatted_serahan_kedua_date = $date->format('d/m/Y'); // Format to DD/MM/YYYY
        }
    }
}

// Determine if the deadline has passed
$is_past_deadline = false;
if ($serahan_pertama_date) {
    $deadline = new DateTime($serahan_pertama_date);
    $current_date = new DateTime();
    $is_past_deadline = $current_date > $deadline;
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Serahan Pertama</title>
    <style>
        /* Minimalistic Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        th, td {
            padding: 12px 20px;
            text-align: left;
        }

        th {
            background-color: #f9f9f9;
            border-bottom: 2px solid #e0e0e0;
            font-weight: 600;
        }

        tr {
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Button Styling */
        .upload-button, .delete-button {
            padding: 8px 16px;
            margin: 2px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .upload-button {
            background-color: #4CAF50;
            color: #fff;
        }

        .upload-button:hover {
            background-color: #45a049;
        }

        .delete-button {
            background-color: #f44336;
            color: #fff;
        }

        .delete-button:hover {
            background-color: #e53935;
        }

        /* Alert Styling */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            width: 90%;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
        }

        .alert.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert.error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        /* Container Styling */
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Heading Styling */
        h1 {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .course-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #4a90e2;
        }

        .course-info {
            font-size: 0.9em;
            color: #666;
            margin-left: 10px;
        }

        .session-selector {
            margin-bottom: 20px;
        }

        .session-selector select {
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 4px 8px;
        }

        .selection-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .selector-item {
            flex: 1;
        }

        .selector-item label {
            display: block;
            margin-bottom: 8px;
            color: #4a90e2;
            font-weight: 600;
        }

        .selector-item select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: white;
        }

        .selector-item select:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }

        .documents-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include('sidebar_ketua_jabatan.php'); ?>
    <div class="container">
        
        <h1>Serahan Pertama<br>(Deadline: <?php echo htmlspecialchars($formatted_serahan_pertama_date); ?>)</h1>

        <!-- Session and Course Selection -->
        <div class="selection-container">
            <!-- Session selector -->
            <div class="selector-item">
                <form method="GET" id="sessionForm">
                    <label for="sesi">Sesi:</label>
                    <select name="sesi" id="sesi" class="form-select" onchange="this.form.submit()">
                        <option value="">Pilih Sesi</option>
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

            <!-- Course selector - only show if session is selected -->
            <?php if (!empty($selected_sesi)): ?>
            <div class="selector-item">
                <form method="GET" id="courseForm">
                    <input type="hidden" name="sesi" value="<?php echo htmlspecialchars($selected_sesi); ?>">
                    <label for="kursus">Kursus:</label>
                    <select name="kursus" id="kursus" class="form-select" onchange="this.form.submit()">
                        <option value="">Pilih Kursus</option>
                        <?php
                        $courses_result->data_seek(0);
                        while ($course = $courses_result->fetch_assoc()) {
                            $selected = (isset($_GET['kursus']) && $_GET['kursus'] == $course['id']) ? 'selected' : '';
                            echo "<option value='{$course['id']}' {$selected}>";
                            echo htmlspecialchars($course['kod_kursus'] . ' - ' . $course['nama_kursus']);
                            echo " (" . htmlspecialchars($course['program'] . ' - Semester ' . $course['semester']) . ")";
                            echo "</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Document List - only show if course is selected -->
        <?php 
        if (!empty($selected_sesi) && isset($_GET['kursus'])):
            $selected_course_id = $_GET['kursus'];
            
            // Fetch the selected course details
            $course_query = "SELECT * FROM kursus WHERE id = ? AND sesi = ?";
            $stmt = $conn->prepare($course_query);
            $stmt->bind_param("is", $selected_course_id, $selected_sesi);
            $stmt->execute();
            $selected_course = $stmt->get_result()->fetch_assoc();
            
            if ($selected_course):
        ?>
            <div class="documents-container mt-4">
                <h3 class="course-header">
                    <?php echo htmlspecialchars($selected_course['kod_kursus'] . ' - ' . $selected_course['nama_kursus']); ?>
                    <span class="course-info">
                        (<?php echo htmlspecialchars($selected_course['program'] . ' - Semester ' . $selected_course['semester']); ?>)
                    </span>
                </h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Document Name</th>
                            <th>Status</th>
                            <th>Comment</th>
                            <th>Dokumen</th>
                            <th>Action</th>
                            <th>Upload Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch documents for selected course
                        $doc_query = "SELECT * FROM dokumen WHERE kursus_id = ? AND serahan_no = 1 ORDER BY dokumen_id ASC";
                        $doc_stmt = $conn->prepare($doc_query);
                        $doc_stmt->bind_param("i", $selected_course_id);
                        $doc_stmt->execute();
                        $documents = $doc_stmt->get_result();
                        
                        $no = 1;
                        while ($doc = $documents->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($doc['nama_dokumen']); ?></td>
                                <td>
                                    <?php 
                                    if ($doc['status'] == 'Checked') {
                                        echo "Telah disemak";
                                        if (!empty($doc['checked_at'])) {
                                            $check_time = new DateTime($doc['checked_at']);
                                            echo "<br><small style='color: #666;'>(" . $check_time->format('d/m/Y H:i:s') . ")</small>";
                                        }
                                    } else {
                                        echo "Belum disemak";
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($doc['comment']); ?></td>
                                <td>
                                    <?php if (!empty($doc['path_dokumen']) && file_exists($doc['path_dokumen'])): ?>
                                        <a href="<?php echo htmlspecialchars($doc['path_dokumen']); ?>" target="_blank">
                                            <?php echo htmlspecialchars(basename($doc['path_dokumen'])); ?>
                                        </a>
                                    <?php else: ?>
                                        No file uploaded
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="upload_dokumen_ketua_jabatan.php" method="POST" enctype="multipart/form-data" style="display:inline;">
                                        <input type="hidden" name="kursus_id" value="<?php echo $selected_course_id; ?>">
                                        <input type="hidden" name="dokumen_id" value="<?php echo $doc['dokumen_id']; ?>">
                                        <input type="hidden" name="serahan_no" value="1">
                                        <input type="hidden" name="sesi" value="<?php echo htmlspecialchars($selected_sesi); ?>">
                                        <input type="file" name="file" accept=".pdf" required <?php echo $is_past_deadline ? 'disabled' : ''; ?>>
                                        <button type="submit" class="upload-button" <?php echo $is_past_deadline ? 'disabled' : ''; ?>>Upload</button>
                                    </form>
                                    <button class="delete-button" onclick="deleteDocument(<?php echo $doc['dokumen_id']; ?>, 'ketua_jabatan')">Delete</button>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($doc['uploaded_at'])) {
                                        $upload_date = new DateTime($doc['uploaded_at']);
                                        echo htmlspecialchars($upload_date->format('d/m/Y H:i:s'));
                                    } else {
                                        echo 'Not Uploaded';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Tambah bahagian pengesahan di sini -->
                <?php
                // Fetch pengesahan details for the course
                $pengesahan_query = "
                    SELECT pk.*, p.nama_pensyarah 
                    FROM pengesahan_kursus pk 
                    LEFT JOIN pensyarah p ON pk.disahkan_oleh = p.id 
                    WHERE pk.kursus_id = ? AND pk.no_serahan = 1";
                $pengesahan_stmt = $conn->prepare($pengesahan_query);
                $pengesahan_stmt->bind_param("i", $selected_course_id);
                $pengesahan_stmt->execute();
                $pengesahan_result = $pengesahan_stmt->get_result();
                $pengesahan = $pengesahan_result->fetch_assoc();
                ?>

                <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
                    <h4 style="color: #333; margin-bottom: 15px;">Status Pengesahan</h4>
                    <table style="width: 100%; margin-top: 10px;">
                        <tr>
                            <td style="width: 200px; padding: 8px; font-weight: bold;">Status Pengesahan:</td>
                            <td style="padding: 8px;">
                                <?php 
                                if ($pengesahan) {
                                    echo htmlspecialchars($pengesahan['Status_pengesahan']);
                                } else {
                                    echo "Belum Disahkan";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; font-weight: bold;">Disahkan Oleh:</td>
                            <td style="padding: 8px;">
                                <?php 
                                if ($pengesahan && $pengesahan['Status_pengesahan'] == 'telah disahkan') {
                                    echo htmlspecialchars($pengesahan['nama_pensyarah']);
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; font-weight: bold;">Waktu Pengesahan:</td>
                            <td style="padding: 8px;">
                                <?php 
                                if ($pengesahan && $pengesahan['Status_pengesahan'] == 'telah disahkan') {
                                    $waktu = new DateTime($pengesahan['waktu_pengesahan']);
                                    echo $waktu->format('d/m/Y H:i:s');
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; font-weight: bold;">Ulasan:</td>
                            <td style="padding: 8px;">
                                <?php 
                                if ($pengesahan && $pengesahan['ulasan']) {
                                    echo htmlspecialchars($pengesahan['ulasan']);
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php 
            endif;
        endif; 
        ?>
    </div>

    <script>
        // Function to handle delete action
        function deleteDocument(dokumenId, role) {
            if (confirm("Are you sure you want to delete this document?")) {
                window.location.href = `delete_dokumen_ketua_jabatan.php?id=${dokumenId}`;
            }
        }
    </script>
</body>
</html>
