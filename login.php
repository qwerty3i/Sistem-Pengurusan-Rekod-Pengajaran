<!DOCTYPE html>
<?php
session_start();

include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Fetch user data
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store user data in session
            $_SESSION['id'] = $user['id'];         // Add user ID to session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['LAST_ACTIVITY'] = time();
            
            // Redirect based on role
            switch($user['role']) {
                case 'admin':
                    echo "<script>
                            alert('Login successful. Welcome, Admin!');
                            window.location.href = 'admin_dashboard.php';
                          </script>";
                    break;
                case 'ketua bahagian':
                    echo "<script>
                            alert('Login successful. Welcome, Ketua Bahagian!');
                            window.location.href = 'dashboard_ketua_jabatan.php';
                          </script>";
                    break;
                case 'pensyarah':
                    echo "<script>
                            alert('Login successful. Welcome, Pensyarah!');
                            window.location.href = 'dashboard_pensyarah.php';
                          </script>";
                    break;
            }
        } else {
            echo "<script>
                    alert('Invalid password.');
                    window.location.href = 'login.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('No user found with this username.');
                window.location.href = 'login.php';
              </script>";
    }
}

$conn->close();
?>


<html lang="en">

   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
      <title>Sistem Pengurusan Rekod Pengajaran</title>
      <link rel="shortcut icon" href="assets/img/favicon.png">
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,500;0,600;0,700;1,400&amp;display=swap">
      <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
      <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
      <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
      <link rel="stylesheet" href="assets/css/style.css">
   </head>
   <body>
      <form method="post" action="">
      <div class="main-wrapper login-body">
         <div class="login-wrapper">
            <div class="container">
               <div class="loginbox">
                  <div class="login-left">
                     <img class="img-fluid" src="assets/img/logo-unipsas.png" alt="Logo">
                     <img class="img-fluid" src="assets/img/logo-unipsas2b.png" alt="Logo">
                  </div>
                  <div class="login-right">
                     <div class="login-right-wrap">
                        <h1>Selamat Datang ke</h1>
                        <p class="account-subtitle">Sistem Pengurusan Rekod Pengajaran</p>
                        
                              <div class="form-group">
                              <label for="username">No. Kad Pengenalan:</label>
                              <input type="text" 
                                     class="form-control" 
                                     id="username" 
                                     name="username" 
                                     required
                                     maxlength="12"
                                     pattern="[0-9]{12}|admin"
                                     title="Sila masukkan 12 digit nombor kad pengenalan tanpa simbol (-)"
                                     placeholder="Contoh: 991231121234"
                                     oninput="validateInput(this)">
                              <div class="form-text small text-muted">
                                  Masukkan 12 digit nombor kad pengenalan tanpa simbol (-).
                              </div>
                           </div>

                           <div class="form-group">
                              <label for="password">Kata Laluan:</label>
                              <input type="password" class="form-control" id="password" name="password" required>
                           </div>
                           <div class="form-group">
                              <button class="btn btn-primary btn-block" type="submit">Log Masuk</button>
                           </div>
                        
                        <div class="text-center forgotpass"><a href="forgot_password.php">Lupa Kata Laluan?</a></div>
                       
                        
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>

      <script src="assets/js/jquery-3.6.0.min.js"></script>
      <script src="assets/js/popper.min.js"></script>
      <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
      <script src="assets/js/script.js"></script>
      <script>
      function validateInput(input) {
          // Dapatkan nilai semasa
          let value = input.value.toLowerCase();
          
          // Jika pengguna sedang menaip 'admin'
          if (value === 'a' || value.startsWith('a') || 
              value === 'ad' || value.startsWith('ad') ||
              value === 'adm' || value.startsWith('adm') ||
              value === 'admi' || value.startsWith('admi') ||
              value === 'admin') {
              input.value = value;
              return;
          }
          
          // Jika bukan 'admin', hanya benarkan nombor
          input.value = input.value.replace(/[^0-9]/g, '').slice(0, 12);
      }
      </script>
   </body>

</html>