<?php
include('database.php');

// Fetch the record based on ID from the URL
$id = $_GET['id'];
$sql = "SELECT p.*, u.username 
        FROM pensyarah p 
        JOIN users u ON p.id_users = u.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $pensyarah = $result->fetch_assoc();
} else {
    echo "No record found!";
    exit();
}

// Tambah pembolehubah untuk mesej ralat
$username_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated data from the form
    $id = $_POST['id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Validasi format IC
    if (!preg_match('/^[0-9]{12}$/', $username)) {
        $username_error = 'Username mesti dalam format kad pengenalan (12 digit nombor tanpa simbol)';
    } else {
        // Check if username already exists (excluding current user)
        $check_username = "SELECT username FROM users WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($check_username);
        $stmt->bind_param("si", $username, $pensyarah['id_users']);
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

            // Begin transaction
            $conn->begin_transaction();

            try {
                // Update pensyarah table
                $sql_pensyarah = "UPDATE pensyarah SET 
                                 nama_pensyarah = ?, 
                                 ketua_jabatan = ?, 
                                 jabatan = ?, 
                                 fakulti = ? 
                                 WHERE id = ?";
                
                $stmt = $conn->prepare($sql_pensyarah);
                $stmt->bind_param("ssssi", $nama_pensyarah, $ketua_jabatan, $jabatan, $fakulti, $id);
                $stmt->execute();

                // Update users table
                $sql_users = "UPDATE users SET 
                             username = ?, 
                             role = ? 
                             WHERE id = ?";
                
                $stmt = $conn->prepare($sql_users);
                $stmt->bind_param("ssi", $username, $role, $pensyarah['id_users']);
                $stmt->execute();

                // Commit transaction
                $conn->commit();
                
                header("Location: senarai_pensyarah.php");
                exit();
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                echo "Error updating record: " . $e->getMessage();
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
    <title>Edit Pensyarah</title>
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
        <h1 class="text-center mb-4">Edit Pensyarah</h1>
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?php echo $pensyarah['id']; ?>">

            <div class="mb-3">
                <label for="username" class="form-label">Username (No. Kad Pengenalan)</label>
                <input type="text" 
                       class="form-control <?php echo $username_error ? 'is-invalid' : ''; ?>" 
                       id="username" 
                       name="username" 
                       value="<?php echo htmlspecialchars($pensyarah['username']); ?>" 
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
                <input type="text" class="form-control" id="nama_pensyarah" name="nama_pensyarah" 
                       value="<?php echo htmlspecialchars($pensyarah['nama_pensyarah']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="ketua_jabatan" class="form-label">Ketua Jabatan</label>
                <select class="form-select" id="ketua_jabatan" name="ketua_jabatan" required>
                    <option value="yes" <?php echo ($pensyarah['ketua_jabatan'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                    <option value="no" <?php echo ($pensyarah['ketua_jabatan'] == 'no') ? 'selected' : ''; ?>>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <select class="form-select" id="jabatan" name="jabatan" required>
                    <?php
                    $departments = [
                        "Jabatan Bahasa Arab", "Jabatan Syariah", "Jabatan Dakwah", 
                        "Jabatan Quran Sunnah", "Jabatan Kewangan", 
                        "Jabatan Pengajian Perniagaan", "Jabatan Perakaunan", 
                        "Jabatan Multimedia Kreatif & Komputeran", 
                        "Jabatan Bahasa & Asasi", "Jabatan Pendidikan Dan Pembangunan Insan"
                    ];
                    foreach ($departments as $department) {
                        $selected = ($pensyarah['jabatan'] == $department) ? 'selected' : '';
                        echo "<option value='$department' $selected>$department</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="fakulti" class="form-label">Fakulti</label>
                <select class="form-select" id="fakulti" name="fakulti" required>
                    <?php
                    $faculties = [
                        "Fakulti Pengajian Islam", "Fakulti Pengurusan & Informatik", 
                        "Fakulti Pengajian Bahasa & Asasi", "Fakulti Syariah"
                    ];
                    foreach ($faculties as $faculty) {
                        $selected = ($pensyarah['fakulti'] == $faculty) ? 'selected' : '';
                        echo "<option value='$faculty' $selected>$faculty</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="text-center">
                <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='senarai_pensyarah.php'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>

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
