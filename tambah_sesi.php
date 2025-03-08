<?php
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sesi = mysqli_real_escape_string($conn, $_POST['sesi']);
    
    // Validate session format
    if (!preg_match("/^[0-9]{8}$/", $sesi)) {
        echo "<script>
                alert('Format sesi tidak sah! Gunakan format YYYYYYYY (contoh: 20232024)');
                window.location.href = 'senarai_kursus.php';
              </script>";
        exit();
    }

    // Check if session exists in either table
    $check_sql = "SELECT sesi_id FROM sesi WHERE sesi_id = '$sesi' 
                  UNION 
                  SELECT sesi FROM kursus WHERE sesi = '$sesi' LIMIT 1";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('Sesi ini telah wujud!');
                window.location.href = 'senarai_kursus.php';
              </script>";
    } else {
        // Insert new session
        $sql = "INSERT INTO sesi (sesi_id) VALUES ('$sesi')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Sesi baharu telah berjaya ditambah!');
                    window.location.href = 'senarai_kursus.php?sesi=$sesi';
                  </script>";
        } else {
            echo "<script>
                    alert('Error: " . $conn->error . "');
                    window.location.href = 'senarai_kursus.php';
                  </script>";
        }
    }
}

$conn->close();
?> 