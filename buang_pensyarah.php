<?php
include('database.php');

// Check if 'id' is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the DELETE query
    $sql = "DELETE FROM pensyarah WHERE id = $id";

    // Execute the query and check for success
    if ($conn->query($sql) === TRUE) {
        echo "Record deleted successfully";
        header("Location: senarai_pensyarah.php"); // Redirect to the main list page
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "Invalid ID!";
}

// Close the database connection
$conn->close();
?>
