<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company') {
    header("Location: index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="tr" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column h-100">
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="firma_paneli.php">Firma Paneli</a>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#">Hoş Geldin, <?= htmlspecialchars($_SESSION['user_fullname']); ?></a></li>
                <li class="nav-item"><a class="nav-link" href="cikis.php">Çıkış Yap</a></li>
            </ul>
        </div>
    </nav>
</header>
<main class="flex-shrink-0">