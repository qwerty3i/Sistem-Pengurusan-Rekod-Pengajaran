<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturers with Missing Documents</title>
    <style>
        /* Basic Styling */
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; color: #333; }
        h1 { text-align: center; margin-top: 20px; color: #333; }
        .accordion { max-width: 700px; margin: 20px auto; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px; background: #fff; }
        .accordion-header { background-color: #4a90e2; color: #fff; padding: 15px 20px; cursor: pointer; font-size: 18px; font-weight: bold; border-radius: 8px; }
        .accordion-content { display: none; padding: 10px 20px; font-size: 16px; color: #333; border-bottom: 1px solid #ddd; }
        .department-header { background-color: #72b0e0; color: #fff; padding: 10px 15px; cursor: pointer; font-size: 16px; font-weight: bold; margin: 5px 0; border-radius: 5px; }
        .department-content { display: none; padding-left: 20px; font-size: 15px; color: #333; }
        .lecturer-header { background-color: #b2d3f2; color: #333; padding: 8px 15px; cursor: pointer; font-size: 15px; font-weight: bold; margin: 5px 0; border-radius: 5px; }
        .lecturer-content { display: none; padding-left: 20px; font-size: 1rem; color: #333; }
        .missing-doc { color: red; font-weight: bold; }
        .accordion-header::after, .department-header::after, .lecturer-header::after { content: "\25BC"; float: right; transition: transform 0.3s; }
        .accordion-header.active::after, .department-header.active::after, .lecturer-header.active::after { transform: rotate(180deg); }
    </style>
</head>
<body>

    <h1>Lecturers with Missing Documents</h1>
    <div class="accordion">

        <?php
        include('database.php');

        $faculty_query = "SELECT DISTINCT fakulti FROM pensyarah";
        $faculties = $conn->query($faculty_query);

        while ($faculty = $faculties->fetch_assoc()) {
            echo '<div class="accordion-item">';
            echo '<div class="accordion-header">' . htmlspecialchars($faculty['fakulti']) . '</div>';
            echo '<div class="accordion-content">';

            $department_query = "SELECT DISTINCT jabatan FROM pensyarah WHERE fakulti = '" . $faculty['fakulti'] . "'";
            $departments = $conn->query($department_query);

            while ($department = $departments->fetch_assoc()) {
                echo '<div class="department-header">' . htmlspecialchars($department['jabatan']) . '</div>';
                echo '<div class="department-content">';

                $lecturer_query = "
                    SELECT p.id, p.nama_pensyarah 
                    FROM pensyarah p
                    LEFT JOIN dokumen d ON p.id = d.pensyarah_id
                    WHERE p.fakulti = '" . $faculty['fakulti'] . "' 
                    AND p.jabatan = '" . $department['jabatan'] . "'
                    AND (d.path_dokumen IS NULL OR d.path_dokumen = '')
                    GROUP BY p.id
                ";
                
                $lecturers = $conn->query($lecturer_query);

                if ($lecturers->num_rows > 0) {
                    while ($lecturer = $lecturers->fetch_assoc()) {
                        echo '<div class="lecturer-header">' . htmlspecialchars($lecturer['nama_pensyarah']) . '</div>';
                        echo '<div class="lecturer-content">';

                        $missing_docs_query = "
                            SELECT nama_dokumen 
                            FROM dokumen 
                            WHERE pensyarah_id = '" . $lecturer['id'] . "' 
                            AND (path_dokumen IS NULL OR path_dokumen = '')
                        ";
                        $missing_docs = $conn->query($missing_docs_query);

                        if ($missing_docs->num_rows > 0) {
                            echo '<ul>';
                            while ($doc = $missing_docs->fetch_assoc()) {
                                echo '<li class="missing-doc">- ' . htmlspecialchars($doc['nama_dokumen']) . '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p class="missing-doc">No documents uploaded.</p>';
                        }
                        echo '</div>'; // End lecturer content
                    }
                } else {
                    echo '<p>No lecturers with incomplete documents in this department.</p>';
                }

                echo '</div>'; // End department content
            }

            echo '</div>'; // End accordion content for faculty
            echo '</div>'; // End accordion item for faculty
        }

        $conn->close();
        ?>

    </div>

    <script>
        document.querySelectorAll('.accordion-header, .department-header').forEach(header => {
            header.addEventListener('click', () => {
                header.classList.toggle('active');
                const content = header.nextElementSibling;
                content.style.display = content.style.display === "block" ? "none" : "block";
            });
        });

        document.querySelectorAll('.lecturer-header').forEach(lecturer => {
            lecturer.addEventListener('click', () => {
                lecturer.classList.toggle('active');
                const lecturerContent = lecturer.nextElementSibling;
                lecturerContent.style.display = lecturerContent.style.display === "block" ? "none" : "block";
            });
        });
    </script>

</body>
</html>
