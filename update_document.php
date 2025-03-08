<?php
session_start();
include('database.php');

// Set timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['id'])) {
    echo "unauthorized";
    exit;
}

if (!isset($_POST['dokumen_id']) || !isset($_POST['status']) || !isset($_POST['comment'])) {
    echo "missing parameters";
    exit;
}

$dokumen_id = intval($_POST['dokumen_id']);
$status = ($_POST['status'] == 'Checked') ? 'Checked' : 'Not Checked';
$comment = trim($_POST['comment']);
$current_time = date('Y-m-d H:i:s');

$query = "UPDATE dokumen 
          SET status = ?, 
              comment = ?,
              checked_at = CASE 
                  WHEN ? = 'Checked' THEN ? 
                  ELSE NULL 
              END
          WHERE dokumen_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssi", 
    $status,
    $comment,
    $status,
    $current_time,
    $dokumen_id
);

try {
    $conn->begin_transaction();

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $conn->commit();
    
    // Format the time for display in Malaysian format
    if ($status === 'Checked') {
        $formatted_date = date('d/m/Y g:i A', strtotime($current_time)); // 12-hour format with AM/PM
        echo json_encode([
            'success' => true,
            'checked_at' => $formatted_date
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'checked_at' => null
        ]);
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in update_document.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 