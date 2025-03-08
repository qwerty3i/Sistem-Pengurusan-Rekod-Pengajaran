<?php
session_start();
include('database.php');

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_REQUEST['serahan_no'])) {
    $serahan_no = intval($_REQUEST['serahan_no']);
} else {
    echo "Serahan number not specified.";
    exit;
}

$current_user_id = intval($_SESSION['id']);

// Get current ketua jabatan's info
$ketua_query = "SELECT p.*, u.username 
                FROM pensyarah p 
                JOIN users u ON p.id_users = u.id 
                WHERE p.ketua_jabatan = 'yes' AND p.id_users = ? 
                LIMIT 1";
$stmt = $conn->prepare($ketua_query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$ketua_result = $stmt->get_result();

// Check if ketua jabatan exists
if ($ketua_result->num_rows === 0) {
    echo "<p>Error: Anda tidak mempunyai akses sebagai ketua jabatan.</p>";
    exit;
}

$ketua = $ketua_result->fetch_assoc();

// Verify ketua data is complete
if (empty($ketua['jabatan']) || empty($ketua['fakulti'])) {
    echo "<p>Error: Data ketua jabatan tidak lengkap.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management System - Fail Semakan</title>
    <style>
        .course-info {
            background-color: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #4a90e2;
        }

        .course-info strong {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .course-info ul {
            margin: 0;
            padding-left: 20px;
        }
        /* Basic Styling */
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; color: #333; }
        h1 { text-align: center; margin-top: 20px; color: #333; }
        .accordion { max-width: 700px; margin: 20px auto; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px; background: #fff; }
        .accordion-header { background-color: #4a90e2; color: #fff; padding: 15px 20px; cursor: pointer; font-size: 18px; font-weight: bold; border-radius: 8px; }
        .accordion-content { background-color: #f4f4f9; display: none; padding: 0 20px 20px 20px; font-size: 16px; color: #333; border-bottom: 1px solid #ddd; }
        .department-header { background-color: #72b0e0; color: #fff; padding: 10px 15px; cursor: pointer; font-size: 16px; font-weight: bold; margin: 5px 0; border-radius: 5px; }
        .department-content { display: none; padding-left: 20px; font-size: 15px; color: #333; }
        .lecturer-header { background-color: #b2d3f2; color: #333; padding: 8px 15px; cursor: pointer; font-size: 15px; font-weight: bold; margin: 5px 0; border-radius: 5px; }
        .lecturer-content { display: none; padding-left: 20px; font-size: 1rem; color: #333; }
        .lecturer-content ul { list-style-type: none; padding-left: 0; }
        .lecturer-content li { padding: 5px 0; font-weight: 500; }
        .accordion-header::after, .department-header::after, .lecturer-header::after { content: "\25BC"; float: right; transition: transform 0.3s; }
        .accordion-header.active::after, .department-header.active::after, .lecturer-header.active::after { transform: rotate(180deg); }

        /* Form Styling */
        form { margin-top: 10px; }
        .form-group { display: flex; align-items: center; gap: 10px; } /* Flex container for alignment */
        .form-check-label { font-size: 1rem; display: inline-flex; align-items: center; }
        .form-input { font-size: 1rem; padding: 8px 10px; border-radius: 4px; border: 1px solid #ddd; width: 100%; max-width: 250px; }
        .form-input[type="checkbox"] { transform: scale(1.5); margin-right: 10px; } /* Scale and margin for checkbox */
        .form-inline-group { display: flex; align-items: center; gap: 10px; }
        .form-button { font-size: 1rem; padding: 8px 12px; background-color: #4a90e2; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .form-button:hover { background-color: #357ABD; }

        /* Download Button Styling */
        .document-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            margin-bottom: 0;
        }
        .download-button { font-size: 1rem; padding: 6px 12px; background-color: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .download-button:hover { background-color: #218838; }
        .status-text { font-size: 1rem; color: #333; font-weight: 500; margin-top: 5px; }

        /* Add new styles for session selection */
        .session-controls-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 20px auto;
            max-width: 600px;
        }

        .session-select {
            flex: 1;
            max-width: 300px;
            margin: 0;
        }

        .session-select select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            width: 100%;
            padding: 8px;
            font-size: 1rem;
        }

        .session-select select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        /* Add new CSS for course header */
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            cursor: pointer;
            margin-bottom: 4px;
            min-height: 28px;
            font-size: 0.9em;
        }

        .course-info {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .course-code {
            font-weight: 600;
            color: #0d6efd;
            min-width: 65px;
        }

        .course-name {
            font-weight: 500;
            color: #212529;
            margin-right: 8px;
        }

        .course-details {
            color: #6c757d;
            font-size: 0.9em;
        }

        .toggle-icon {
            font-size: 0.8em;
            color: #6c757d;
            margin-left: 8px;
        }

        .separator {
            color: #dee2e6;
            margin: 0 6px;
        }

        /* Update existing CSS */
        .document-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            margin-bottom: 0;
        }

        .form-inline-group {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 5px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .form-check-label {
            margin: 0;
            white-space: nowrap;
        }

        .form-input[type="checkbox"] {
            margin: 0;
            width: 18px;
            height: 18px;
        }

        .download-button {
            padding: 6px 12px;
            white-space: nowrap;
            display: inline-block;
            text-align: center;
            min-width: 120px;
        }

        .status-text {
            margin: 5px 0 0;
            text-align: right;
            font-size: 0.9rem;
        }

        /* Add new styles */
        .text-muted {
            color: #6c757d;
        }

        hr {
            margin: 15px 0;
            border: 0;
            border-top: 1px solid #dee2e6;
        }

        .course-content {
            padding: 15px 20px;
        }

        /* Container styles */
        .controls-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .status-checkbox-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        /* Document container and layout */
        .document-container {
            margin-bottom: 20px;
        }

        .document-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .document-name {
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .document-controls {
            display: flex;
            gap: 20px;
        }

        /* Status and checkbox section */
        .status-section {
            flex: 1;
            min-width: 200px;
        }

        .form-inline-group {
            margin-bottom: 8px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-text {
            font-size: 0.9rem;
            color: #666;
        }

        /* Comments section */
        .comments-section {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .comment-input {
            width: 100%;
            min-height: 60px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.9rem;
            resize: vertical;
        }

        .save-comment-btn {
            align-self: flex-end;
            padding: 6px 15px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .save-comment-btn:hover {
            background-color: #357abd;
        }

        .save-comment-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        /* Success animation */
        @keyframes saveSuccess {
            0% { background-color: #4a90e2; }
            50% { background-color: #28a745; }
            100% { background-color: #4a90e2; }
        }

        .save-success {
            animation: saveSuccess 1.5s ease;
        }

        .submit-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .submit-btn {
            padding: 8px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #218838;
        }

        .submit-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        /* Success animation */
        @keyframes submitSuccess {
            0% { background-color: #28a745; }
            50% { background-color: #218838; }
            100% { background-color: #28a745; }
        }

        .submit-success {
            animation: submitSuccess 1.5s ease;
        }

        /* Table styling */
        .document-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .document-table th,
        .document-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .document-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: left;
        }

        .document-row:hover {
            background-color: #f8f9fa;
        }

        /* Column specific styles */
        .document-name {
            font-weight: 500;
            color: #2c3e50;
            min-width: 200px;
        }

        .status-cell {
            min-width: 150px;
        }

        .comment-cell {
            width: 40%;
        }

        .action-cell {
            white-space: nowrap;
            text-align: center;
        }

        /* Form elements in table */
        .comment-input {
            width: 100%;
            min-height: 60px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.9rem;
            resize: vertical;
        }

        .download-button {
            padding: 6px 12px;
            margin-right: 8px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }

        .download-button:hover {
            background-color: #357abd;
            color: white;
        }

        .submit-btn {
            padding: 6px 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-check {
            margin-bottom: 5px;
        }

        .status-text {
            font-size: 0.9rem;
        }

        .text-muted {
            color: #6c757d;
            font-style: italic;
        }

        .content-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .lecturer-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .lecturer-name {
            background: #4a90e2;
            color: white;
            padding: 15px 20px;
            font-size: 1.2rem;
            font-weight: 500;
        }

        .course-card {
            border: 1px solid #dee2e6;
            margin-bottom: 10px;
            background: white;
        }

        .course-header {
            padding: 15px 20px;
            background: #f8f9fa;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: background-color 0.3s;
        }

        .course-content {
            padding: 20px;
            background: white;
            display: none; /* Hidden by default */
        }

        /* Ensure table is visible when container is shown */
        .course-content.active {
            display: block;
        }

        .table-responsive {
            overflow-x: auto;
            margin-top: 10px;
        }

        /* Debug styles */
        .course-content[style*="display: block"] {
            border: 1px solid #ddd;
            margin: 10px 0;
        }

        /* Make sure table takes full width */
        .document-table {
            width: 100%;
            margin-bottom: 0;
        }

        .courses-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .course-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden; /* Ensure the background color doesn't spill */
        }

        .course-info {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        .lecturer-name {
            background: #4a90e2; /* Match with button color */
            color: white;
            padding: 10px 20px;
            margin: -15px -20px 10px -20px; /* Negative margin to extend to edges */
            font-weight: 500;
            font-size: 1em;
        }

        .course-details {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 15px 0 10px 0; /* Adjusted margin */
        }

        .view-docs-btn {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .documents-section {
            padding: 20px;
        }

        .docs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .docs-table th,
        .docs-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .docs-table th {
            background: #f8f9fa;
            font-weight: 500;
        }

        .status-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .comment-box {
            width: 100%;
            min-height: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-download,
        .btn-submit {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.9em;
        }

        .btn-download {
            background: #28a745;
            color: white;
            text-decoration: none;
        }

        .btn-submit {
            background: #4a90e2;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }

        /* Lecturer Card */
        .lecturer-card {
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .lecturer-header {
            background: #4a90e2;
            color: white;
            padding: 15px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lecturer-name {
            font-size: 1.1em;
            font-weight: 500;
        }

        /* Course Card */
        .courses-container {
            background: white;
            padding: 15px;
        }

        .course-card {
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .course-header {
            padding: 12px 15px;
            background: #f8f9fa;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .course-code {
            font-weight: 600;
            color: #2c3e50;
            min-width: 100px;
        }

        .course-name {
            flex: 1;
        }

        /* Documents Table */
        .documents-container {
            padding: 15px;
        }

        .documents-table {
            width: 100%;
            border-collapse: collapse;
        }

        .documents-table th,
        .documents-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .documents-table th {
            background: #f8f9fa;
            font-weight: 500;
        }

        /* Form Elements */
        .status-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .comment-input {
            width: 100%;
            min-height: 60px;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            resize: vertical;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-download,
        .btn-submit {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        .btn-download {
            background: #28a745;
            color: white;
            text-decoration: none;
        }

        .btn-submit {
            background: #4a90e2;
            color: white;
        }

        .toggle-icon {
            transition: transform 0.3s;
        }

        .active .toggle-icon {
            transform: rotate(180deg);
        }

        .upload-time {
            white-space: nowrap;
            color: #666;
            font-size: 0.9em;
            text-align: center;
        }

        .upload-time {
            white-space: nowrap;
            color: #666;
            font-size: 0.9em;
            text-align: center;
        }

        .documents-table th,
        .documents-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
            vertical-align: middle; /* Align content vertically */
        }

        /* Adjust column widths */
        .documents-table {
            table-layout: fixed;
            width: 100%;
        }

        .documents-table th:nth-child(1) { width: 20%; } /* Nama Dokumen */
        .documents-table th:nth-child(2) { width: 15%; } /* Status */
        .documents-table th:nth-child(3) { width: 25%; } /* Komen */
        .documents-table th:nth-child(4) { width: 15%; } /* Masa Hantar */
        .documents-table th:nth-child(5) { width: 25%; } /* Tindakan */

        /* Make sure the table is scrollable on mobile */
        @media screen and (max-width: 768px) {
            .documents-container {
                overflow-x: auto;
            }
            
            .documents-table {
                min-width: 800px;
            }
        }

        .upload-time {
            white-space: nowrap;
            text-align: center;
            font-size: 0.9em;
        }

        .upload-time.not-uploaded {
            color: #dc3545; /* Red color for not uploaded */
        }

        .not-uploaded-text {
            color: #dc3545;
            font-style: italic;
        }

        /* Rest of your existing table CSS */
        .documents-table th,
        .documents-table td {
            padding: 12px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        /* Column widths */
        .documents-table {
            table-layout: fixed;
            width: 100%;
        }

        .documents-table th:nth-child(1) { width: 20%; } /* Nama Dokumen */
        .documents-table th:nth-child(2) { width: 15%; } /* Status */
        .documents-table th:nth-child(3) { width: 25%; } /* Komen */
        .documents-table th:nth-child(4) { width: 15%; } /* Masa Hantar */
        .documents-table th:nth-child(5) { width: 25%; } /* Tindakan */

        .status-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-label {
            font-size: 0.9em;
            font-weight: 500;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Optional: Add hover effect to checkbox */
        input[type="checkbox"]:hover {
            opacity: 0.8;
        }

        .status-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .check-time {
            font-size: 0.8em;
            color: #666;
            font-style: italic;
        }

        .status-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .status-check input[type="checkbox"]:disabled {
            opacity: 0.5
        }

        .status-check input[type="checkbox"]:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .status-check input[type="checkbox"]:disabled + .status-info .status-label {
            opacity: 0.7;
        }

        .not-uploaded {
            color: #6c757d !important; /* Grey color for not uploaded */
            font-style: italic;
        }

        /* Optional: Add tooltip for disabled checkbox */
        .status-check {
            position: relative;
        }

        .status-check input[type="checkbox"]:disabled:hover::after {
            content: "Dokumen belum dihantar";
            position: absolute;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            left: 25px;
            top: -25px;
        }

        .status-checkbox:disabled,
        .comment-box:disabled,
        textarea:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            background-color: #e9ecef;
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('sidebar_ketua_jabatan.php'); ?>

    <h1>Fail Semakan - <?php echo ($serahan_no == 1) ? 'Serahan Pertama' : 'Serahan Kedua'; ?></h1>

    <div class="session-controls-container">
        <form method="GET" class="session-select">
            <input type="hidden" name="serahan_no" value="<?php echo $serahan_no; ?>">
            <select name="sesi" class="form-select" onchange="this.form.submit()" required>
                <option value="">Pilih Sesi</option>
                <?php
                $sesi_query = "SELECT DISTINCT sesi FROM kursus ORDER BY sesi DESC";
                $sesi_result = $conn->query($sesi_query);
                while($sesi_row = $sesi_result->fetch_assoc()) {
                    $selected = (isset($_GET['sesi']) && $_GET['sesi'] == $sesi_row['sesi']) ? 'selected' : '';
                    echo "<option value='{$sesi_row['sesi']}' {$selected}>{$sesi_row['sesi']}</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <?php if(isset($_GET['sesi'])): 
        $sesi = $_GET['sesi'];
        
        // Get unique lecturers first (excluding the current logged-in ketua jabatan)
        $query = "SELECT DISTINCT 
                    p.id as pensyarah_id,
                    p.nama_pensyarah
                  FROM pensyarah p 
                  JOIN kursus k ON p.id = k.pensyarah_id 
                  WHERE k.sesi = ? 
                  AND p.jabatan = ?
                  AND p.id_users != ?  -- Exclude current logged-in user
                  ORDER BY p.nama_pensyarah";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $sesi, $ketua['jabatan'], $_SESSION['id']);
        $stmt->execute();
        $result = $stmt->get_result();
    ?>

    <div class="container">
        <?php while ($pensyarah = $result->fetch_assoc()): ?>
            <div class="lecturer-card">
                <!-- Lecturer Header -->
                <div class="lecturer-header" onclick="toggleLecturer(<?php echo $pensyarah['pensyarah_id']; ?>)">
                    <span class="lecturer-name"><?php echo htmlspecialchars($pensyarah['nama_pensyarah']); ?></span>
                    <span class="toggle-icon">▼</span>
                </div>

                <!-- Courses Container -->
                <div id="lecturer-<?php echo $pensyarah['pensyarah_id']; ?>" class="courses-container" style="display:none;">
                    <?php
                    // Get courses for this lecturer
                    $course_query = "SELECT id as kursus_id, kod_kursus, nama_kursus, program, semester 
                                   FROM kursus 
                                   WHERE pensyarah_id = ? AND sesi = ?
                                   ORDER BY kod_kursus";
                    $course_stmt = $conn->prepare($course_query);
                    $course_stmt->bind_param("is", $pensyarah['pensyarah_id'], $sesi);
                    $course_stmt->execute();
                    $courses = $course_stmt->get_result();
                    ?>

                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <div class="course-card">
                            <!-- Course Header -->
                            <div class="course-header" onclick="toggleCourse(<?php echo $course['kursus_id']; ?>)">
                                <div class="course-info">
                                    <span class="course-code"><?php echo htmlspecialchars($course['kod_kursus']); ?></span>
                                    <span class="course-name"><?php echo htmlspecialchars($course['nama_kursus']); ?></span>
                                    <span class="separator">|</span>
                                    <span class="course-details">
                                        <?php echo htmlspecialchars($course['program']); ?> - Semester <?php echo htmlspecialchars($course['semester']); ?>
                                    </span>
                                </div>
                                <span class="toggle-icon">▼</span>
                            </div>

                            <!-- Documents Table -->
                            <div id="course-<?php echo $course['kursus_id']; ?>" class="documents-container" style="display:none;">
                                <?php
                                $doc_query = "SELECT * FROM dokumen 
                                            WHERE kursus_id = ? AND serahan_no = ? 
                                            ORDER BY nama_dokumen";
                                $doc_stmt = $conn->prepare($doc_query);
                                $doc_stmt->bind_param("ii", $course['kursus_id'], $serahan_no);
                                $doc_stmt->execute();
                                $documents = $doc_stmt->get_result();
                                ?>

                                <table class="documents-table">
                                    <thead>
                                        <tr>
                                            <th>Nama Dokumen</th>
                                            <th>Status</th>
                                            <th>Komen</th>
                                            <th>Masa Hantar</th>
                                            <th>Tindakan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($doc = $documents->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($doc['nama_dokumen']); ?></td>
                                                <td>
                                                    <div class="status-check">
                                                        <input type="checkbox" 
                                                               id="status-<?php echo $doc['dokumen_id']; ?>"
                                                               <?php echo ($doc['status'] === 'Checked') ? 'checked' : ''; ?>
                                                               <?php echo (empty($doc['path_dokumen'])) ? 'disabled' : ''; ?>
                                                               onchange="submitDocument(<?php echo $doc['dokumen_id']; ?>)"
                                                               >
                                                        <div class="status-info">
                                                            <span class="status-label" style="color: <?php echo ($doc['status'] === 'Checked') ? '#28a745' : '#dc3545'; ?>">
                                                                <?php 
                                                                if (empty($doc['path_dokumen'])) {
                                                                    echo '<span class="not-uploaded">Belum hantar</span>';
                                                                } else {
                                                                    echo ($doc['status'] === 'Checked') ? 'Telah disemak' : 'Belum disemak';
                                                                }
                                                                ?>
                                                            </span>
                                                            <?php if ($doc['status'] === 'Checked' && $doc['checked_at']): ?>
                                                                <span class="check-time">
                                                                    disemak pada <?php 
                                                                        $timestamp = strtotime($doc['checked_at']);
                                                                        echo date('d/m/Y g:i A', $timestamp);
                                                                    ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <textarea class="comment-input" 
                                                              id="comment-<?php echo $doc['dokumen_id']; ?>"
                                                              placeholder="Tambah komen..."><?php 
                                                        echo htmlspecialchars($doc['comment'] ?? ''); 
                                                    ?></textarea>
                                                </td>
                                                <td class="upload-time <?php echo empty($doc['path_dokumen']) ? 'not-uploaded' : ''; ?>">
                                                    <?php 
                                                        if (!empty($doc['path_dokumen']) && !empty($doc['uploaded_at'])) {
                                                            $date = new DateTime($doc['uploaded_at']);
                                                            echo $date->format('d/m/Y H:i'); 
                                                        } else {
                                                            echo '<span class="not-uploaded-text">Belum hantar</span>';
                                                        }
                                                    ?>
                                                </td>
                                                <td class="action-buttons">
                                                    <?php if (!empty($doc['path_dokumen'])): ?>
                                                        <a href="download.php?id=<?php echo $doc['dokumen_id']; ?>" 
                                                           class="btn-download">Muat Turun</a>
                                                        <a href="<?php echo htmlspecialchars($doc['path_dokumen']); ?>" 
                                                           class="btn-download" 
                                                           target="_blank" 
                                                           style="background-color: #17a2b8;">Lihat</a>
                                                    <?php endif; ?>
                                                    <button class="btn-submit" 
                                                            onclick="submitDocument(<?php echo $doc['dokumen_id']; ?>)">
                                                        Hantar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <div class="pengesahan-section" style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                                    <h4>Pengesahan Kursus</h4>
    
        <?php
        // Dapatkan maklumat pengesahan sedia ada jika ada
        $pengesahan_query = "SELECT pk.*, p.nama_pensyarah 
                            FROM pengesahan_kursus pk
                            LEFT JOIN pensyarah p ON pk.disahkan_oleh = p.id
                            WHERE pk.kursus_id = ? AND pk.no_serahan = ?
                            ORDER BY pk.waktu_pengesahan DESC
                            LIMIT 1";
                            $stmt = $conn->prepare($pengesahan_query);
                            $stmt->bind_param("ii", $course['kursus_id'], $serahan_no);
                            $stmt->execute();
                            $pengesahan = $stmt->get_result()->fetch_assoc();
                            ?>

                            <form id="pengesahanForm-<?php echo $course['kursus_id']; ?>" 
                              class="pengesahan-form" 
                              onsubmit="return submitPengesahan(<?php echo $course['kursus_id']; ?>, event)">
        
                                <input type="hidden" name="no_serahan" value="<?php echo $serahan_no; ?>">
        
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label for="ulasan-<?php echo $course['kursus_id']; ?>">Ulasan:</label>
                                    <textarea id="ulasan-<?php echo $course['kursus_id']; ?>" 
                                      name="ulasan" 
                                      class="form-control" 
                                      rows="3"
                                      style="width: 100%;"><?php echo $pengesahan['ulasan'] ?? ''; ?></textarea>
                                </div>

        <div class="info-group" style="margin-bottom: 15px;">
            <p><strong>Disahkan oleh:</strong> 
                <?php 
                if ($pengesahan && $pengesahan['Status_pengesahan'] == 'telah disahkan') {
                    echo htmlspecialchars($pengesahan['nama_pensyarah']);
                } else {
                    echo htmlspecialchars($ketua['nama_pensyarah'] ?? '-');
                }
                ?>
            </p>
            <p><strong>Waktu Pengesahan:</strong> 
                <?php echo $pengesahan ? date('d/m/Y H:i', strtotime($pengesahan['waktu_pengesahan'])) : '-'; ?>
            </p>
            <p><strong>Status Pengesahan:</strong> 
                <span class="status-badge" style="
                    padding: 5px 10px;
                    border-radius: 4px;
                    background-color: <?php echo $pengesahan && $pengesahan['Status_pengesahan'] == 'telah disahkan' ? '#28a745' : '#dc3545'; ?>;
                    color: white;
                ">
                    <?php echo $pengesahan ? ucfirst($pengesahan['Status_pengesahan']) : 'Belum disahkan'; ?>
                </span>
            </p>
        </div>

            <div class="button-group" style="display: flex; gap: 10px;">
                <button type="submit" 
                    name="status" 
                    value="telah disahkan" 
                    class="btn-submit" 
                    style="background-color: #28a745;">
                    Sahkan
                </button>
                <button type="submit" 
                        name="status" 
                        value="belum disahkan" 
                        class="btn-submit" 
                        style="background-color: #dc3545;">
                    Batal Pengesahan
                </button>
            </div>
                                            </form>
                            </div>

                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php endif; ?>

    <script>
        function toggleLecturer(pensyarahId) {
            const container = document.getElementById('lecturer-' + pensyarahId);
            const header = event.currentTarget;
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                header.classList.add('active');
            } else {
                container.style.display = 'none';
                header.classList.remove('active');
            }
        }

        function toggleCourse(kursusId) {
            const container = document.getElementById('course-' + kursusId);
            const header = event.currentTarget;
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                header.classList.add('active');
            } else {
                container.style.display = 'none';
                header.classList.remove('active');
            }
        }

        function submitDocument(docId) {
            const checkbox = document.getElementById('status-' + docId);
            
            // Don't proceed if checkbox is disabled
            if (checkbox.disabled) {
                return;
            }
            
            const status = checkbox.checked ? 'Checked' : 'Not Checked';
            const statusContainer = checkbox.nextElementSibling;
            const comment = document.getElementById('comment-' + docId).value;
            const button = event.currentTarget;
            
            button.disabled = true;
            button.innerHTML = 'Menghantar...';
            
            const formData = new FormData();
            formData.append('dokumen_id', docId);
            formData.append('status', status);
            formData.append('comment', comment);

            fetch('update_document.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update status label and check time
                    let statusHtml = `<span class="status-label" style="color: ${checkbox.checked ? '#28a745' : '#dc3545'}">
                        ${checkbox.checked ? 'Telah disemak' : 'Belum disemak'}
                    </span>`;
                    
                    if (checkbox.checked && data.checked_at) {
                        statusHtml += `<span class="check-time">
                            disemak pada ${data.checked_at}
                        </span>`;
                    }
                    
                    statusContainer.innerHTML = statusHtml;
                    
                    // Show success message
                    button.innerHTML = 'Tersimpan!';
                    button.style.backgroundColor = '#28a745';
                    
                    setTimeout(() => {
                        button.innerHTML = 'Hantar';
                        button.style.backgroundColor = '#4a90e2';
                        button.disabled = false;
                    }, 2000);
                } else {
                    console.error('Server response:', data);
                    alert('Ralat semasa mengemaskini dokumen');
                    button.innerHTML = 'Hantar';
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ralat semasa mengemaskini dokumen');
                button.innerHTML = 'Hantar';
                button.disabled = false;
            });
        }
        function submitPengesahan(kursusId, event) {
            event.preventDefault();
            
            const form = document.getElementById('pengesahanForm-' + kursusId);
            const formData = new FormData(form);
            const clickedButton = event.submitter;
            const status = clickedButton.value;
            
            formData.append('status', status);
            formData.append('kursus_id', kursusId);

            // Disable buttons during submission
            const buttons = form.querySelectorAll('button');
            buttons.forEach(button => button.disabled = true);

            fetch('update_pengesahan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berjaya!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload(); // Reload page to show updated status
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat!',
                        text: data.message || 'Ralat tidak diketahui berlaku'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat!',
                    text: 'Ralat semasa mengemaskini pengesahan: ' + error.message
                });
            })
            .finally(() => {
                buttons.forEach(button => button.disabled = false);
            });

            return false;
        }

        function updateUIAfterPengesahan(kursusId, isDisabled) {
            const form = document.getElementById('pengesahanForm-' + kursusId);
            const container = form.closest('.documents-container'); // Add this class to your main container
            
            // Update checkboxes and comment boxes
            const checkboxes = container.querySelectorAll('.status-checkbox');
            const commentBoxes = container.querySelectorAll('.comment-box');
            const ulasanTextarea = form.querySelector('textarea[name="ulasan"]');
            const sahkanButton = form.querySelector('button[value="telah disahkan"]');
            
            if (isDisabled) {
                // Disable elements when pengesahan is confirmed
                checkboxes.forEach(cb => cb.disabled = true);
                commentBoxes.forEach(box => box.disabled = true);
                ulasanTextarea.disabled = true;
                sahkanButton.disabled = true;
            } else {
                // Enable elements when pengesahan is cancelled
                checkboxes.forEach(cb => cb.disabled = false);
                commentBoxes.forEach(box => box.disabled = false);
                ulasanTextarea.disabled = false;
                sahkanButton.disabled = false;
            }
        }

        // Add event listeners for checkboxes and comment boxes
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.status-checkbox');
            const commentBoxes = document.querySelectorAll('.comment-box');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateDokumenStatus(this.dataset.dokumenId, this.checked ? 'Checked' : 'Not Checked');
                });
            });

            commentBoxes.forEach(box => {
                box.addEventListener('blur', function() {
                    updateDokumenComment(this.dataset.dokumenId, this.value);
                });
            });
        });

        function updateDokumenStatus(dokumenId, status) {
            // Add your existing updateDokumenStatus function here
        }

        function updateDokumenComment(dokumenId, comment) {
            // Add your existing updateDokumenComment function here
        }
    </script>
</body>
</html>
