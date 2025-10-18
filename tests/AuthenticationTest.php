<?php
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Users tablosunu oluştur
        $this->pdo->exec("CREATE TABLE Users(id TEXT PRIMARY KEY, full_name TEXT, email TEXT UNIQUE, role TEXT, password TEXT, company_id TEXT, balance REAL)");

        // Test için bir kullanıcı ve hash'lenmiş şifresini oluştur
        $hashedPassword = password_hash('sifre123', PASSWORD_DEFAULT);

        // Kullanıcıyı test veritabanına ekle
        $stmt = $this->pdo->prepare("INSERT INTO Users (id, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['user01', 'test@example.com', $hashedPassword]);
    }

    public function test_dogru_bilgilerle_giris_basarili_olur(): void
    {
        // Eylem: Veritabanından kullanıcıyı e-postasına göre bul
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Doğrulama: Kullanıcı bulundu mu ve şifre doğru mu?
        $this->assertNotFalse($user, "Kullanıcı bulunamadı.");
        $this->assertTrue(password_verify('sifre123', $user['password']), "Şifre doğrulaması başarısız oldu.");
    }

    public function test_yanlis_sifre_ile_giris_basarisiz_olur(): void
    {
        // Eylem
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Doğrulama: Şifrenin yanlış olduğunu doğrula
        $this->assertNotFalse($user);
        $this->assertFalse(password_verify('yanlissifre', $user['password']), "Yanlış şifre beklenmedik bir şekilde doğru kabul edildi.");
    }
}