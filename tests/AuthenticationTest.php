<?php
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    private $pdo;
    private $testUser;
    private $hashedPassword;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Users tablosunu oluştur
        $this->pdo->exec("CREATE TABLE Users(id TEXT PRIMARY KEY, full_name TEXT, email TEXT UNIQUE, role TEXT, password TEXT, company_id TEXT, balance REAL)");

        // Test için bir kullanıcı ve hash'lenmiş şifresini oluştur
        $this->hashedPassword = password_hash('sifre123', PASSWORD_DEFAULT);
        $this->testUser = [
            'id' => 'user01',
            'full_name' => 'Test Kullanici',
            'email' => 'test@example.com',
            'role' => 'user',
            'password' => $this->hashedPassword,
            'company_id' => null,
            'balance' => 800
        ];

        // Kullanıcıyı test veritabanına ekle
        $stmt = $this->pdo->prepare("INSERT INTO Users VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array_values($this->testUser));
    }

    public function test_dogru_bilgilerle_giris_basarili_olur(): void
    {
        // Eylem: giris.php mantığını çalıştır
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Doğrulama: Kullanıcı bulundu mu ve şifre doğru mu?
        $this->assertNotFalse($user);
        $this->assertTrue(password_verify('sifre123', $user['password']));
    }

    public function test_yanlis_sifre_ile_giris_basarisiz_olur(): void
    {
        // Eylem
        $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Doğrulama
        $this->assertNotFalse($user);
        $this->assertFalse(password_verify('yanlissifre', $user['password']));
    }
}