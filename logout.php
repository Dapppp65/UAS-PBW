<!-- Theme Color: Orange (Thai Tea), White, Light Brown -->
<?php
session_start();
session_unset(); // Hapus semua data sesi
session_destroy(); // Hancurkan sesi
header('Location: login.php'); // Arahkan ke halaman login
exit;
?>