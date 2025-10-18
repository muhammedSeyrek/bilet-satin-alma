<?php
session_start();

// Güvenlik Kontrolü
// 'company_id'nin de session'da olduğundan emin olalım.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company' || !isset($_SESSION['company_id'])) {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Oturumdan Firma Admin'in bilgilerini alalım
$admin_fullname = $_SESSION['user_fullname'];
$company_id = $_SESSION['company_id']; // Artık doğrudan session'dan geliyor!

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM Trips WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $stmt_kuponlar = $pdo->prepare("SELECT * FROM Coupons WHERE company_id = ?");
    $stmt_kuponlar->execute([$company_id]);
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
    <title>Firma Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Firma Paneli</a>
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="#">Hoş Geldin, <?= htmlspecialchars($admin_fullname); ?></a></li>
            <li class="nav-item"><a class="nav-link" href="cikis.php">Çıkış Yap</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Sefer Yönetimi</h3>
        <a href="sefer_ekle.php" class="btn btn-primary">Yeni Sefer Ekle</a>
    </div>
    
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Güzergah</th>
                <th>Kalkış Zamanı</th>
                <th>Fiyat</th>
                <th>Kapasite</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($seferler)): ?>
                <tr>
                    <td colspan="5" class="text-center">Henüz firmanıza ait bir sefer bulunmamaktadır.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($seferler as $sefer): ?>
                    <tr>
                        <td><?= htmlspecialchars($sefer['departure_city']) ?> -> <?= htmlspecialchars($sefer['destination_city']) ?></td>
                        <td><?= htmlspecialchars($sefer['departure_time']) ?></td>
                        <td><?= htmlspecialchars($sefer['price']) ?> TL</td>
                        <td><?= htmlspecialchars($sefer['capacity']) ?></td>
                        <td>
                            <a href="sefer_duzenle.php?id=<?= $sefer['id'] ?>" class="btn btn-warning btn-sm">Düzenle</a>
                            <a href="sefer_sil.php?id=<?= $sefer['id'] ?>" class="btn btn-danger btn-sm">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </table>

    <hr class="my-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Kupon Yönetimi</h3>
        <a href="firma_kupon_ekle.php" class="btn btn-primary">Yeni Kupon Ekle</a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Kod</th>
                <th>İndirim Oranı (%)</th>
                <th>Limit</th>
                <th>Son Kullanma</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kuponlar)): ?>
                <tr>
                    <td colspan="5" class="text-center">Firmanıza ait kupon bulunmamaktadır.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($kuponlar as $kupon): ?>
                    <tr>
                        <td><?= htmlspecialchars($kupon['code']) ?></td>
                        <td><?= htmlspecialchars($kupon['discount'] * 100) ?>%</td>
                        <td><?= htmlspecialchars($kupon['usage_limit']) ?></td>
                        <td><?= htmlspecialchars($kupon['expire_date']) ?></td>
                        <td>
                            <a href="firma_kupon_duzenle.php?id=<?= htmlspecialchars($kupon['id']) ?>" class="btn btn-warning btn-sm">Düzenle</a>
                            <a href="firma_kupon_sil.php?id=<?= htmlspecialchars($kupon['id']) ?>" class="btn btn-danger btn-sm">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div> ```

Bu iki eklemeyi yaptığında, mevcut kodunun üzerine kupon yönetimi bölümünü doğru bir şekilde inşa etmiş olacaksın. Tekrar kusura bakma, bu şekilde daha anlaşılır olduğunu umuyorum.

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>