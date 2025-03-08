<?php
include('database.php');

// Check if session is set
if (!isset($_GET['sesi'])) {
    header("Location: senarai_kursus.php");
    exit();
}

$sesi = $_GET['sesi'];

// Insert kursus data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input data
    $pensyarah_id = !empty($_POST['pensyarah_id']) ? 
        mysqli_real_escape_string($conn, $_POST['pensyarah_id']) : NULL;
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $kod_kursus = mysqli_real_escape_string($conn, $_POST['kod_kursus']);
    $nama_kursus = mysqli_real_escape_string($conn, $_POST['nama_kursus']);
    $program = mysqli_real_escape_string($conn, $_POST['program']);
    $sesi = mysqli_real_escape_string($conn, $_POST['sesi']);

    // Check if course already exists for this session
    $check_sql = "SELECT * FROM kursus WHERE kod_kursus = '$kod_kursus' AND semester = '$semester' AND sesi = '$sesi'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('Kursus ini telah wujud untuk sesi ini!');
                window.location.href = 'senarai_kursus.php?sesi=$sesi';
              </script>";
    } else {
        // Insert into kursus table with NULL handling
        $sql = "INSERT INTO kursus (pensyarah_id, semester, sesi, kod_kursus, nama_kursus, program) 
                VALUES (" . ($pensyarah_id === NULL ? "NULL" : "'$pensyarah_id'") . ", 
                        '$semester', '$sesi', '$kod_kursus', '$nama_kursus', '$program')";

        if ($conn->query($sql) === TRUE) {
            // Create default documents for this course
            $kursus_id = $conn->insert_id;
            
            // Documents for Serahan 1
            $docs_1 = array(
                "Ringkasan Maklumat Kursus",
                "Perincian Kursus Mingguan",
                "Borang Item Penilaian",
                "Borang Pemetaan Pentaksiran dan COPO",
                "Rekod Kehadiran Pelajar (Minggu 1-7 for diploma) (Minggu 1-4 for Asasi)",
                "Jadual PdP Pensyarah"
            );

            foreach ($docs_1 as $doc) {
                $sql = "INSERT INTO dokumen (kursus_id, serahan_no, nama_dokumen, status) 
                        VALUES ($kursus_id, 1, '$doc', 'Not Checked')";
                $conn->query($sql);
            }

            // Documents for Serahan 2
            $docs_2 = array(
                "Rekod Kehadiran Pelajar (Minggu 1-14 for diploma) (Minggu 1-12 for Asasi)",
                "Perincian Kursus Mingguan (lengkap)",
                "Soalan Kemajuan 1",
                "Soalan Kemajuan 2",
                "Soalan Peperiksaan Akhir Beserta Skema Jawapan",
                "Analisis Keputusan – Laporan CQI & Penilaian Pengajaran oleh Pelajar",
                "Dokumen Tambahan"
            );

            foreach ($docs_2 as $doc) {
                $sql = "INSERT INTO dokumen (kursus_id, serahan_no, nama_dokumen, status) 
                        VALUES ($kursus_id, 2, '$doc', 'Not Checked')";
                $conn->query($sql);
            }

            echo "<script>
                    alert('Kursus baru telah berjaya ditambah!');
                    window.location.href = 'senarai_kursus.php?sesi=$sesi';
                  </script>";
        } else {
            echo "<script>
                    alert('Error: " . $conn->error . "');
                    window.location.href = 'tambah_kursus.php?sesi=$sesi';
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
    <title>Tambah Kursus</title>
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
        <h1 class="text-center mb-4">Tambah Kursus</h1>
        <form action="" method="POST">
            <input type="hidden" name="sesi" value="<?php echo $sesi; ?>">
            
            <div class="mb-3">
                <label for="pensyarah_id" class="form-label">Pensyarah</label>
                <select class="form-select" id="pensyarah_id" name="pensyarah_id">
                    <option value="">Pilih Pensyarah</option>
                    <?php
                    $sql = "SELECT id, nama_pensyarah FROM pensyarah ORDER BY nama_pensyarah";
                    $result = $conn->query($sql);
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['nama_pensyarah']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <select class="form-select" id="semester" name="semester" required>
                    <?php
                    for ($i = 1; $i <= 8; $i++) {
                        echo "<option value='$i'>$i</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="kursus_type" class="form-label">Jenis Penambahan</label>
                <select class="form-select" id="kursus_type" onchange="toggleKursusForm()" required>
                    <option value="new">Tambah Kursus Baharu</option>
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

            <div id="new_kursus_div">
                <div class="mb-3">
                    <label for="kod_kursus" class="form-label">Kod Kursus</label>
                    <input type="text" class="form-control" id="kod_kursus" name="kod_kursus" required>
                </div>

                <div class="mb-3">
                    <label for="nama_kursus" class="form-label">Nama Kursus</label>
                    <input type="text" class="form-control" id="nama_kursus" name="nama_kursus" required>
                </div>
            </div>

            <div class="mb-3" id="program_div">
                <label for="program" class="form-label">Program</label>
                <select class="form-select" id="program" name="program" required>
                    <option value="">Pilih Program</option>
                    <option>Diploma Pengajian Islam Pengkhususan Bahasa Arab & Tamadun Islam</option>
                    <option>Diploma Bahasa Arab Komunikasi</option>
                    <option>Diploma Pengajian Islam Pengkhususan Syariah & Undang - Undang</option>
                    <option>Diploma Pengurusan Muamalat</option>
                    <option>Diploma Pengajian Islam Pengkhususan Dakwah & Pengurusan</option>
                    <option>Diploma Pengajian Media Islam</option>
                    <option>Diploma Pengajian Quran dan Sunnah</option>
                    <option>Diploma Pengurusan Kewangan Islam</option>
                    <option>Diploma Kewangan & Perbankan</option>
                    <option>Diploma Pengajian Perniagaan</option>
                    <option>Diploma Pengurusan Pemasaran</option>
                    <option>Diploma Perakaunan</option>
                    <option>Diploma Pembangunan Perisian (Internet & Multimedia)</option>
                    <option>Diploma Teknologi Maklumat & Rangkaian</option>
                    <option>Diploma In English</option>
                    <option>Diploma Pendidikan Awal Kanak-Kanak Secara Islamik</option>
                    <option>Diploma Kaunseling Islam</option>
                    <option>Diploma Peradaban Islam</option>
                    <option>Diploma Bahasa Arab</option>
                    <option>Diploma Sains Komputer</option>
                    <option>Ijazah Sarjana Muda Syariah dengan Pentadbiran Kehakiman (Kepujian)</option>
                    <option>Ijazah Sarjana Muda Dakwah Islamiah Dengan Kepujian</option>
                    <option>Ijazah Sarjana Muda Pengajian Quran dan Sunnah dengan Kepujian</option>
                    <option>Ijazah Sarjana Muda Kewangan Islam (Kepujian)</option>
                    <option>Ijazah Sarjana Muda Pentadbiran Perniagaan Dengan Kepujian</option>
                    <option>Ijazah Sarjana Muda Perakaunan Dengan Kepujian</option>
                    <option>Ijazah Sarjana Muda Multimedia (Pengiklanan Digital) Dengan Kepujian</option>
                    <option>Ijazah Sarjana Muda Pengajian Bahasa Inggeris Dengan Kepimpinan Pengajaran</option>
                    <option>Ijazah Sarjana Muda Pendidikan Awal Kanak-Kanak Secara Islamik</option>
                    <option>Ijazah Sarjana Muda Tahfiz Al-Quran Dengan Pengurusan Islam</option>
                    <option>Master Of Education Teaching English As A Second Language</option>
                    <option>Sarjana Pengajian Islam</option>
                    <option>Doktor Falsafah Pengajian Islam</option>
                    <option>Asasi Sains Kemanusiaan</option>
                </select>
            </div>

            <div class="text-center">
                <button type="button" class="btn btn-secondary me-2" onclick="window.location.href='senarai_kursus.php?sesi=<?php echo $sesi; ?>'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

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
            const newDiv = document.getElementById('new_kursus_div');
            
            if (kursusType === 'existing') {
                existingDiv.style.display = 'block';
                newDiv.style.display = 'none';
                // Make new course fields not required
                document.getElementById('kod_kursus').required = false;
                document.getElementById('nama_kursus').required = false;
                // Reset Select2
                $('#existing_kursus').val(null).trigger('change');
            } else {
                existingDiv.style.display = 'none';
                newDiv.style.display = 'block';
                // Make new course fields required
                document.getElementById('kod_kursus').required = true;
                document.getElementById('nama_kursus').required = true;
                // Clear form fields
                document.getElementById('kod_kursus').value = '';
                document.getElementById('nama_kursus').value = '';
                document.getElementById('program').selectedIndex = 0;
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 