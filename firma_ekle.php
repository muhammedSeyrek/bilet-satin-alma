<?php
session_start();

require_once 'fonksiyonlar.php';

// Güvenlik Kontrolü: Sadece 'admin' rolündekiler erişebilir.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Eğer form gönderilmişse (POST metoduyla)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Formdan gelen firma adını al
    $firma_adi = $_POST['firma_adi'];
    $firma_id = slugify($firma_adi);

    try {
        $pdo = new PDO('sqlite:purchasing_tickets.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. Veritabanına yeni firmayı ekle
        $sql = "INSERT INTO Bus_Company (id, name) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$firma_id, $firma_adi]);

        header("Location: admin_paneli.php");
        exit();

    } catch (PDOException $e) {
        // Bir hata olursa (örn: aynı ID veya isimde firma varsa), hatayı göster
        die("Veritabanı hatası: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yeni Firma Ekle - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container">
        <a class="navbar-brand" href="admin_paneli.php">Admin Paneli</a>
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="#">Hoş Geldin, <?= htmlspecialchars($_SESSION['user_fullname']); ?></a></li>
            <li class="nav-item"><a class="nav-link" href="cikis.php">Çıkış Yap</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Yeni Firma Ekle</h3>
                </div>
                <div class="card-body">
                    <form action="firma_ekle.php" method="POST">
                        <div class="mb-3">
                            <label for="firma_adi" class="form-label">Firma Adı</label>
                            <input type="text" class="form-control" id="firma_adi" name="firma_adi" required>
                        </div>
                        <button type="submit" class="btn btn-success">Firmayı Kaydet</button>
                        <a href="admin_paneli.php" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>