<?php
session_start();
include('database.php');

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['kursus_id']) || !isset($_POST['status']) || !isset($_POST['no_serahan'])) {
        throw new Exception('Missing required parameters');
    }

    $kursus_id = intval($_POST['kursus_id']);
    $status = $_POST['status'];
    $ulasan = $_POST['ulasan'] ?? '';
    $no_serahan = intval($_POST['no_serahan']);
    
    // Get pensyarah_id from session
    $current_user_id = $_SESSION['id'];
    $pensyarah_query = "SELECT id FROM pensyarah WHERE id_users = ?";
    $stmt = $conn->prepare($pensyarah_query);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Pensyarah not found');
    }
    
    $pensyarah = $result->fetch_assoc();
    $pensyarah_id = $pensyarah['id'];

    // Check if record exists with specific no_serahan
    $check_query = "SELECT * FROM pengesahan_kursus WHERE kursus_id = ? AND no_serahan = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $kursus_id, $no_serahan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $query = "UPDATE pengesahan_kursus 
                 SET Status_pengesahan = ?,
                     ulasan = ?,
                     disahkan_oleh = ?,
                     pensyarah_id = ?,
                     waktu_pengesahan = NOW()
                 WHERE kursus_id = ? AND no_serahan = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiiii", 
            $status,
            $ulasan,
            $pensyarah_id,
            $pensyarah_id,
            $kursus_id,
            $no_serahan
        );
    } else {
        // Insert new record
        $query = "INSERT INTO pengesahan_kursus 
                 (kursus_id, no_serahan, Status_pengesahan, ulasan, disahkan_oleh, pensyarah_id, waktu_pengesahan)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissii",
            $kursus_id,
            $no_serahan,
            $status,
            $ulasan,
            $pensyarah_id,
            $pensyarah_id
        );
    }

    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Pengesahan berjaya dikemaskini'
    ]);

} catch (Exception $e) {
    error_log("Error in update_pengesahan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 