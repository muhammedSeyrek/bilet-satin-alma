<?php
$page_title = "Kayıt Ol";
require_once 'header.php';

// Eğer kullanıcı zaten giriş yapmışsa
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = ''; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    try {
        $pdo = new PDO('sqlite:purchasing_tickets.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error_message = "Bu e-posta adresi zaten kullanımda.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Users (id, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([uniqid(), $full_name, $email, $hashed_password, 'user']);
            
            $_SESSION['flash_message'] = "Kaydınız başarıyla oluşturuldu. Şimdi giriş yapabilirsiniz.";

            header("Location: giris.php");
            exit();
        }
    } catch (PDOException $e) {
        $error_message = "Veritabanı hatası: " . $e->getMessage();
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header"><h3>Kayıt Ol</h3></div>
                <div class="card-body">
                    <form action="kayit.php" method="POST">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="fullname" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>