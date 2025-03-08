<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Kursus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-container {
            margin-top: 50px;
            margin-bottom: 30px;
            margin-left: 20px;
            margin-right: 20px;
        }
        .center-button {
            display: flex;
            justify-content: right;
        }
        .session-select {
            max-width: 300px;
            margin: 20px auto;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .session-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .session-select {
    max-width: 300px;
    margin: 20px auto;
}

.session-select select {
    border-radius: 5px;
    border: 1px solid #ced4da;
}

.session-select select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.session-controls-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin: 20px auto;
    max-width: 600px;
}

.session-select {
    flex: 1;
    max-width: 300px;
    margin: 0; /* Remove margin as it's handled by the container */
}

.session-select select {
    border-radius: 5px;
    border: 1px solid #ced4da;
    width: 100%;
}

.session-select select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.search-container {
    width: 300px;
}

.center-button {
    margin-bottom: 15px;
}

.sortable {
    cursor: pointer;
}

.sortable:hover {
    background-color: #2c3034;
}

.sortable::after {
    content: '↕️';
    font-size: 12px;
    margin-left: 5px;
}

.sortable.asc::after {
    content: '↑';
}

.sortable.desc::after {
    content: '↓';
}
    </style>
</head>
<body>
<?php include('sidebar.php'); ?>

    <div class="content table-container">
        <h1 class="text-center mb-4">Senarai Kursus</h1>

        <!-- Session Controls Container -->
        <div class="session-controls-container">
            <!-- Session Selection Form -->
            <form method="GET" class="session-select">
                <div class="input-group">
                    <select name="sesi" class="form-select" onchange="this.form.submit()" required>
                        <option value="">Pilih Sesi</option>
                        <?php
                        include "database.php";
                        $sesi_query = "SELECT DISTINCT sesi_id as sesi FROM sesi 
                                      UNION 
                                      SELECT DISTINCT sesi FROM kursus 
                                      ORDER BY sesi DESC";
                        $sesi_result = $conn->query($sesi_query);
                        while($sesi_row = $sesi_result->fetch_assoc()) {
                            $selected = (isset($_GET['sesi']) && $_GET['sesi'] == $sesi_row['sesi']) ? 'selected' : '';
                            echo "<option value='{$sesi_row['sesi']}' {$selected}>{$sesi_row['sesi']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </form>

            <!-- Add Session Button -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                Tambah Sesi Baharu
            </button>
        </div>

        <!-- Add Session Modal -->
        <div class="modal fade" id="addSessionModal" tabindex="-1" aria-labelledby="addSessionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSessionModalLabel">Tambah Sesi Baharu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="tambah_sesi.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="newSession" class="form-label">Sesi</label>
                                <input type="number" class="form-control" id="newSession" name="sesi" 
                                       placeholder="Contoh: 20232024" required
                                       min="20232024" max="99999999"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <small class="text-muted">Format: YYYYYYYY (contoh: 20232024)</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php
        if(isset($_GET['sesi'])) {
            $sesi = $_GET['sesi'];
            echo '<div class="center-button d-flex justify-content-between align-items-center">
                    <div class="search-container">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari kursus...">
                    </div>
                    <button class="btn btn-success" onclick="window.location.href=\'tambah_kursus.php?sesi=' . $sesi . '\'">
                        Tambah Kursus
                    </button>
                  </div>
                  <br>';
            ?>
            <table class="table table-bordered table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Bil</th>
                        <th class="sortable" data-sort="nama_kursus">Nama Kursus</th>
                        <th class="sortable" data-sort="kod_kursus">Kod Kursus</th>
                        <th class="sortable" data-sort="program">Program</th>
                        <th class="sortable" data-sort="semester">Semester</th>
                        <th class="sortable" data-sort="nama_pensyarah">Nama Pensyarah</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="courseTableBody">
                    <?php
                    $sql = "SELECT k.*, p.nama_pensyarah 
                            FROM kursus k 
                            LEFT JOIN pensyarah p ON k.pensyarah_id = p.id
                            WHERE k.sesi = $sesi
                            ORDER BY k.nama_kursus ASC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $bil = 1; // Pembolehubah untuk nombor berjujukan
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$bil}</td>
                                    <td>{$row['nama_kursus']}</td>
                                    <td>{$row['kod_kursus']}</td>
                                    <td>{$row['program']}</td>
                                    <td>{$row['semester']}</td>
                                    <td>" . ($row['nama_pensyarah'] ?? 'NULL') . "</td>
                                    <td>
                                        <a href='kemaskini_kursus.php?id={$row['id']}' class='btn btn-primary btn-sm'>Edit</a>
                                        <a href='buang_kursus.php?id={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this course?\")'>Delete</a>
                                    </td>
                                  </tr>";
                            $bil++; // Tambah nombor berjujukan
                        }
                    } else {
                        echo "<tr><td colspan='7'>No records found for this session</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php
        } else {
            echo '<div class="text-center mt-4">Sila pilih sesi untuk melihat senarai kursus</div>';
        }
        $conn->close();
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let searchText = this.value.toLowerCase();
        let tableRows = document.querySelectorAll('tbody tr');
        let visibleCount = 0;
        
        tableRows.forEach(row => {
            let text = row.textContent.toLowerCase();
            let isVisible = text.includes(searchText);
            row.style.display = isVisible ? '' : 'none';
            
            // Kemaskini nombor berjujukan untuk baris yang kelihatan sahaja
            if (isVisible) {
                visibleCount++;
                row.children[0].textContent = visibleCount;
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        let sortDirection = 'asc';
        let lastSortedColumn = '';

        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', function() {
                const column = this.dataset.sort;
                
                // Toggle sort direction
                if (lastSortedColumn === column) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortDirection = 'asc';
                }
                lastSortedColumn = column;

                // Update header classes
                document.querySelectorAll('.sortable').forEach(h => {
                    h.classList.remove('asc', 'desc');
                });
                this.classList.add(sortDirection);

                // Get table rows and convert to array for sorting
                const tbody = document.getElementById('courseTableBody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                // Sort rows
                rows.sort((a, b) => {
                    let aValue = a.children[getColumnIndex(column)].textContent.trim();
                    let bValue = b.children[getColumnIndex(column)].textContent.trim();

                    // Handle semester sorting numerically
                    if (column === 'semester') {
                        return sortDirection === 'asc' ? 
                            parseInt(aValue) - parseInt(bValue) : 
                            parseInt(bValue) - parseInt(aValue);
                    }

                    // Regular string sorting
                    return sortDirection === 'asc' ? 
                        aValue.localeCompare(bValue) : 
                        bValue.localeCompare(aValue);
                });

                // Reorder rows in DOM
                rows.forEach((row, index) => {
                    row.children[0].textContent = index + 1; // Update Bil numbers
                    tbody.appendChild(row);
                });
            });
        });

        // Helper function to get column index based on data-sort value
        function getColumnIndex(column) {
            switch(column) {
                case 'nama_kursus': return 1;
                case 'kod_kursus': return 2;
                case 'program': return 3;
                case 'semester': return 4;
                case 'nama_pensyarah': return 5;
                default: return 0;
            }
        }
    });
    </script>
</body>
</html> 