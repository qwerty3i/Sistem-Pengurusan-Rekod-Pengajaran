<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar and Topbar UI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            overflow: hidden;
            transition: margin-left 0.3s;
        }

        .sidebar {
            background-color: #2c3e50;
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            color: #fff;
            transition: margin-left 0.3s;
        }

        .sidebar h2 {
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #fff;
            padding: 10px 0;
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
            border-radius: 4px;
        }

        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            transition: margin-left 0.3s, width 0.3s;
        }

        .topbar {
            background-color: #3498db;
            padding: 15px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: margin-left 0.3s;
        }

        .topbar button {
            font-size: 24px;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
        }

        .content {
            padding: 20px;
        }

        /* Toggle behavior */
        .sidebar.active {
            margin-left: -250px;
        }

        .main-content.active {
            margin-left: 0;
            width: 100%;
        }

        .topbar.active {
            margin-left: 0;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <h2>Menu</h2>
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="topbar" id="topbar">
            <button id="toggle-btn" onclick="toggleSidebar()">☰</button>
            <h1>Dashboard</h1>
        </div>
        <div class="content">
            <h2>Welcome to the Dashboard</h2>
            <p>This is your content area. Add anything you want here.</p>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const topbar = document.getElementById('topbar');

            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
            topbar.classList.toggle('active');
        }
    </script>
</body>
</html>
