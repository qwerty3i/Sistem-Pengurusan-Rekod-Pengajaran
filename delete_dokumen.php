<?php
// delete_dokumen.php

include('database.php');
session_start();

if (isset($_GET['id'])) {
    $dokumen_id = intval($_GET['id']);

    // Fetch document details including kursus_id for maintaining sesi parameter
    $sql = "SELECT d.serahan_no, d.path_dokumen, k.sesi, k.id as kursus_id 
            FROM dokumen d 
            JOIN kursus k ON d.kursus_id = k.id 
            WHERE d.dokumen_id = ?";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    
    $stmt->bind_param("i", $dokumen_id);
    $stmt->execute();   
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $serahan_no = $row['serahan_no'];
        $file_path = $row['path_dokumen'];
        $sesi = $row['sesi'];
        $kursus_id = $row['kursus_id'];

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Update document record instead of deleting
            $update_sql = "UPDATE dokumen 
                          SET path_dokumen = NULL, 
                              status = 'Not Checked',
                              uploaded_at = NULL,
                              comment = NULL 
                          WHERE dokumen_id = ?";
                          
            $update_stmt = $conn->prepare($update_sql);
            if (!$update_stmt) {
                throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            
            $update_stmt->bind_param("i", $dokumen_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Execute failed: (" . $update_stmt->errno . ") " . $update_stmt->error);
            }

            // Commit the transaction
            $conn->commit();

            // Delete the physical file if it exists
            if (!empty($file_path) && file_exists($file_path)) {
                if (!unlink($file_path)) {
                    error_log("Failed to delete file: " . $file_path);
                    $_SESSION['file_deletion_error'] = "Document updated in database, but failed to delete the file from the server.";
                }
            }

            $_SESSION['delete_success'] = "Document deleted successfully.";

            // Redirect with both sesi and kursus parameters
            $redirect_url = ($serahan_no == 1) ? "serahan_pertama.php" : "serahan_kedua.php";
            $redirect_url .= "?sesi=" . urlencode($sesi) . "&kursus=" . urlencode($kursus_id);
            header("Location: " . $redirect_url);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['delete_error'] = "Error updating document: " . $e->getMessage();
            header("Location: serahan_pertama.php?sesi=" . urlencode($sesi) . "&kursus=" . urlencode($kursus_id));
            exit();
        } finally {
            if (isset($update_stmt)) {
                $update_stmt->close();
            }
            $stmt->close();
        }
    } else {
        $_SESSION['delete_error'] = "Document not found!";
        header("Location: serahan_pertama.php");
        exit();
    }
} else {
    $_SESSION['delete_error'] = "Invalid request!";
    header("Location: serahan_pertama.php");
    exit();
}

$conn->close();
?>
