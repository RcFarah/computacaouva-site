<?php
session_start();
session_destroy(); // Destrói todas as sessões
header("Location: login.php"); // Manda de volta pro login
exit;
?>