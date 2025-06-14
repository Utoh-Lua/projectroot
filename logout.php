<?php
// logout.php
session_start();

// Hapus semua data session
$_SESSION = [];
session_unset();
session_destroy();

// (Opsional) Kirim flash message via GET param
header("Location: index.php?logout=success");
exit;
?>
