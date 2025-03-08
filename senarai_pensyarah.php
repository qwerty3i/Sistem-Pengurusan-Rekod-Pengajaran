<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Pensyarah</title>
    <!-- Bootstrap CSS -->
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
        <h1 class="text-center mb-4">Senarai Pensyarah</h1>
        
        <div class="center-button d-flex justify-content-between align-items-center">
            <div class="search-container">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari pensyarah...">
            </div>
            <button class="btn btn-success" onclick="window.location.href='tambah_pensyarah.php'">
                Tambah Pensyarah
            </button>
        </div>
        <br>
        <table class="table table-bordered table-striped text-center">
            <thead class="table-dark">
                <tr>
                    <th>Bil</th>
                    <th class="sortable" data-sort="nama_pensyarah">Nama Pensyarah</th>
                    <th class="sortable" data-sort="ketua_jabatan">Ketua Jabatan</th>
                    <th class="sortable" data-sort="jabatan">Jabatan</th>
                    <th class="sortable" data-sort="fakulti">Fakulti</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="lecturerTableBody">
                <?php
                include "database.php";
                $sql = "SELECT * FROM pensyarah";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $bil = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$bil}</td>
                                <td>{$row['nama_pensyarah']}</td>
                                <td>{$row['ketua_jabatan']}</td>
                                <td>{$row['jabatan']}</td>
                                <td>{$row['fakulti']}</td>
                                <td>
                                    <a href='kemaskini_pensyarah.php?id={$row['id']}' class='btn btn-primary btn-sm'>Edit</a>
                                    <a href='buang_pensyarah.php?id={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this lecturer?\")'>Delete</a>
                                </td>
                              </tr>";
                        $bil++;
                    }
                } else {
                    echo "<tr><td colspan='6'>No records found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>

       
    </div>

    <!-- Bootstrap JS -->
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
    </script>
    <script>
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
                const tbody = document.getElementById('lecturerTableBody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                // Sort rows
                rows.sort((a, b) => {
                    let aValue = a.children[getColumnIndex(column)].textContent.trim();
                    let bValue = b.children[getColumnIndex(column)].textContent.trim();

                    return sortDirection === 'asc' ? 
                        aValue.localeCompare(bValue) : 
                        bValue.localeCompare(aValue);
                });

                // Reorder rows in DOM and update Bil numbers
                rows.forEach((row, index) => {
                    row.children[0].textContent = index + 1; // Update Bil numbers
                    tbody.appendChild(row);
                });
            });
        });

        // Helper function to get column index based on data-sort value
        function getColumnIndex(column) {
            switch(column) {
                case 'nama_pensyarah': return 1;
                case 'ketua_jabatan': return 2;
                case 'jabatan': return 3;
                case 'fakulti': return 4;
                default: return 0;
            }
        }
    });
    </script>
</body>
</html>
