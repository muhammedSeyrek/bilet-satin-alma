<?php
// Sayfa başlığını ayarla ve admin header'ını çağır
$page_title = "Yeni Firma Ekle";
require_once 'admin_header.php';
require_once 'fonksiyonlar.php'; // slugify fonksiyonu için

// Eğer form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firma_adi = $_POST['firma_adi'];
    $firma_id = slugify($firma_adi);

    try {
        $pdo = new PDO('sqlite:purchasing_tickets.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "INSERT INTO Bus_Company (id, name) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$firma_id, $firma_adi]);

        header("Location: admin_paneli.php");
        exit();
    } catch (PDOException $e) {
        // die("Veritabanı hatası: " . $e->getMessage()); // Bu satırı artık kullanmıyoruz
        header("Location: hata.php?mesaj=Firma eklenirken bir sorun olustu. Ayni isimde bir firma zaten var olabilir.");
        exit();
    }
}
?>

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

<?php
// Ortak footer'ı çağır
require_once 'footer.php';
?>