<!DOCTYPE html>

<?php

if (isset($_REQUEST['serahan_no'])) {
    $serahan_no = $_REQUEST['serahan_no'];
    // Your code using $serahan_no goes here
} else {
    echo "Serahan number not specified.";
    exit;
}

// Dapatkan parameter dari URL
$serahan_no = $_GET['serahan_no'] ?? null;
$sesi = $_GET['sesi'] ?? null;
$kursus_id = $_GET['kursus_id'] ?? null;
$kod_kursus = $_GET['kod_kursus'] ?? null;
$nama_kursus = $_GET['nama_kursus'] ?? null;
$program = $_GET['program'] ?? null;
$semester = $_GET['semester'] ?? null;
$active_course_id = $_GET['kursus_id'] ?? null;

// Jika ada parameter kursus_id, scroll terus ke kursus tersebut
if ($kursus_id) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const targetElement = document.getElementById('kursus-" . $kursus_id . "');
            if (targetElement) {
                // Buka semua accordion yang berkaitan
                let parent = targetElement;
                while (parent) {
                    if (parent.classList.contains('accordion-content') || 
                        parent.classList.contains('department-content') || 
                        parent.classList.contains('lecturer-content')) {
                        parent.style.display = 'block';
                    }
                    parent = parent.parentElement;
                }
                // Scroll ke kursus
                targetElement.scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>";
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management System - Fail Semakan</title>
    <style>
        /* Basic Styling */
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f4f9; 
            color: #333; 
            padding: 0px;
        }
        
        h1 { 
            text-align: center; 
            margin: 20px 0 30px 0;
            color: #2c3e50;
            font-size: 2em;
        }
        
        /* Container Styling */
        .content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Dropdown Containers */
        .accordion {
            width: 100%;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Faculty Level */
        .accordion-header {
            background-color: #34495e;
            color: #fff;
            padding: 15px 25px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: 500;
            border-radius: 8px;
            margin-bottom: 2px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        
        .accordion-header:hover {
            background-color: #2c3e50;
        }
        
        .accordion-content {
            padding: 0 15px;
            display: none;
            background-color: #fff;
        }
        
        /* Department Level */
        .department-header {
            background-color: #3498db;
            color: #fff;
            padding: 12px 25px;
            cursor: pointer;
            font-size: 1.1em;
            margin: 5px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        
        .department-header:hover {
            background-color: #2980b9;
        }
        
        .department-content {
            padding: 0 15px;
            display: none;
        }
        
        /* Lecturer Level */
        .lecturer-header {
            background-color: #5dade2;
            color: #fff;
            padding: 10px 25px;
            cursor: pointer;
            font-size: 1em;
            margin: 5px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        
        .lecturer-header:hover {
            background-color: #3498db;
        }
        
        .lecturer-content {
            padding: 0 15px;
            display: none;
        }
        
        /* Course Level */
        .course-header {
            background-color: #d4e6f1;
            color: #2c3e50;
            padding: 10px 25px;
            cursor: pointer;
            font-size: 1em;
            margin: 5px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        
        .course-header:hover {
            background-color: #c5d9e7;
        }
        
        .course-content {
            padding: 15px;
            display: none;
            background-color: #fff;
            border-radius: 6px;
            margin: 5px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Dropdown Arrows */
        .accordion-header::after,
        .department-header::after,
        .lecturer-header::after,
        .course-header::after {
            content: '\25BC';
            font-size: 0.8em;
            transition: transform 0.3s ease;
        }
        
        .active::after {
            transform: rotate(180deg);
        }
        
        /* Session Selection Styling */
        .session-controls-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .session-select {
            width: 100%;
        }
        
        .session-select select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            color: #2c3e50;
            background-color: #fff;
        }
        
        /* Table Styling */
        .document-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #fff;
        }
        
        .document-table th {
            background-color: #f7f9fa;
            color: #2c3e50;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: 600;
        }
        
        .document-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .document-table tr:hover {
            background-color: #f9f9f9;
        }
        
        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-checked {
            background-color: #27ae60;
            color: white;
        }
        
        .status-unchecked {
            background-color: #e74c3c;
            color: white;
        }
        
        /* View Button */
        .view-btn {
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        
        .view-btn:hover {
            background-color: #2980b9;
        }
        
        /* Course Info */
        .course-info {
            font-size: 0.9em;
            color: #666;
            margin-left: 10px;
        }
        
        .verification-section {
            margin-top: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .verification-info {
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #ccc;
        }

        .verification-info h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .verification-info p {
            margin: 5px 0;
            color: #555;
        }

        .verification-status {
            font-weight: bold;
        }

        .verification-info.verified {
            border-left-color: #27ae60;
            background-color: #f0f9f4;
        }

        .verification-info.verified .verification-status {
            color: #27ae60;
        }

        .verification-info.pending {
            border-left-color: #f39c12;
            background-color: #fef9e7;
        }

        .verification-info.pending .verification-status {
            color: #f39c12;
        }

        .verification-info.not-verified {
            border-left-color: #e74c3c;
            background-color: #fdf3f2;
        }

        .verification-info.not-verified .verification-status {
            color: #e74c3c;
        }
    </style>
</head>
<body>
<?php include('sidebar.php'); ?>

    <h1>Fail Semakan - <?php echo ($serahan_no == 1) ? 'Serahan Pertama' : 'Serahan Kedua'; ?></h1>

    <!-- Session Selection -->
    <div class="session-controls-container">
        <form method="GET" class="session-select">
            <input type="hidden" name="serahan_no" value="<?php echo $serahan_no; ?>">
            <select name="sesi" class="form-select" onchange="this.form.submit()" required>
                <option value="">Pilih Sesi</option>
                <?php
                include "database.php";
                $sesi_query = "SELECT DISTINCT sesi FROM kursus ORDER BY sesi DESC";
                $sesi_result = $conn->query($sesi_query);
                while($sesi_row = $sesi_result->fetch_assoc()) {
                    $selected = (isset($_GET['sesi']) && $_GET['sesi'] == $sesi_row['sesi']) ? 'selected' : '';
                    echo "<option value='{$sesi_row['sesi']}' {$selected}>{$sesi_row['sesi']}</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <?php if(isset($_GET['sesi'])): ?>
    <div class="content accordion">
        <?php
        $sesi = $_GET['sesi'];
        
        // Fetch Distinct Faculties
        $faculty_query = "SELECT DISTINCT p.fakulti 
                         FROM pensyarah p 
                         JOIN kursus k ON p.id = k.pensyarah_id 
                         WHERE k.sesi = '$sesi'";
        $faculties = $conn->query($faculty_query);

        while ($faculty = $faculties->fetch_assoc()) {
            echo '<div class="accordion-item">';
            echo '<div class="accordion-header">' . $faculty['fakulti'] . '</div>';
            echo '<div class="accordion-content">';

            // Fetch Distinct Departments
            $department_query = "SELECT DISTINCT p.jabatan 
                               FROM pensyarah p 
                               JOIN kursus k ON p.id = k.pensyarah_id 
                               WHERE p.fakulti = '{$faculty['fakulti']}' 
                               AND k.sesi = '$sesi'";
            $departments = $conn->query($department_query);

            while ($department = $departments->fetch_assoc()) {
                echo '<div class="department-header">' . $department['jabatan'] . '</div>';
                echo '<div class="department-content">';

                // Fetch Lecturers
                $lecturer_query = "SELECT DISTINCT p.* 
                                 FROM pensyarah p 
                                 JOIN kursus k ON p.id = k.pensyarah_id 
                                 WHERE p.fakulti = '{$faculty['fakulti']}' 
                                 AND p.jabatan = '{$department['jabatan']}' 
                                 AND k.sesi = '$sesi'";
                $lecturers = $conn->query($lecturer_query);

                while ($lecturer = $lecturers->fetch_assoc()) {
                    echo '<div class="lecturer-header">' . $lecturer['nama_pensyarah'] . '</div>';
                    echo '<div class="lecturer-content">';
                    
                    // Fetch Courses
                    $course_query = "SELECT * FROM kursus 
                                   WHERE pensyarah_id = {$lecturer['id']} 
                                   AND sesi = '$sesi'";
                    $courses = $conn->query($course_query);
                    
                    while ($course = $courses->fetch_assoc()) {
                        echo '<div class="course-header" id="kursus-' . $course['id'] . '" 
                                 ' . ($course['id'] == $active_course_id ? 'data-auto-open="true"' : '') . '>
                                ' . $course['kod_kursus'] . ' - ' . $course['nama_kursus'] . 
                                ' <span class="course-info">' . $course['program'] . ' - Semester ' . $course['semester'] . '</span>
                              </div>';
                        echo '<div class="course-content" ' . ($course['id'] == $active_course_id ? 'style="display: block;"' : '') . '>';
                        
                        // Jadual Dokumen
                        echo '<table class="document-table">
                                <thead>
                                    <tr>
                                        <th>Nama Dokumen</th>
                                        <th>Masa Hantar</th>
                                        <th>Masa Semak</th>
                                        <th>Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        
                        $document_query = "SELECT * FROM dokumen 
                                         WHERE serahan_no = $serahan_no 
                                         AND kursus_id = " . $course['id'];
                        $documents = $conn->query($document_query);

                        while ($document = $documents->fetch_assoc()) {
                            echo '<tr>
                                    <td>' . htmlspecialchars($document['nama_dokumen']) . '</td>
                                    <td>' . 
                                        (!empty($document['path_dokumen']) ? 
                                            date('d/m/Y H:i', strtotime($document['uploaded_at'])) : 
                                            '<span class="status-badge status-unchecked">Belum Hantar</span>'
                                        ) . 
                                    '</td>
                                    <td>' . 
                                        ($document['status'] === 'Checked' ? 
                                            date('d/m/Y H:i', strtotime($document['checked_at'])) : 
                                            '<span class="status-badge status-unchecked">Belum Disemak</span>'
                                        ) . 
                                    '</td>
                                    <td>' . 
                                        (!empty($document['path_dokumen']) ? 
                                            '<a href="' . htmlspecialchars($document['path_dokumen']) . '" class="view-btn" target="_blank">Lihat</a>' : 
                                            '<span class="status-badge status-unchecked">Tiada Fail</span>'
                                        ) . 
                                    '</td>
                                </tr>';
                        }
                        
                        echo '</tbody></table>';

                        // Tambah bahagian pengesahan kursus
                        echo '<div class="verification-section">';

                        // Semak status pengesahan untuk kursus ini
                        $verification_query = "SELECT pk.*, p.nama_pensyarah 
                                              FROM pengesahan_kursus pk 
                                              JOIN pensyarah p ON pk.disahkan_oleh = p.id 
                                              WHERE pk.kursus_id = " . $course['id'] . "
                                              AND pk.no_serahan = " . $serahan_no . "
                                              ORDER BY pk.waktu_pengesahan DESC 
                                              LIMIT 1";
                        $verification_result = $conn->query($verification_query);

                        if ($verification_result->num_rows > 0) {
                            $verification = $verification_result->fetch_assoc();
                            echo '<div class="verification-info ' . 
                                 ($verification['Status_pengesahan'] == 'telah disahkan' ? 'verified' : 'pending') . 
                                 '">';
                            echo '<h4>Status Pengesahan</h4>';
                            echo '<p>Status: <span class="verification-status">' . 
                                 ($verification['Status_pengesahan'] == 'telah disahkan' ? 'Telah Disahkan' : 'Belum Disahkan') . 
                                 '</span></p>';
                            echo '<p>Disahkan oleh: ' . htmlspecialchars($verification['nama_pensyarah']) . '</p>';
                            echo '<p>Waktu Pengesahan: ' . date('d/m/Y H:i', strtotime($verification['waktu_pengesahan'])) . '</p>';
                            if (!empty($verification['ulasan'])) {
                                echo '<p>Ulasan: ' . htmlspecialchars($verification['ulasan']) . '</p>';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="verification-info not-verified">';
                            echo '<h4>Status Pengesahan</h4>';
                            echo '<p>Status: <span class="verification-status">Belum Disahkan</span></p>';
                            echo '</div>';
                        }

                        echo '</div>'; // Close verification-section

                        // Tambah style untuk bahagian pengesahan
                        echo '<style>
                            .verification-section {
                                margin-top: 20px;
                                padding: 20px;
                                background-color: #fff;
                                border-radius: 8px;
                                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                            }

                            .verification-info {
                                padding: 15px;
                                border-radius: 6px;
                                border-left: 4px solid #ccc;
                            }

                            .verification-info h4 {
                                margin: 0 0 10px 0;
                                color: #2c3e50;
                            }

                            .verification-info p {
                                margin: 5px 0;
                                color: #555;
                            }

                            .verification-status {
                                font-weight: bold;
                            }

                            .verification-info.verified {
                                border-left-color: #27ae60;
                                background-color: #f0f9f4;
                            }

                            .verification-info.verified .verification-status {
                                color: #27ae60;
                            }

                            .verification-info.pending {
                                border-left-color: #f39c12;
                                background-color: #fef9e7;
                            }

                            .verification-info.pending .verification-status {
                                color: #f39c12;
                            }

                            .verification-info.not-verified {
                                border-left-color: #e74c3c;
                                background-color: #fdf3f2;
                            }

                            .verification-info.not-verified .verification-status {
                                color: #e74c3c;
                            }
                        </style>';

                        echo '</div>'; // Close course-content
                    }
                    
                    echo '</div>'; // Close lecturer-content
                }

                echo '</div>'; // Close department-content
            }

            echo '</div>'; // Close accordion-content
            echo '</div>'; // Close accordion-item
        }
        ?>
    </div>
    <?php endif; ?>

    <script>
        // JavaScript for Accordion Toggle
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', () => {
                header.classList.toggle('active');
                const content = header.nextElementSibling;
                content.style.display = content.style.display === "block" ? "none" : "block";
            });
        });

        document.querySelectorAll('.department-header').forEach(department => {
            department.addEventListener('click', () => {
                department.classList.toggle('active');
                const departmentContent = department.nextElementSibling;
                departmentContent.style.display = departmentContent.style.display === "block" ? "none" : "block";
            });
        });

        document.querySelectorAll('.lecturer-header').forEach(lecturer => {
            lecturer.addEventListener('click', () => {
                lecturer.classList.toggle('active');
                const lecturerContent = lecturer.nextElementSibling;
                lecturerContent.style.display = lecturerContent.style.display === "block" ? "none" : "block";
            });
        });
		
        // Function to handle form submission
        function updateDocument(event, dokumenId) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            formData.append('dokumen_id', dokumenId);

            fetch('update_dokumen.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log("Response data:", data);
                if (data.trim() === "success") {
                    const statusText = document.getElementById(`status-text-${dokumenId}`);
                    const isChecked = formData.get('status') === 'Checked';
                    statusText.textContent = `Status: ${isChecked ? 'Checked' : 'Not Checked'}`;
                    alert('Document updated successfully');
                } else {
                    alert('Error updating document: ' + data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the document');
            });
        }

        // Function to handle checkbox change
        function updateDocumentCheckbox(checkbox, dokumenId) {
            const formData = new FormData();
            formData.append('dokumen_id', dokumenId);
            formData.append('status', checkbox.checked ? 'Checked' : '');

            fetch('update_dokumen.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log("Checkbox change response:", data);
                if (data.trim() === "success") {
                    const statusText = document.getElementById(`status-text-${dokumenId}`);
                    statusText.textContent = `Status: ${checkbox.checked ? 'Checked' : 'Not Checked'}`;
                } else {
                    alert('Error updating status: ' + data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the status');
            });
        }

        // Add event listener for course headers
        document.querySelectorAll('.course-header').forEach(course => {
            course.addEventListener('click', () => {
                course.classList.toggle('active');
                const courseContent = course.nextElementSibling;
                courseContent.style.display = courseContent.style.display === "block" ? "none" : "block";
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Existing accordion code...

            // Auto-open specific course if needed
            const courseToOpen = document.querySelector('[data-auto-open="true"]');
            if (courseToOpen) {
                // Buka semua parent accordion
                let parent = courseToOpen.parentElement;
                while (parent) {
                    if (parent.classList.contains('lecturer-content')) {
                        parent.style.display = 'block';
                        parent.previousElementSibling.classList.add('active');
                    } else if (parent.classList.contains('department-content')) {
                        parent.style.display = 'block';
                        parent.previousElementSibling.classList.add('active');
                    } else if (parent.classList.contains('accordion-content')) {
                        parent.style.display = 'block';
                        parent.previousElementSibling.classList.add('active');
                    }
                    parent = parent.parentElement;
                }

                // Buka kursus yang dipilih
                courseToOpen.classList.add('active');
                courseToOpen.nextElementSibling.style.display = 'block';

                // Scroll ke kursus yang dipilih
                courseToOpen.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>

</body>
</html>
