<?php
include('database.php');
include('sidebar.php');

// Dapatkan senarai sesi
$sesi_query = "SELECT sesi_id FROM sesi ORDER BY sesi_id DESC";
$sesi_result = $conn->query($sesi_query);

// Dapatkan sesi yang dipilih
$selected_sesi = isset($_GET['sesi']) ? $_GET['sesi'] : null;
if (!$selected_sesi) {
    $current_sesi_query = "SELECT sesi_id FROM sesi ORDER BY sesi_id DESC LIMIT 1";
    $selected_sesi = $conn->query($current_sesi_query)->fetch_assoc()['sesi_id'];
}

// Fungsi untuk mendapatkan status serahan mengikut kursus
function getSubmissionStatus($conn, $kursus_id, $serahan_no) {
    $query = "SELECT COUNT(*) as total, 
              SUM(CASE WHEN path_dokumen != '' THEN 1 ELSE 0 END) as submitted,
              SUM(CASE WHEN status = 'Checked' THEN 1 ELSE 0 END) as checked
              FROM dokumen 
              WHERE kursus_id = $kursus_id AND serahan_no = $serahan_no";
    return $conn->query($query)->fetch_assoc();
}

// Query untuk mendapatkan senarai kursus dan status
$kursus_query = "
    SELECT 
        k.id,
        k.kod_kursus,
        k.nama_kursus,
        k.program,
        k.semester,
        p.nama_pensyarah,
        COALESCE(pk1.Status_pengesahan, 'belum disahkan') as status_serahan1,
        COALESCE(pk2.Status_pengesahan, 'belum disahkan') as status_serahan2
    FROM kursus k
    LEFT JOIN pensyarah p ON k.pensyarah_id = p.id
    LEFT JOIN pengesahan_kursus pk1 ON k.id = pk1.kursus_id AND pk1.no_serahan = 1
    LEFT JOIN pengesahan_kursus pk2 ON k.id = pk2.kursus_id AND pk2.no_serahan = 2
    WHERE k.sesi = '$selected_sesi'
    ORDER BY k.kod_kursus";

$kursus_result = $conn->query($kursus_query);

// Dapatkan tarikh akhir
$deadline_query = "SELECT * FROM deadlines ORDER BY serahan_no";
$deadline_result = $conn->query($deadline_query);
$deadlines = [];
while($row = $deadline_result->fetch_assoc()) {
    $deadlines[$row['serahan_no']] = $row['deadline_date'];
}

