<?php
$page_title = "Firma Düzenle";
require_once 'admin_header.php';

// Güvenlik Kontrolü (header'da zaten var ama ID kontrolü için ekliyoruz)
if (!isset($_GET['id'])) {
    header("Location: admin_paneli.php");
    exit();
}

$firma_id_to_edit = $_GET['id'];
$firma = null;

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eğer form gönderilmişse, UPDATE işlemini yap
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $yeni_firma_adi = $_POST['firma_adi'];
        $sqlUpdate = "UPDATE Bus_Company SET name = ? WHERE id = ?";
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute([$yeni_firma_adi, $firma_id_to_edit]);
        header("Location: admin_paneli.php");
        exit();
    }

    // Formu doldurmak için mevcut firma bilgilerini çek
    $stmt = $pdo->prepare("SELECT * FROM Bus_Company WHERE id = ?");
    $stmt->execute([$firma_id_to_edit]);
    $firma = $stmt->fetch(PDO::FETCH_ASSOC);

    // Eğer firma bulunamazsa, admin paneline geri yönlendir
    if (!$firma) {
        header("Location: admin_paneli.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: hata.php?mesaj=Veritabani hatasi olustu.");
    exit();
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Firmayı Düzenle</h3>
                </div>
                <div class="card-body">
                    <form action="firma_duzenle.php?id=<?= htmlspecialchars($firma['id']) ?>" method="POST">
                        <div class="mb-3">
                            <label for="firma_adi" class="form-label">Firma Adı</label>
                            <input type="text" class="form-control" id="firma_adi" name="firma_adi" value="<?= htmlspecialchars($firma['name']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
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