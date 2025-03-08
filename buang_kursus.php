<?php
include('database.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get sesi before deleting
    $sesi_query = "SELECT sesi FROM kursus WHERE id = $id";
    $sesi_result = $conn->query($sesi_query);
    $sesi_row = $sesi_result->fetch_assoc();
    $sesi = $sesi_row['sesi'];

    // Delete related documents first
    $delete_docs = "DELETE FROM dokumen WHERE kursus_id = $id";
    if ($conn->query($delete_docs) === TRUE) {
        // Then delete the course
        $delete_course = "DELETE FROM kursus WHERE id = $id";
        if ($conn->query($delete_course) === TRUE) {
            echo "<script>
                    alert('Kursus telah berjaya dibuang!');
                    window.location.href = 'senarai_kursus.php?sesi=$sesi';
                  </script>";
        } else {
            echo "<script>
                    alert('Error deleting course: " . $conn->error . "');
                    window.location.href = 'senarai_kursus.php?sesi=$sesi';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Error deleting documents: " . $conn->error . "');
                window.location.href = 'senarai_kursus.php?sesi=$sesi';
              </script>";
    }
} else {
    header("Location: senarai_kursus.php");
}

$conn->close();
?> 