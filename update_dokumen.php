<?php
// Database connection
include('database.php');

// Check if the form data is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dokumen_id'])) {
    // Retrieve and sanitize form data
    $dokumen_id = intval($_POST['dokumen_id']);
    $status = isset($_POST['status']) ? 'Checked' : 'Not Checked';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // Update the document's status and comment in the database
    $stmt = $conn->prepare("UPDATE dokumen SET status = ?, comment = ? WHERE dokumen_id = ?");
    $stmt->bind_param("ssi", $status, $comment, $dokumen_id);

    // Execute the update and respond
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
