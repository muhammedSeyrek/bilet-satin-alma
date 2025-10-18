<?php
// Sayfa başlığını ayarla ve header'ı çağır
$page_title = "Biletlerim";
require_once 'header.php';

// Güvenlik kontrolü (header.php zaten session_start yapıyor)
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit();
}

// Sadece bu sayfaya özgü PHP kodları
$user_id = $_SESSION['user_id'];
$biletler = [];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Firma adını da çeken güncel SQL sorgusu
    $sql = "SELECT 
                Tickets.id AS bilet_id,
                Trips.departure_city,
                Trips.destination_city,
                Trips.departure_time,
                Tickets.total_price,
                Bus_Company.name AS company_name
            FROM Tickets
            JOIN Trips ON Tickets.trip_id = Trips.id
            JOIN Bus_Company ON Trips.company_id = Bus_Company.id
            WHERE Tickets.user_id = ? AND Tickets.status = 'active'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header("Location: hata.php?mesaj=Veritabani hatasi olustu.");
    exit();
}

// Fonksiyonlar dosyasını dahil et (tarih formatı için)
require_once 'fonksiyonlar.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Satın Aldığım Biletler</h3>
        <a href="index.php" class="btn btn-secondary">Ana Sayfaya Dön</a>
    </div>
    <hr>
    
    <?php if (empty($biletler)): ?>
        <div class="alert alert-info">Henüz satın alınmış biletiniz bulunmamaktadır.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Güzergah</th>
                    <th>Firma</th>
                    <th>Kalkış Zamanı</th>
                    <th>Fiyat</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($biletler as $bilet): ?>
                    <tr>
                        <td><?= htmlspecialchars($bilet['departure_city']) ?> -> <?= htmlspecialchars($bilet['destination_city']) ?></td>
                        <td><?= htmlspecialchars($bilet['company_name']) ?></td>
                        <td><?= format_turkish_date($bilet['departure_time']) // Tarih formatlama fonksiyonunu kullanıyoruz ?></td>
                        <td><?= htmlspecialchars($bilet['total_price']) ?> TL</td>
                        <td>
                            <a href="bilet_iptal.php?bilet_id=<?= htmlspecialchars($bilet['bilet_id']) ?>" class="btn btn-danger btn-sm">İptal Et</a>
                            <a href="bilet_pdf.php?bilet_id=<?= htmlspecialchars($bilet['bilet_id']) ?>" class="btn btn-primary btn-sm" target="_blank">PDF İndir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// Footer'ı çağır
require_once 'footer.php';
?>