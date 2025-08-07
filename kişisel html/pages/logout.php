<?php
session_start();

// Tüm session verilerini temizle
session_destroy();

// Ana sayfaya yönlendir
header('Location: index.php');
exit;
?> 