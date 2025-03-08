<?php
session_start();
include('database.php');

// Pastikan pengguna sudah log masuk
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Dapatkan email semasa
$username = $_SESSION['username'];
$sql = "SELECT email FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_email = $user['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Kemaskini email jika berubah
    if ($email !== $current_email) {
        $update_email_sql = "UPDATE users SET email = ? WHERE username = ?";
        $update_email_stmt = $conn->prepare($update_email_sql);
        $update_email_stmt->bind_param("ss", $email, $username);
        if ($update_email_stmt->execute()) {
            $success = "Email berjaya dikemaskini";
            $current_email = $email;
        } else {
            $error = "Ralat semasa mengemaskini email";
        }
    }
    
    // Kemaskini kata laluan hanya jika semua field kata laluan diisi
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        // Dapatkan kata laluan semasa dari pangkalan data
        $sql = "SELECT password FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Sahkan kata laluan semasa
        if (!password_verify($current_password, $user['password'])) {
            $error = "Kata laluan semasa tidak tepat";
        }
        // Pastikan kata laluan baru sepadan
        elseif ($new_password !== $confirm_password) {
            $error = "Kata laluan baru tidak sepadan";
        }
        // Pastikan kata laluan baru tidak sama dengan kata laluan semasa
        elseif ($current_password === $new_password) {
            $error = "Kata laluan baru tidak boleh sama dengan kata laluan semasa";
        }
        else {
            // Hash kata laluan baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Kemaskini kata laluan dalam pangkalan data
            $update_sql = "UPDATE users SET password = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_password, $username);
            
            if ($update_stmt->execute()) {
                $success = "Kata laluan berjaya dikemaskini";
            } else {
                $error = "Ralat semasa mengemaskini kata laluan";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tetapan Log Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .password-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <?php include('sidebar.php'); ?>
    
    <div class="container">
        <div class="password-container">
            <h2 class="text-center mb-4">Tetapan Log Masuk</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($current_email); ?>">
                </div>
                
                <hr class="my-4">
                <h5 class="mb-3">Tukar Kata Laluan (Pilihan)</h5>
                
                <div class="mb-3">
                    <label for="current_password" class="form-label">Kata Laluan Semasa</label>
                    <input type="password" class="form-control" id="current_password" name="current_password">
                </div>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">Kata Laluan Baru</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Sahkan Kata Laluan Baru</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
                
                <div class="text-center">
                    <button type="button" class="btn btn-secondary me-2" onclick="history.back()">Kembali</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
