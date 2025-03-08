<?php
session_start();

// Set header untuk mencegah cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// Simpan role untuk mesej
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Kosongkan semua pembolehubah sesi
$_SESSION = array();

// Hapuskan cookie sesi
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Musnahkan sesi
session_destroy();

// Mesej berdasarkan role
$message = '';
switch($role) {
    case 'admin':
        $message = 'Admin telah berjaya log keluar.';
        break;
    case 'ketua bahagian':
        $message = 'Ketua Bahagian telah berjaya log keluar.';
        break;
    case 'pensyarah':
        $message = 'Pensyarah telah berjaya log keluar.';
        break;
    default:
        $message = 'Anda telah berjaya log keluar.';
}

// Redirect terus ke login page dengan JavaScript
echo "<script>
        alert('$message');
        window.location.replace('login.php');
      </script>";

// Redirect backup menggunakan PHP jika JavaScript gagal
header("Location: login.php");
exit();
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=login.php">
    <script type="text/javascript">
        window.location.replace('login.php');
    </script>
</head>
<body>
    <noscript>
        <meta http-equiv="refresh" content="0;url=login.php">
    </noscript>
    
    Jika anda tidak dialihkan secara automatik, sila <a href="login.php">klik di sini</a>.
</body>
</html>