// Fungsi untuk mendapatkan singkatan program
function getProgramAcronym($program) {
    $words = explode(' ', $program);
    $acronym = '';
    foreach ($words as $word) {
        // Ambil huruf pertama setiap perkataan, kecuali perkataan 'dan', 'in', dll
        if (!in_array(strtolower($word), ['dan', 'in', 'and', 'of'])) {
            $acronym .= strtoupper(substr($word, 0, 1));
        }
    }
    return $acronym;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Terperinci</title>
    <style>
        .report-container {
            padding: 20px;
        }

        .sesi-selector {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-container {
            width: 300px;
            position: relative;
        }

        .search-container .form-control {
            padding-left: 35px;
            height: 38px;
        }

        .search-container::before {
            content: '🔍';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 15px;
        }

        .sesi-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sesi-form select {
            width: 200px;
            height: 38px;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: #fff;
        }

        .sesi-form label {
            margin-bottom: 0;
            color: #495057;
            font-weight: 500;
        }

        .report-filters {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .report-table th, 
        .report-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            min-width: 150px;
        }

        .report-table th {
            background: #f5f6fa;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .status-complete {
            background: #d4edda;
            color: #155724;
        }

        .status-incomplete {
            background: #f8d7da;
            color: #721c24;
        }

        .status-partial {
            background: #fff3cd;
            color: #856404;
        }

        .deadline-warning {
            color: #dc3545;
            font-weight: bold;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .progress-bar {
            height: 8px;
            background: #eee;
            border-radius: 4px;
            margin-top: 8px;
        }

        .progress-fill {
            height: 100%;
            background: #3498db;
            border-radius: 4px;
        }

        .action-button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #3498db;
            color: white;
            margin-right: 5px;
        }

        .action-button:hover {
            background: #2980b9;
        }

        .reminder-button {
            background: #e74c3c;
        }

        .reminder-button:hover {
            background: #c0392b;
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

        .details-btn {
            padding: 4px 12px;
            background-color: #6c757d;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85em;
            transition: background-color 0.2s;
            white-space: nowrap;
            margin-left: 10px;
        }

        .details-btn:hover {
            background-color: #5a6268;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <h1>Laporan Terperinci</h1>

        <!-- Pemilih Sesi -->
        <div class="sesi-selector">
            <div class="search-container">
                <input type="text" 
                       id="searchInput" 
                       class="form-control" 
                       placeholder="Cari kursus atau pensyarah..."
                       autocomplete="off">
            </div>
            <form method="GET" action="" class="sesi-form">
                <label for="sesi">Pilih Sesi:</label>
                <select name="sesi" 
                        id="sesi" 
                        class="form-select" 
                        onchange="this.form.submit()">
                    <?php while($sesi = $sesi_result->fetch_assoc()): ?>
                        <option value="<?php echo $sesi['sesi_id']; ?>" 
                                <?php echo ($sesi['sesi_id'] == $selected_sesi) ? 'selected' : ''; ?>>
                            <?php echo $sesi['sesi_id']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <!-- Ringkasan Status -->
        <div class="summary-cards">
            <?php foreach([1, 2] as $serahan_no): ?>
                <div class="summary-card">
                    <h3>Serahan <?php echo $serahan_no; ?></h3>
                    <?php
                    $total_query = "SELECT 
                        COUNT(*) as total_kursus,
                        SUM(CASE WHEN pk.Status_pengesahan = 'telah disahkan' THEN 1 ELSE 0 END) as disahkan
                    FROM kursus k
                    LEFT JOIN pengesahan_kursus pk ON k.id = pk.kursus_id AND pk.no_serahan = $serahan_no
                    WHERE k.sesi = '$selected_sesi'";
                    $total_result = $conn->query($total_query)->fetch_assoc();
                    $percentage = ($total_result['total_kursus'] > 0) ? 
                        ($total_result['disahkan'] / $total_result['total_kursus'] * 100) : 0;
                    ?>
                    <p>Status Pengesahan: <?php echo $total_result['disahkan']; ?>/<?php echo $total_result['total_kursus']; ?></p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                    <?php if(isset($deadlines[$serahan_no])): ?>
                        <p>Tarikh Akhir: <?php echo date('d/m/Y', strtotime($deadlines[$serahan_no])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Jadual Terperinci -->
        <table class="report-table">
            <thead>
                <tr>
                    <th>Bil</th>
                    <th class="sortable" data-sort="kod">Kod Kursus</th>
                    <th class="sortable" data-sort="nama">Nama Kursus</th>
                    <th class="sortable" data-sort="pensyarah">Pensyarah</th>
                    <th class="sortable" data-sort="program">Program</th>
                    <th class="sortable" data-sort="serahan1">Serahan 1</th>
                    <th class="sortable" data-sort="serahan2">Serahan 2</th>
                    <th class="sortable" data-sort="status">Status Semakan</th>
                </tr>
            </thead>
            <tbody id="reportTableBody">
                <?php 
                $bil = 1;
                while($kursus = $kursus_result->fetch_assoc()): 
                    $serahan1_status = getSubmissionStatus($conn, $kursus['id'], 1);
                    $serahan2_status = getSubmissionStatus($conn, $kursus['id'], 2);
                    $program_acronym = getProgramAcronym($kursus['program']);
                    ?>
                    <tr>
                        <td><?php echo $bil++; ?></td>
                        <td><?php echo $kursus['kod_kursus']; ?></td>
                        <td><?php echo $kursus['nama_kursus']; ?></td>
                        <td><?php echo $kursus['nama_pensyarah']; ?></td>
                        <td><?php echo $program_acronym . '/' . $kursus['semester']; ?></td>
                        <td>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="status-badge <?php 
                                    echo ($serahan1_status['submitted'] == $serahan1_status['total']) ? 'status-complete' : 
                                         ($serahan1_status['submitted'] > 0 ? 'status-partial' : 'status-incomplete'); 
                                ?>">
                                    <?php echo $serahan1_status['submitted']; ?>/<?php echo $serahan1_status['total']; ?> Dokumen
                                </span>
                                <a href="file_management.php?serahan_no=1&sesi=<?php echo $selected_sesi; ?>&kursus_id=<?php echo $kursus['id']; ?>&kod_kursus=<?php echo urlencode($kursus['kod_kursus']); ?>&nama_kursus=<?php echo urlencode($kursus['nama_kursus']); ?>&program=<?php echo urlencode($kursus['program']); ?>&semester=<?php echo $kursus['semester']; ?>" 
                                   class="details-btn">
                                    Details
                                </a>
                            </div>
                            <?php if(isset($deadlines[1]) && strtotime($deadlines[1]) < time() && 
                                    $serahan1_status['submitted'] < $serahan1_status['total']): ?>
                                <div class="deadline-warning">Lewat</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="status-badge <?php 
                                    echo ($serahan2_status['submitted'] == $serahan2_status['total']) ? 'status-complete' : 
                                         ($serahan2_status['submitted'] > 0 ? 'status-partial' : 'status-incomplete'); 
                                ?>">
                                    <?php echo $serahan2_status['submitted']; ?>/<?php echo $serahan2_status['total']; ?> Dokumen
                                </span>
                                <a href="file_management.php?serahan_no=2&sesi=<?php echo $selected_sesi; ?>&kursus_id=<?php echo $kursus['id']; ?>&kod_kursus=<?php echo urlencode($kursus['kod_kursus']); ?>&nama_kursus=<?php echo urlencode($kursus['nama_kursus']); ?>&program=<?php echo urlencode($kursus['program']); ?>&semester=<?php echo $kursus['semester']; ?>" 
                                   class="details-btn">
                                    Details
                                </a>
                            </div>
                            <?php if(isset($deadlines[2]) && strtotime($deadlines[2]) < time() && 
                                    $serahan2_status['submitted'] < $serahan2_status['total']): ?>
                                <div class="deadline-warning">Lewat</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php 
                                echo ($serahan1_status['checked'] == $serahan1_status['total'] && 
                                     $serahan2_status['checked'] == $serahan2_status['total']) ? 'status-complete' : 
                                    ($serahan1_status['checked'] > 0 || $serahan2_status['checked'] > 0 ? 'status-partial' : 'status-incomplete'); 
                            ?>">
                                <?php echo $serahan1_status['checked'] + $serahan2_status['checked']; ?>/
                                <?php echo $serahan1_status['total'] + $serahan2_status['total']; ?> Disemak
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let searchText = this.value.toLowerCase();
        let tableRows = document.querySelectorAll('.report-table tbody tr');
        let visibleCount = 0;
        
        tableRows.forEach(row => {
            let kodKursus = row.children[1].textContent.toLowerCase();
            let namaKursus = row.children[2].textContent.toLowerCase();
            let namaPensyarah = row.children[3].textContent.toLowerCase();
            
            let isVisible = kodKursus.includes(searchText) || 
                           namaKursus.includes(searchText) || 
                           namaPensyarah.includes(searchText);
                           
            row.style.display = isVisible ? '' : 'none';
            
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
                const tbody = document.getElementById('reportTableBody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                // Sort rows
                rows.sort((a, b) => {
                    let aValue, bValue;
                    
                    switch(column) {
                        case 'kod':
                            aValue = a.children[1].textContent.trim();
                            bValue = b.children[1].textContent.trim();
                            break;
                        case 'nama':
                            aValue = a.children[2].textContent.trim();
                            bValue = b.children[2].textContent.trim();
                            break;
                        case 'pensyarah':
                            aValue = a.children[3].textContent.trim();
                            bValue = b.children[3].textContent.trim();
                            break;
                        case 'program':
                            aValue = a.children[4].textContent.trim();
                            bValue = b.children[4].textContent.trim();
                            break;
                        case 'serahan1':
                            // Mengambil nombor dari format "X/Y Dokumen"
                            aValue = parseInt(a.children[5].textContent.split('/')[0]);
                            bValue = parseInt(b.children[5].textContent.split('/')[0]);
                            return sortDirection === 'asc' ? aValue - bValue : bValue - aValue;
                        case 'serahan2':
                            aValue = parseInt(a.children[6].textContent.split('/')[0]);
                            bValue = parseInt(b.children[6].textContent.split('/')[0]);
                            return sortDirection === 'asc' ? aValue - bValue : bValue - aValue;
                        case 'status':
                            aValue = parseInt(a.children[7].textContent.split('/')[0]);
                            bValue = parseInt(b.children[7].textContent.split('/')[0]);
                            return sortDirection === 'asc' ? aValue - bValue : bValue - aValue;
                        default:
                            aValue = a.children[1].textContent.trim();
                            bValue = b.children[1].textContent.trim();
                    }

                    // Untuk kes teks
                    if (column !== 'serahan1' && column !== 'serahan2' && column !== 'status') {
                        return sortDirection === 'asc' ? 
                            aValue.localeCompare(bValue) : 
                            bValue.localeCompare(aValue);
                    }
                });

                // Reorder rows in DOM and update Bil numbers
                rows.forEach((row, index) => {
                    row.children[0].textContent = index + 1; // Update Bil numbers
                    tbody.appendChild(row);
                });
            });
        });
    });
    </script>
</body>
</html> 