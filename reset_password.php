<?php
include('database.php');

$message = '';
$error = '';
$valid_token = false;
$token = $_GET['token'] ?? '';

// Periksa token
if ($token) {
    $sql = "SELECT pr.*, u.username 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? AND pr.used = 0 AND pr.expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $reset_data = $result->fetch_assoc();
        $valid_token = true;
    } else {
        $error = "Pautan tidak sah atau telah tamat tempoh.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password) {
        // Kemaskini kata laluan
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $reset_data['user_id']);
        
        if ($update_stmt->execute()) {
            // Tanda token sebagai telah digunakan
            $mark_used_sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
            $mark_used_stmt = $conn->prepare($mark_used_sql);
            $mark_used_stmt->bind_param("s", $token);
            $mark_used_stmt->execute();
            
            $message = "Kata laluan anda telah berjaya dikemaskini. Sila log masuk dengan kata laluan baru anda.";
        } else {
            $error = "Ralat semasa mengemaskini kata laluan.";
        }
    } else {
        $error = "Kata laluan tidak sepadan.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tetapkan Semula Kata Laluan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .reset-password-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-password-container">
            <h2 class="text-center mb-4">Tetapkan Semula Kata Laluan</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-primary">Log Masuk</a>
                    </div>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                    <?php if (!$valid_token): ?>
                        <div class="text-center mt-3">
                            <a href="login.php" class="btn btn-primary">Kembali ke Log Masuk</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token && !$message): ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Kata Laluan Baru:</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Sahkan Kata Laluan Baru:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Tetapkan Semula Kata Laluan</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 