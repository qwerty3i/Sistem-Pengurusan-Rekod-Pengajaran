<?php
include('database.php');

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Periksa jika username wujud dan dapatkan maklumat email
    $sql = "SELECT id, email FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Periksa email dalam database
        if ($user['email'] === null || $user['email'] === '') {
            // Jika email kosong, simpan email baru
            $update_email_sql = "UPDATE users SET email = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_email_sql);
            $update_stmt->bind_param("si", $email, $user['id']);
            
            if (!$update_stmt->execute()) {
                $error = "Ralat semasa mengemaskini email.";
                goto end;
            }
        } else if ($user['email'] !== $email) {
            // Jika email tidak sepadan dengan yang ada dalam database
            $error = "Email yang dimasukkan tidak sepadan dengan rekod kami.";
            goto end;
        }
        
        // Proses reset kata laluan
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Simpan token dalam database
        $insert_sql = "INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iss", $user['id'], $token, $expiry);
        
        if ($insert_stmt->execute()) {
            $reset_link = "http://localhost/reset_password.php?token=" . $token;
            $message = "Pautan untuk menetapkan semula kata laluan telah dihantar ke email anda. Sila semak email anda.";
            
            // Dalam persekitaran sebenar, anda perlu menghantar email kepada pengguna
            // Contoh pautan: $reset_link
        } else {
            $error = "Ralat semasa memproses permintaan anda. Sila cuba lagi.";
        }
    } else {
        $error = "Tiada akaun yang dijumpai dengan username ini.";
    }
}
end:
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Laluan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .forgot-password-container {
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
        <div class="forgot-password-container">
            <h2 class="text-center mb-4">Lupa Kata Laluan</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">No. Kad Pengenalan:</label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           required
                           maxlength="12"
                           pattern="[0-9]{12}"
                           title="Sila masukkan 12 digit nombor kad pengenalan tanpa simbol"
                           placeholder="Contoh: 991231121234"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           required
                           placeholder="Contoh: nama@email.com">
                    <div class="form-text">
                        Jika anda telah mendaftar email sebelum ini, sila masukkan email yang sama.
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='login.php'">
                        Kembali
                    </button>
                    <button type="submit" class="btn btn-primary">Hantar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 