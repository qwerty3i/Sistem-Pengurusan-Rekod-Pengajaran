<?php
include('database.php');
session_start();

// Verify ketua bahagian role
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'ketua bahagian') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $kursus_id = $_POST['kursus_id'];
    $dokumen_id = $_POST['dokumen_id'];
    $serahan_no = isset($_POST['serahan_no']) ? intval($_POST['serahan_no']) : 1;
    $sesi = $_POST['sesi'];
    $file = $_FILES['file'];

    // Validate file type
    if ($file['type'] !== 'application/pdf') {
        $_SESSION['upload_error'] = "Invalid file type. Only PDF files are allowed.";
        header("Location: serahan_pertama_ketua_jabatan.php?sesi=" . urlencode($sesi) . "&kursus=" . urlencode($kursus_id));
        exit();
    }

    // Create upload directory if it doesn't exist
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            $_SESSION['upload_error'] = "Failed to create upload directory.";
            header("Location: serahan_pertama_ketua_jabatan.php?sesi=" . urlencode($sesi) . "&kursus=" . urlencode($kursus_id));
            exit();
        }
    }

    // Generate unique filename
    $fileName = uniqid() . "_" . basename($file['name']);
    $filePath = $uploadDir . $fileName;

    // Begin transaction
    $conn->begin_transaction();

    try {
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Get the old file path before updating
            $old_file_query = "SELECT path_dokumen FROM dokumen WHERE dokumen_id = ?";
            $stmt = $conn->prepare($old_file_query);
            $stmt->bind_param("i", $dokumen_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old_file_path = '';
            
            if ($row = $result->fetch_assoc()) {
                $old_file_path = $row['path_dokumen'];
            }
            $stmt->close();

            // Update document record
            $update_sql = "UPDATE dokumen 
                          SET status = 'Not Checked', 
                              path_dokumen = ?, 
                              uploaded_at = NOW() 
                          WHERE dokumen_id = ? 
                          AND kursus_id = ? 
                          AND serahan_no = ?";
            
            $stmt = $conn->prepare($update_sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("siis", $filePath, $dokumen_id, $kursus_id, $serahan_no);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            if ($stmt->affected_rows > 0) {
                // Delete old file if it exists
                if (!empty($old_file_path) && file_exists($old_file_path)) {
                    if (!unlink($old_file_path)) {
                        error_log("Failed to delete old file: " . $old_file_path);
                    }
                }
                
                $conn->commit();
                $_SESSION['upload_success'] = "Document uploaded successfully!";
            } else {
                throw new Exception("No document was updated. Please check the document ID and course ID.");
            }
            
            $stmt->close();
        } else {
            throw new Exception("Failed to upload file.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        
        // Delete the newly uploaded file if it exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $_SESSION['upload_error'] = "Error: " . $e->getMessage();
    }
} else {
    $_SESSION['upload_error'] = "No file uploaded.";
}

$conn->close();

// Tentukan halaman redirect berdasarkan serahan_no
$redirect_page = ($serahan_no == 1) ? "serahan_pertama_ketua_jabatan.php" : "serahan_kedua_ketua_jabatan.php";

// Redirect dengan parameter yang betul
if (isset($_SESSION['upload_error'])) {
    header("Location: " . $redirect_page . "?sesi=" . urlencode($sesi) . "&kursus=" . urlencode($kursus_id));
    exit();
}

if (isset($_SESSION['upload_success'])) {
    header("Location: " . $redirect_page . "?sesi=" . urlencode($sesi) . "&kursus=" . urlencode($kursus_id));
    exit();
}

// Default redirect
header("Location: " . $redirect_page . "?sesi=" . urlencode($sesi) . "&kursus=" . urlencode($kursus_id));
exit();
?>
