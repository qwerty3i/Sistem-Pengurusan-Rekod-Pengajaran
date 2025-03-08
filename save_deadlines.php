<?php
// Database connection
include('database.php');

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $serahan_pertama_date = $_POST['serahan_pertama_date'];
    $serahan_kedua_date = $_POST['serahan_kedua_date'];

    // Function to check if a deadline exists
    function checkDeadlineExists($conn, $serahan_no) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM deadlines WHERE serahan_no = ?");
        $stmt->bind_param("i", $serahan_no);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    // Function to insert a new deadline
    function insertDeadline($conn, $serahan_no, $deadline_date) {
        $stmt = $conn->prepare("INSERT INTO deadlines (serahan_no, deadline_date) VALUES (?, ?)");
        $stmt->bind_param("is", $serahan_no, $deadline_date);
        $stmt->execute();
        $stmt->close();
    }

    // Function to update an existing deadline
    function updateDeadline($conn, $serahan_no, $deadline_date) {
        $stmt = $conn->prepare("UPDATE deadlines SET deadline_date = ? WHERE serahan_no = ?");
        $stmt->bind_param("si", $deadline_date, $serahan_no);
        $stmt->execute();
        $stmt->close();
    }

    // Check if each deadline exists, then insert or update accordingly
    if (checkDeadlineExists($conn, 1)) {
        updateDeadline($conn, 1, $serahan_pertama_date);
    } else {
        insertDeadline($conn, 1, $serahan_pertama_date);
    }

    if (checkDeadlineExists($conn, 2)) {
        updateDeadline($conn, 2, $serahan_kedua_date);
    } else {
        insertDeadline($conn, 2, $serahan_kedua_date);
    }

    $conn->close();

    // Display JavaScript alert and redirect
    echo "<script>
            alert('Deadlines updated successfully!');
            window.location.href = 'dashboard_admin.php';
          </script>";
    exit();
}
?>
