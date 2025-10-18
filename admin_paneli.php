<?php
session_start();

// Güvenlik Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Veritabanındaki tüm firmaları çek
    $stmt_firmalar = $pdo->query("SELECT * FROM Bus_Company");
    $firmalar = $stmt_firmalar->fetchAll(PDO::FETCH_ASSOC);

    // 2. Veritabanındaki tüm Firma Adminlerini çek
    $stmt_admins = $pdo->query("SELECT Users.id, Users.full_name, Users.email, Bus_Company.name AS company_name
                                FROM Users 
                                JOIN Bus_Company ON Users.company_id = Bus_Company.id
                                WHERE Users.role = 'company'");
    $firma_adminleri = $stmt_admins->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Veritabanındaki tüm kuponları çek
    $stmt_kuponlar = $pdo->query("SELECT * FROM Coupons");
    $kuponlar = $stmt_kuponlar->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Paneli</title>
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
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Firma Yönetimi</h3>
        <a href="firma_ekle.php" class="btn btn-primary">Yeni Firma Ekle</a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Firma ID</th>
                <th>Firma Adı</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($firmalar)): ?>
                <tr><td colspan="3" class="text-center">Sistemde kayıtlı firma bulunmamaktadır.</td></tr>
            <?php else: ?>
                <?php foreach ($firmalar as $firma): ?>
                    <tr>
                        <td><?= htmlspecialchars($firma['id']) ?></td>
                        <td><?= htmlspecialchars($firma['name']) ?></td>
                        <td>
                            <a href="firma_duzenle.php?id=<?= htmlspecialchars($firma['id']) ?>" class="btn btn-warning btn-sm">Düzenle</a>
                            <a href="firma_sil.php?id=<?= htmlspecialchars($firma['id']) ?>" class="btn btn-danger btn-sm">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <hr class="my-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Firma Admin Yönetimi</h3>
        <a href="firma_admin_ekle.php" class="btn btn-primary">Yeni Firma Admini Ekle</a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Ad Soyad</th>
                <th>E-posta</th>
                <th>Atandığı Firma</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($firma_adminleri)): ?>
                <tr><td colspan="4" class="text-center">Sistemde kayıtlı firma admini bulunmamaktadır.</td></tr>
            <?php else: ?>
                <?php foreach ($firma_adminleri as $admin): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['full_name']) ?></td>
                        <td><?= htmlspecialchars($admin['email']) ?></td>
                        <td><?= htmlspecialchars($admin['company_name']) ?></td>
                        <td>
                            <a href="firma_admin_duzenle.php?id=<?= htmlspecialchars($admin['id']) ?>" class="btn btn-warning btn-sm">Düzenle</a>
                            <a href="firma_admin_sil.php?id=<?= htmlspecialchars($admin['id']) ?>" class="btn btn-danger btn-sm">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <hr class="my-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Kupon Yönetimi</h3>
        <a href="kupon_ekle.php" class="btn btn-primary">Yeni Kupon Ekle</a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Kod</th>
                <th>İndirim Oranı (%)</th>
                <th>Limit</th>
                <th>Son Kullanma</th>
                <th>Firma (Genel ise boş)</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kuponlar)): ?>
                <tr>
                    <td colspan="6" class="text-center">Sistemde kayıtlı kupon bulunmamaktadır.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($kuponlar as $kupon): ?>
                    <tr>
                        <td><?= htmlspecialchars($kupon['code']) ?></td>
                        <td><?= htmlspecialchars($kupon['discount'] * 100) ?>%</td>
                        <td><?= htmlspecialchars($kupon['usage_limit']) ?></td>
                        <td><?= htmlspecialchars($kupon['expire_date']) ?></td>
                        <td><?= htmlspecialchars($kupon['company_id'] ?? 'Tüm Firmalar') ?></td>
                        <td>
                            <a href="kupon_duzenle.php?id=<?= htmlspecialchars($kupon['id']) ?>" class="btn btn-warning btn-sm">Düzenle</a>
                            <a href="kupon_sil.php?id=<?= htmlspecialchars($kupon['id']) ?>" class="btn btn-danger btn-sm">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>