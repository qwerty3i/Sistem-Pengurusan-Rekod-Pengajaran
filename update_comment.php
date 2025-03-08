<?php
session_start();
include('database.php');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo "unauthorized";
    exit;
}

// Validate input
if (!isset($_POST['dokumen_id']) || !isset($_POST['comment'])) {
    echo "missing parameters";
    exit;
}

// Sanitize inputs
$dokumen_id = intval($_POST['dokumen_id']);
$comment = trim($_POST['comment']);

try {
    $query = "UPDATE dokumen SET comment = ? WHERE dokumen_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("si", $comment, $dokumen_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    echo "success";

} catch (Exception $e) {
    error_log("Error in update_comment.php: " . $e->getMessage());
    echo "error: " . $e->getMessage();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 