<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Deadlines</title>
    <style>
        /* Basic Styling */
        .content {
            margin-left: 20px;
            margin-right: 20px;
            margin-top: 50px;
            margin-bottom: 30px;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
        }

        h1 {
            color: #4a90e2;
            margin-bottom: 20px;
        }

        .deadline-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        input[type="date"] {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        .btn-submit {
            padding: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #4a90e2;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #357abd;
        }

        .btn-cancel {
            padding: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #dc3545;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .btn-cancel:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<?php include('sidebar.php'); ?>

    <div class="content">
        <h1>Set Deadlines</h1>
        <?php
        // Include database connection
        include('database.php');

        // Initialize variables for deadlines
        $serahan_pertama_date = '';
        $serahan_kedua_date = '';

        // Query to get deadlines for Serahan Pertama and Kedua
        $query = "SELECT serahan_no, deadline_date FROM deadlines WHERE serahan_no IN (1, 2)";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            // Fetch dates from database
            while ($row = $result->fetch_assoc()) {
                if ($row['serahan_no'] == 1) {
                    $serahan_pertama_date = $row['deadline_date'];
                } elseif ($row['serahan_no'] == 2) {
                    $serahan_kedua_date = $row['deadline_date'];
                }
            }
        }
        $conn->close();
        ?>
        
        <form action="save_deadlines.php" method="POST" class="deadline-form">
            
            <!-- Serahan Pertama Deadline -->
            <div class="form-group">
                <label for="serahan_pertama_date">Deadline for Serahan Pertama</label>
                <input type="date" id="serahan_pertama_date" name="serahan_pertama_date" value="<?php echo htmlspecialchars($serahan_pertama_date); ?>" required>
            </div>

            <!-- Serahan Kedua Deadline -->
            <div class="form-group">
                <label for="serahan_kedua_date">Deadline for Serahan Kedua</label>
                <input type="date" id="serahan_kedua_date" name="serahan_kedua_date" value="<?php echo htmlspecialchars($serahan_kedua_date); ?>" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit">Save Deadlines</button>
            <button type="button" class="btn-cancel" onclick="window.location.href='index.html'">Cancel</button>
        </form>
    </div>

</body>
</html>
