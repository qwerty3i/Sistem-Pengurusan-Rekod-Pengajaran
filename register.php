<?php
include ('database.php');

// Handle delete request
if(isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $sql = "DELETE FROM users WHERE id = '$id' AND role = 'admin'";
    $conn->query($sql);
    header("Location: register.php");
    exit();
}

// Handle form submission for add and edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ic = preg_replace("/[^0-9]/", "", $_POST['ic']); // Remove any non-numeric characters
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate IC format
    if(strlen($ic) !== 12) {
        echo "<script>alert('No. Kad Pengenalan mesti 12 digit.');</script>";
    } else if($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if(isset($_POST['edit_id'])) {
            // Update existing admin
            $id = mysqli_real_escape_string($conn, $_POST['edit_id']);
            $sql = "UPDATE users SET username = '$ic', password = '$hashed_password' 
                    WHERE id = '$id' AND role = 'admin'";
        } else {
            // Add new admin
            $sql = "INSERT INTO users (username, password, role) 
                    VALUES ('$ic', '$hashed_password', 'admin')";
        }

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Operasi berjaya.');window.location.href='register.php';</script>";
        } else {
            echo "<script>alert('Ralat: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Kata laluan tidak sepadan.');</script>";
    }
}

// Get admin details for edit
if(isset($_GET['edit'])) {
    $id = mysqli_real_escape_string($conn, $_GET['edit']);
    $edit_sql = "SELECT * FROM users WHERE id = '$id' AND role = 'admin'";
    $edit_result = $conn->query($edit_sql);
    $edit_data = $edit_result->fetch_assoc();
}

// Get all admins
$sql = "SELECT * FROM users WHERE role = 'admin'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Admin</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Senarai Admin</h4>
                <button type="button" class="btn btn-light" data-toggle="modal" data-target="#addAdminModal">
                    <i class="fas fa-plus"></i> Tambah Admin
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No.</th>
                                <th>No. Kad Pengenalan</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            while($row = $result->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $row['username'] ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAdminModal" 
                                            onclick="setEditData('<?= $row['id'] ?>', '<?= $row['username'] ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteAdmin(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Admin -->
    <div class="modal fade" id="addAdminModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Admin Baharu</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="" method="POST" onsubmit="return validateForm(this);">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>No. Kad Pengenalan:</label>
                            <input type="text" class="form-control" name="ic" maxlength="12" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            <small class="text-muted">Masukkan 12 digit tanpa simbol</small>
                        </div>
                        <div class="form-group">
                            <label>Kata Laluan:</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Kepastian Kata Laluan:</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Kemaskini Admin -->
    <div class="modal fade" id="editAdminModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Kemaskini Admin</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="" method="POST" onsubmit="return validateForm(this);">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>No. Kad Pengenalan:</label>
                            <input type="text" class="form-control" name="ic" id="edit_ic" maxlength="12" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            <small class="text-muted">Masukkan 12 digit tanpa simbol</small>
                        </div>
                        <div class="form-group">
                            <label>Kata Laluan Baharu:</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Kepastian Kata Laluan Baharu:</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Kemaskini</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    function deleteAdmin(id) {
        if(confirm('Adakah anda pasti untuk memadam admin ini?')) {
            window.location.href = 'register.php?delete=' + id;
        }
    }

    function setEditData(id, username) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_ic').value = username;
    }

    function validateForm(form) {
        const ic = form.ic.value;
        const password = form.password.value;
        const confirmPassword = form.confirm_password.value;

        if(ic.length !== 12) {
            alert('No. Kad Pengenalan mesti 12 digit.');
            return false;
        }

        if(password !== confirmPassword) {
            alert('Kata laluan tidak sepadan.');
            return false;
        }

        return true;
    }
    </script>
</body>
</html>

