
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar and Topbar UI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f6fa;
        }

        .sidebar {
            background-color: #ffffff;
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar h2 {
            color: #1a1a1a;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #666;
            padding: 12px 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .sidebar a:hover {
            background-color: #f0f2f5;
            color: #2563eb;
        }

        .sidebar a.active {
            background-color: #e0e7ff;
            color: #2563eb;
            font-weight: 500;
        }

        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            transition: all 0.3s ease;
        }

        .topbar {
            background-color: #ffffff;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        #toggle-btn {
            font-size: 1.2rem;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        #toggle-btn:hover {
            background-color: #f0f2f5;
            color: #2563eb;
        }

        .user-dropdown {
            position: relative;
        }

        .user-name {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            color: #1a1a1a;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .user-name:hover {
            background-color: #f0f2f5;
        }

        .user-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: #ffffff;
            min-width: 180px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-top: 5px;
        }

        .user-dropdown-content a {
            color: #666;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .user-dropdown-content a:hover {
            background-color: #f0f2f5;
            color: #2563eb;
        }

        .user-dropdown:hover .user-dropdown-content {
            display: block;
        }

        /* Toggle behavior */
        .sidebar.active {
            margin-left: -250px;
        }

        .main-content.active {
            margin-left: 0;
            width: 100%;
        }
        .nav-buttons {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            padding: 8px 15px;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-btn:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <h2>Menu</h2>
        <a href="dashboard_pensyarah.php">
            <i class="fas fa-home" style="margin-right: 10px;"></i>Dashboard
        </a>
        <a href="visimisi.php">
            <i class="fas fa-bullseye" style="margin-right: 10px;"></i>Visi dan Misi
        </a>
        <a href="tugas_pensyarah.php">
            <i class="fas fa-tasks" style="margin-right: 10px;"></i>Tugas Pensyarah
        </a>
        <a href="serahan_pertama.php">
            <i class="fas fa-file-alt" style="margin-right: 10px;"></i>Serahan Pertama
        </a>
        <a href="serahan_kedua.php">
            <i class="fas fa-file-alt" style="margin-right: 10px;"></i>Serahan Kedua
        </a>
    </div>

    <div class="main-content" id="mainContent">
    <div class="topbar" id="topbar">
            <button id="toggle-btn" onclick="toggleSidebar()">☰</button>
            <div class="nav-buttons">
                <a href="change_password_pensyarah.php" class="nav-btn">Tetapan Log Masuk</a>
                <a href="logout.php" class="nav-btn">Log Keluar</a>
            </div>
        </div>
        
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
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
