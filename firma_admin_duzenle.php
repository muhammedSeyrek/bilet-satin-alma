<?php
session_start();
// Güvenlik Kontrolleri
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_id_to_edit = $_GET['id'];
$admin_to_edit = null;
$firmalar = [];

try {
    $pdo = new PDO('sqlite:purchasing_tickets.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- UPDATE İŞLEMİ ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $company_id = $_POST['company_id'];

        $sqlUpdate = "UPDATE Users SET full_name = ?, email = ?, company_id = ? WHERE id = ? AND role = 'company'";
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute([$full_name, $email, $company_id, $admin_id_to_edit]);

        header("Location: admin_paneli.php");
        exit();
    }

    // DÜZENLENECEK FİRMA ADMİNİNİN BİLGİLERİNİ ÇEK
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ? AND role = 'company'");
    $stmt->execute([$admin_id_to_edit]);
    $admin_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);

    // DROPDOWN İÇİN TÜM FİRMALARI ÇEK
    $stmt_firmalar = $pdo->query("SELECT id, name FROM Bus_Company");
    $firmalar = $stmt_firmalar->fetchAll(PDO::FETCH_ASSOC);

    if (!$admin_to_edit) {
        header("Location: admin_paneli.php");
        exit();
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="tr">
<head>
    <title>Firma Admini Düzenle - Admin Paneli</title>
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
                    <h3>Firma Admini Düzenle</h3>
                </div>
                <div class="card-body">
                    <form action="firma_admin_duzenle.php?id=<?= htmlspecialchars($admin_to_edit['id']) ?>" method="POST">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($admin_to_edit['full_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($admin_to_edit['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="company_id" class="form-label">Atanacak Firma</label>
                            <select class="form-select" id="company_id" name="company_id" required>
                                <?php foreach ($firmalar as $firma): ?>
                                    <option value="<?= htmlspecialchars($firma['id']) ?>" <?= ($firma['id'] == $admin_to_edit['company_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($firma['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                        <a href="admin_paneli.php" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>