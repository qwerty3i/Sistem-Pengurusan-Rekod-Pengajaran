<!DOCTYPE html>
<?php
include('database.php');

// Tambah pembolehubah untuk mesej ralat
$username_error = '';

// Insert pensyarah data
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve and sanitize input data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Validasi format IC
    if (!preg_match('/^[0-9]{12}$/', $username)) {
        $username_error = 'Username mesti dalam format kad pengenalan (12 digit nombor tanpa simbol)';
    } else {
        // Check if username already exists
        $check_username = "SELECT username FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_username);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $username_error = 'Username sudah wujud. Sila gunakan username lain.';
        } else {
            $nama_pensyarah = $_POST['nama_pensyarah'];
            $ketua_jabatan = $_POST['ketua_jabatan'];
            $jabatan = $_POST['jabatan'];
            $fakulti = $_POST['fakulti'];

            $role = ($ketua_jabatan === 'yes') ? 'ketua bahagian' : 'pensyarah';

            // Hash the default password for security
            $default_password = password_hash('123', PASSWORD_DEFAULT);

            // Insert into users table with the provided username and determined role
            $sql_users = "INSERT INTO users (username, password, role) 
                          VALUES ('$username', '$default_password', '$role')";

            if ($conn->query($sql_users) === TRUE) {
                // Get the ID of the newly inserted user
                $id_users = $conn->insert_id;

                // Insert into pensyarah table with the id_users reference
                $sql_pensyarah = "INSERT INTO pensyarah (id_users, nama_pensyarah, ketua_jabatan, jabatan, fakulti) 
                                  VALUES ($id_users, '$nama_pensyarah', '$ketua_jabatan', '$jabatan', '$fakulti')";

                if ($conn->query($sql_pensyarah) === TRUE) {
                    echo "<script>
                            alert('New user and pensyarah records created successfully with username \"$username\" and default password \"123\"');
                            window.location.href = 'senarai_pensyarah.php';
                          </script>";
                } else {
                    echo "<script>
                            alert('Error inserting into pensyarah table: " . $conn->error . "');
                            window.location.href = 'register.php';
                          </script>";
                }
            } else {
                echo "<script>
                        alert('Error inserting into users table: " . $conn->error . "');
                        window.location.href = 'register.php';
                      </script>";
            }

            $conn->close();
        }
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pensyarah</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            margin-top: 50px;
            max-width: 600px;
        }
    </style>
</head>
<body>
    <?php include('sidebar.php'); ?>
    <div class="container form-container">
        <h1 class="text-center mb-4">Tambah Pensyarah</h1>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username (No. Kad Pengenalan)</label>
                <input type="text" 
                       class="form-control <?php echo $username_error ? 'is-invalid' : ''; ?>" 
                       id="username" 
                       name="username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                       maxlength="12"
                       pattern="[0-9]{12}"
                       title="Sila masukkan 12 digit nombor kad pengenalan tanpa simbol"
                       placeholder="Contoh: 991231121234"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);"
                       required>
                <?php if ($username_error): ?>
                    <div class="invalid-feedback">
                        <?php echo $username_error; ?>
                    </div>
                <?php endif; ?>
                <div class="form-text">
                    Masukkan 12 digit nombor kad pengenalan tanpa simbol (-).
                </div>
            </div>
            
            <div class="mb-3">
                <label for="nama_pensyarah" class="form-label">Nama Pensyarah</label>
                <input type="text" class="form-control" id="nama_pensyarah" name="nama_pensyarah" required>
            </div>

            <div class="mb-3">
                <label for="ketua_jabatan" class="form-label">Ketua Jabatan</label>
                <select class="form-select" id="ketua_jabatan" name="ketua_jabatan" required>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <select class="form-select" id="jabatan" name="jabatan" required>
                    <option>Jabatan Bahasa Arab</option>
                    <option>Jabatan Syariah</option>
                    <option>Jabatan Dakwah</option>
                    <option>Jabatan Quran Sunnah</option>
                    <option>Jabatan Kewangan</option>
                    <option>Jabatan Pengajian Perniagaan</option>
                    <option>Jabatan Perakaunan</option>
                    <option>Jabatan Multimedia Kreatif & Komputeran</option>
                    <option>Jabatan Bahasa & Asasi</option>
                    <option>Jabatan Pendidikan Dan Pembangunan Insan</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="fakulti" class="form-label">Fakulti</label>
                <select class="form-select" id="fakulti" name="fakulti" required>
                    <option>Fakulti Pengajian Islam</option>
                    <option>Fakulti Pengurusan & Informatik</option>
                    <option>Fakulti Pengajian Bahasa & Asasi</option>
                    <option>Fakulti Syariah</option>
                </select>
            </div>

            <div class="text-center">
                 <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='senarai_pensyarah.php'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('username').addEventListener('input', function(e) {
        // Buang semua aksara selain nombor
        let value = this.value.replace(/[^0-9]/g, '');
        
        // Had kepada 12 digit
        if (value.length > 12) {
            value = value.slice(0, 12);
        }
        
        // Update nilai input
        this.value = value;
    });
    </script>
</body>
</html>
