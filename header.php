<?php
// Oturumu her zaman en başta başlat
session_start();
// Hata raporlamayı aç
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Aktif sayfanın adını al (örn: index.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="tr" class="h-100"> <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="d-flex flex-column h-100"> <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Bilet Platformu</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Hoş Geldin, <?= htmlspecialchars($_SESSION['user_fullname']); ?></a>
                        </li>
                        <?php if ($_SESSION['user_role'] === 'user'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($current_page == 'biletlerim.php') ? 'active' : '' ?>" href="biletlerim.php">Biletlerim</a>
                            </li>
                        <?php elseif ($_SESSION['user_role'] === 'company'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($current_page == 'firma_paneli.php') ? 'active' : '' ?>" href="firma_paneli.php">Firma Paneli</a>
                            </li>
                        <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($current_page == 'admin_paneli.php') ? 'active' : '' ?>" href="admin_paneli.php">Admin Paneli</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cikis.php">Çıkış Yap</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'giris.php') ? 'active' : '' ?>" href="giris.php">Giriş Yap</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'kayit.php') ? 'active' : '' ?>" href="kayit.php">Kayıt Ol</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="flex-shrink-0">