<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'];
    $discount_rate = $_POST['discount_rate'];
    $usage_limit = $_POST['usage_limit'];
    $expire_date = $_POST['expire_date'];
    $db_discount = $discount_rate / 100;
    try {
        $pdo = new PDO('sqlite:purchasing_tickets.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Sadece bu satırı düzeltiyoruz: discount_rate -> discount
        $sql = "INSERT INTO Coupons (id, code, discount, usage_limit, expire_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([uniqid('coupon_'), $code, $db_discount, $usage_limit, $expire_date]);
        header("Location: admin_paneli.php");
        exit();
    } catch (PDOException $e) {
        die("Veritabanı hatası: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <title>Yeni Kupon Ekle - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    </nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Yeni Kupon Ekle (Tüm Firmalar İçin)</h3>
                </div>
                <div class="card-body">
                    <form action="kupon_ekle.php" method="POST">
                        <div class="mb-3">
                            <label for="code" class="form-label">Kupon Kodu</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="discount_rate" class="form-label">İndirim Oranı (%)</label>
                            <input type="number" class="form-control" id="discount_rate" name="discount_rate" min="1" max="100" placeholder="Örn: 20" required>
                        </div>
                        <div class="mb-3">
                            <label for="usage_limit" class="form-label">Kullanım Limiti</label>
                            <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="expire_date" class="form-label">Son Kullanma Tarihi</label>
                            <input type="date" class="form-control" id="expire_date" name="expire_date" required>
                        </div>
                        <button type="submit" class="btn btn-success">Kuponu Kaydet</button>
                        <a href="admin_paneli.php" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>