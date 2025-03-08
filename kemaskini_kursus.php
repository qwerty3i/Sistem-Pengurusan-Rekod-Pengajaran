<?php
include('database.php');

// Check if ID is set
if (!isset($_GET['id'])) {
    header("Location: senarai_kursus.php");
    exit();
}

$id = $_GET['id'];

// Get current course data
$sql = "SELECT * FROM kursus WHERE id = $id";
$result = $conn->query($sql);
$kursus = $result->fetch_assoc();

if (!$kursus) {
    echo "<script>
            alert('Kursus tidak dijumpai!');
            window.location.href = 'senarai_kursus.php';
          </script>";
    exit();
}

// Update kursus data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pensyarah_id = !empty($_POST['pensyarah_id']) ? 
        mysqli_real_escape_string($conn, $_POST['pensyarah_id']) : NULL;
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $kod_kursus = mysqli_real_escape_string($conn, $_POST['kod_kursus']);
    $nama_kursus = mysqli_real_escape_string($conn, $_POST['nama_kursus']);
    $program = mysqli_real_escape_string($conn, $_POST['program']);
    $sesi = mysqli_real_escape_string($conn, $_POST['sesi']);

    // Check if course already exists (excluding current course)
    $check_sql = "SELECT * FROM kursus 
                  WHERE kod_kursus = '$kod_kursus' 
                  AND semester = '$semester' 
                  AND sesi = '$sesi' 
                  AND id != $id";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('Kursus ini telah wujud untuk sesi ini!');
                window.location.href = 'senarai_kursus.php?sesi=$sesi';
              </script>";
    } else {
        // Update kursus
        $update_sql = "UPDATE kursus SET 
                      pensyarah_id = " . ($pensyarah_id === NULL ? "NULL" : "'$pensyarah_id'") . ",
                      semester = '$semester',
                      sesi = '$sesi',
                      kod_kursus = '$kod_kursus',
                      nama_kursus = '$nama_kursus',
                      program = '$program'
                      WHERE id = $id";

        if ($conn->query($update_sql) === TRUE) {
            echo "<script>
                    alert('Kursus telah berjaya dikemaskini!');
                    window.location.href = 'senarai_kursus.php?sesi=$sesi';
                  </script>";
        } else {
            echo "<script>
                    alert('Error: " . $conn->error . "');
                    window.location.href = 'kemaskini_kursus.php?id=$id';
                  </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kemaskini Kursus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            margin-top: 50px;
            max-width: 600px;
        }
        .select2-container {
            width: 100% !important;
        }
        
        .select2-selection {
            height: 38px !important;
            padding: 5px !important;
        }
        
        .select2-selection__arrow {
            height: 37px !important;
        }
    </style>
</head>
<body>
    <?php include('sidebar.php'); ?>
    <div class="container form-container">
        <h1 class="text-center mb-4">Kemaskini Kursus</h1>
        <form action="" method="POST">
            <input type="hidden" name="sesi" value="<?php echo $kursus['sesi']; ?>">
            
            <div class="mb-3">
                <label for="pensyarah_id" class="form-label">Pensyarah</label>
                <select class="form-select" id="pensyarah_id" name="pensyarah_id">
                    <option value="">Pilih Pensyarah</option>
                    <?php
                    $sql = "SELECT id, nama_pensyarah FROM pensyarah ORDER BY nama_pensyarah";
                    $result = $conn->query($sql);
                    while($row = $result->fetch_assoc()) {
                        $selected = ($row['id'] == $kursus['pensyarah_id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' {$selected}>{$row['nama_pensyarah']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <select class="form-select" id="semester" name="semester" required>
                    <?php
                    for ($i = 1; $i <= 8; $i++) {
                        $selected = ($i == $kursus['semester']) ? 'selected' : '';
                        echo "<option value='$i' {$selected}>$i</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="kursus_type" class="form-label">Jenis Kemaskini</label>
                <select class="form-select" id="kursus_type" onchange="toggleKursusForm()" required>
                    <option value="manual">Kemaskini Kursus</option>
                    <option value="existing">Pilih Dari Senarai Sedia Ada</option>
                </select>
            </div>

            <div id="existing_kursus_div" style="display: none;" class="mb-3">
                <label for="existing_kursus" class="form-label">Pilih Kursus</label>
                <select class="form-select" id="existing_kursus" onchange="fillKursusDetails()">
                    <option value="">Pilih Kursus</option>
                    <?php
                    $existing_query = "SELECT DISTINCT kod_kursus, nama_kursus 
                                      FROM kursus 
                                      WHERE id != $id
                                      ORDER BY kod_kursus";
                    $existing_result = $conn->query($existing_query);
                    while($row = $existing_result->fetch_assoc()) {
                        $display_text = $row['kod_kursus'] . " - " . $row['nama_kursus'];
                        echo "<option value='" . htmlspecialchars(json_encode($row)) . "'>" 
                             . htmlspecialchars($display_text) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div id="manual_kursus_div">
                <div class="mb-3">
                    <label for="kod_kursus" class="form-label">Kod Kursus</label>
                    <input type="text" class="form-control" id="kod_kursus" name="kod_kursus" 
                           value="<?php echo $kursus['kod_kursus']; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="nama_kursus" class="form-label">Nama Kursus</label>
                    <input type="text" class="form-control" id="nama_kursus" name="nama_kursus" 
                           value="<?php echo $kursus['nama_kursus']; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="program" class="form-label">Program</label>
                <select class="form-select" id="program" name="program" required>
                    <option value="">Pilih Program</option>
                    <?php
                    $programs = [
                        "Diploma Pengajian Islam Pengkhususan Bahasa Arab & Tamadun Islam",
                        "Diploma Bahasa Arab Komunikasi",
                        "Diploma Pengajian Islam Pengkhususan Syariah & Undang - Undang",
                        "Diploma Pengurusan Muamalat",
                        "Diploma Pengajian Islam Pengkhususan Dakwah & Pengurusan",
                        "Diploma Pengajian Media Islam",
                        "Diploma Pengajian Quran dan Sunnah",
                        "Diploma Pengurusan Kewangan Islam",
                        "Diploma Kewangan & Perbankan",
                        "Diploma Pengajian Perniagaan",
                        "Diploma Pengurusan Pemasaran",
                        "Diploma Perakaunan",
                        "Diploma Pembangunan Perisian (Internet & Multimedia)",
                        "Diploma Teknologi Maklumat & Rangkaian",
                        "Diploma In English",
                        "Diploma Pendidikan Awal Kanak-Kanak Secara Islamik",
                        "Diploma Kaunseling Islam",
                        "Diploma Peradaban Islam",
                        "Diploma Bahasa Arab",
                        "Diploma Sains Komputer",
                        "Ijazah Sarjana Muda Syariah dengan Pentadbiran Kehakiman (Kepujian)",
                        "Ijazah Sarjana Muda Dakwah Islamiah Dengan Kepujian",
                        "Ijazah Sarjana Muda Pengajian Quran dan Sunnah dengan Kepujian",
                        "Ijazah Sarjana Muda Kewangan Islam (Kepujian)",
                        "Ijazah Sarjana Muda Pentadbiran Perniagaan Dengan Kepujian",
                        "Ijazah Sarjana Muda Perakaunan Dengan Kepujian",
                        "Ijazah Sarjana Muda Multimedia (Pengiklanan Digital) Dengan Kepujian",
                        "Ijazah Sarjana Muda Pengajian Bahasa Inggeris Dengan Kepimpinan Pengajaran",
                        "Ijazah Sarjana Muda Pendidikan Awal Kanak-Kanak Secara Islamik",
                        "Ijazah Sarjana Muda Tahfiz Al-Quran Dengan Pengurusan Islam",
                        "Master Of Education Teaching English As A Second Language",
                        "Sarjana Pengajian Islam",
                        "Doktor Falsafah Pengajian Islam",
                        "Asasi Sains Kemanusiaan"
                    ];
                    foreach ($programs as $prog) {
                        $selected = ($prog == $kursus['program']) ? 'selected' : '';
                        echo "<option {$selected}>$prog</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="text-center">
                <button type="button" class="btn btn-secondary me-2" 
                        onclick="window.location.href='senarai_kursus.php?sesi=<?php echo $kursus['sesi']; ?>'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initiate Select2 with search
            $('#existing_kursus').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari dan pilih kursus...',
                allowClear: true,
                width: '100%'
            });
        });

        function toggleKursusForm() {
            const kursusType = document.getElementById('kursus_type').value;
            const existingDiv = document.getElementById('existing_kursus_div');
            const manualDiv = document.getElementById('manual_kursus_div');
            
            if (kursusType === 'existing') {
                existingDiv.style.display = 'block';
                manualDiv.style.display = 'none';
                document.getElementById('kod_kursus').required = false;
                document.getElementById('nama_kursus').required = false;
                $('#existing_kursus').val(null).trigger('change');
            } else {
                existingDiv.style.display = 'none';
                manualDiv.style.display = 'block';
                document.getElementById('kod_kursus').required = true;
                document.getElementById('nama_kursus').required = true;
                document.getElementById('kod_kursus').value = '<?php echo $kursus['kod_kursus']; ?>';
                document.getElementById('nama_kursus').value = '<?php echo $kursus['nama_kursus']; ?>';
            }
        }

        function fillKursusDetails() {
            const selectedKursus = $('#existing_kursus').val();
            if (selectedKursus) {
                const kursusData = JSON.parse(selectedKursus);
                document.getElementById('kod_kursus').value = kursusData.kod_kursus;
                document.getElementById('nama_kursus').value = kursusData.nama_kursus;
            }
        }
    </script>
</body>
</html> 