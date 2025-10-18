<?php
$page_title = "Firma Yönetim Paneli";
require_once 'firma_header.php'; // Firma için olan header'ı çağır

$company_id = $_SESSION['company_id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. O firmaya ait tüm seferleri çek
    $stmt_seferler = $pdo->prepare("SELECT * FROM Trips WHERE company_id = ?");
    $stmt_seferler->execute([$company_id]);
    $seferler = $stmt_seferler->fetchAll(PDO::FETCH_ASSOC);

    // 2. O firmaya ait tüm kuponları çek
    $stmt_kuponlar = $pdo->prepare("SELECT * FROM Coupons WHERE company_id = ?");
    $stmt_kuponlar->execute([$company_id]);
    $kuponlar = $stmt_kuponlar->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header("Location: hata.php?mesaj=Veritabani hatasi olustu.");
    exit();
}

require_once 'fonksiyonlar.php'; // Tarih formatlama fonksiyonu için
?>

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
                <tr><td colspan="5" class="text-center">Henüz firmanıza ait bir sefer bulunmamaktadır.</td></tr>
            <?php else: ?>
                <?php foreach ($seferler as $sefer): ?>
                    <tr>
                        <td><?= htmlspecialchars($sefer['departure_city']) ?> -> <?= htmlspecialchars($sefer['destination_city']) ?></td>
                        <td><?= format_turkish_date($sefer['departure_time']) ?></td>
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
                <tr><td colspan="5" class="text-center">Firmanıza ait kupon bulunmamaktadır.</td></tr>
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
</div>

<?php
require_once 'footer.php';
?>