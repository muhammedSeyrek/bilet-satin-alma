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

    // Formdaki dropdown menüyü doldurmak için tüm firmaları çekiyoruz
    $stmt_firmalar = $pdo->query("SELECT id, name FROM Bus_Company");
    $firmalar = $stmt_firmalar->fetchAll(PDO::FETCH_ASSOC);

    // Eğer form gönderilmişse (POST metoduyla)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // 1. Formdan gelen verileri al
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $company_id = $_POST['company_id']; // Hangi firmaya atanacağını formdan alıyoruz

        // 2. Parolayı hash'le
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Veritabanına yeni firma adminini ekle
        $sql = "INSERT INTO Users (id, full_name, email, password, role, company_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            uniqid('comadmin_'),
            $full_name,
            $email,
            $hashed_password,
            'company', // Rolü 'company' olarak sabitliyoruz
            $company_id
        ]);

        // 4. İşlem başarılıysa, admin paneline geri yönlendir
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
    <title>Yeni Firma Admini Ekle - Admin Paneli</title>
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
                    <h3>Yeni Firma Admini Ekle</h3>
                </div>
                <div class="card-body">
                    <form action="firma_admin_ekle.php" method="POST">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Geçici Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="company_id" class="form-label">Atanacak Firma</label>
                            <select class="form-select" id="company_id" name="company_id" required>
                                <option value="" selected disabled>Lütfen bir firma seçin...</option>
                                <?php foreach ($firmalar as $firma): ?>
                                    <option value="<?= htmlspecialchars($firma['id']) ?>"><?= htmlspecialchars($firma['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Firma Adminini Kaydet</button>
                        <a href="admin_paneli.php" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>