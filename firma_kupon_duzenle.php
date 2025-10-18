<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company' || !isset($_SESSION['company_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$kupon_id_to_edit = $_GET['id'];
$company_id = $_SESSION['company_id'];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Formdan gelen verilerle UPDATE işlemi...
        $code = $_POST['code'];
        $discount_rate = $_POST['discount_rate'];
        $usage_limit = $_POST['usage_limit'];
        $expire_date = $_POST['expire_date'];
        $db_discount = $discount_rate / 100;

        $sqlUpdate = "UPDATE Coupons SET code = ?, discount = ?, usage_limit = ?, expire_date = ? WHERE id = ? AND company_id = ?";
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute([$code, $db_discount, $usage_limit, $expire_date, $kupon_id_to_edit, $company_id]);

        header("Location: firma_paneli.php");
        exit();
    }

    // Düzenlenecek kuponun bilgilerini çek (güvenlik kontrolü ile)
    $stmt = $pdo->prepare("SELECT * FROM Coupons WHERE id = ? AND company_id = ?");
    $stmt->execute([$kupon_id_to_edit, $company_id]);
    $kupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kupon) {
        header("Location: firma_paneli.php");
        exit();
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="tr">
<head>
    <title>Firma Kuponu Düzenle - Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark"></nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3>Firma Kuponunu Düzenle</h3></div>
                <div class="card-body">
                    <form action="firma_kupon_duzenle.php?id=<?= htmlspecialchars($kupon['id']) ?>" method="POST">
                        <div class="mb-3">
                            <label for="code" class="form-label">Kupon Kodu</label>
                            <input type="text" class="form-control" id="code" name="code" value="<?= htmlspecialchars($kupon['code']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="discount_rate" class="form-label">İndirim Oranı (%)</label>
                            <input type="number" class="form-control" id="discount_rate" name="discount_rate" value="<?= htmlspecialchars($kupon['discount'] * 100) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="usage_limit" class="form-label">Kullanım Limiti</label>
                            <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="<?= htmlspecialchars($kupon['usage_limit']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="expire_date" class="form-label">Son Kullanma Tarihi</label>
                            <input type="date" class="form-control" id="expire_date" name="expire_date" value="<?= htmlspecialchars($kupon['expire_date']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                        <a href="firma_paneli.php" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>