<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serahan Pertama - Document Management</title>
    <style>
        /* Basic Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #4a90e2;
            text-align: center;
            margin-bottom: 20px;
        }

        .back-button {
            background-color: #4a90e2;
            color: #fff;
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s;
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #357ABD;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4a90e2;
            color: white;
        }

        td .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons .upload-button, .action-buttons .delete-button {
            padding: 8px 12px;
            font-size: 13px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            transition: background 0.3s;
        }

        .upload-button {
            background-color: #28a745;
            color: #fff;
        }

        .upload-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .delete-button {
            background-color: #dc3545;
            color: #fff;
        }

        .upload-button:hover:not(:disabled) {
            background-color: #218838;
        }

        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.html" class="back-button">Back to Senarai Semak</a>
        <h1>Serahan Pertama - Document Management</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Document Name</th>
                    <th>Status</th>
                    <th>Comment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ringkasan Maklumat Kursus</td>
                    <td>Checked</td>
                    <td>Looks good</td>
                    <td class="action-buttons">
                        <button class="upload-button" onclick="uploadDocument()">Upload</button>
                        <button class="delete-button" onclick="deleteDocument()">Delete</button>
                    </td>
                </tr>
                <tr>
                    <td>Perincian Kursus Mingguan</td>
                    <td>Not Checked</td>
                    <td>Missing information</td>
                    <td class="action-buttons">
                        <button class="upload-button" onclick="uploadDocument()">Upload</button>
                        <button class="delete-button" onclick="deleteDocument()">Delete</button>
                    </td>
                </tr>
                <tr>
                    <td>Borang Item Penilaian</td>
                    <td>Checked</td>
                    <td>No issues</td>
                    <td class="action-buttons">
                        <button class="upload-button" onclick="uploadDocument()">Upload</button>
                        <button class="delete-button" onclick="deleteDocument()">Delete</button>
                    </td>
                </tr>
                <!-- Add more rows as needed for each document -->
            </tbody>
        </table>
    </div>

    <script>
        // Function to handle upload action (mock example)
        function uploadDocument() {
            alert("Upload document functionality triggered!");
        }

        // Function to handle delete action (mock example)
        function deleteDocument() {
            if (confirm("Are you sure you want to delete this document?")) {
                alert("Document deleted!");
            }
        }

        // Lock upload buttons after the deadline
        const deadlinePassed = true; // Example condition; replace with actual logic
        if (deadlinePassed) {
            document.querySelectorAll(".upload-button").forEach(button => {
                button.disabled = true;
            });
        }
    </script>

</body>
</html>
