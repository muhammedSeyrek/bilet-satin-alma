<?php
use PHPUnit\Framework\TestCase;

class RegistrationTest extends TestCase
{
    private $pdo; // Test veritabanı bağlantısını bu değişkende tutacağız

    /**
     * Bu fonksiyon her bir test metodundan önce otomatik olarak çalıştırılır.
     * Görevi, her test için temiz bir başlangıç ortamı hazırlamaktır.
     */
    protected function setUp(): void
    {
        // 1. Hafıza-içi geçici bir SQLite veritabanı oluştur
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. Gerçek veritabanımızdaki Users tablosunun aynısını bu geçici veritabanında oluştur
        // Not: Gerçek şemamızdaki FOREIGN KEY veya CHECK kısıtlamalarını basitlik için çıkardık.
        $this->pdo->exec("CREATE TABLE Users(
            id TEXT PRIMARY KEY,
            full_name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            role TEXT NOT NULL,
            password TEXT NOT NULL,
            company_id TEXT,
            balance REAL DEFAULT 0
        )");
    }

    /**
     * Bu bizim asıl testimiz.
     * Yeni bir kullanıcının başarıyla kayıt olup olmadığını test eder.
     */
    public function test_yeni_kullanici_basariyla_kayit_olabilir(): void
    {
        // --- 1. Hazırlık (Arrange) ---
        // kayit.php'nin ihtiyaç duyduğu POST verilerini sahte olarak oluşturuyoruz.
        $postVerisi = [
            'full_name' => 'Ahmet Yılmaz',
            'email' => 'ahmet@yilmaz.com',
            'password' => 'güvenli123'
        ];

        // --- 2. Eylem (Act) ---
        // kayit.php'nin içindeki kayıt mantığını, hazırladığımız test veritabanı ve
        // sahte POST verileri ile burada çalıştırıyoruz.
        // (Not: header() yönlendirmesi testi durduracağı için, kayit.php'nin mantığını buraya taşıdık)
        
        $pdo = $this->pdo; // Test veritabanımızı kullanalım
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE email = ?");
        $stmt->execute([$postVerisi['email']]);
        if ($stmt->fetchColumn() == 0) {
            $hashed_password = password_hash($postVerisi['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO Users (id, full_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql);
            $stmt_insert->execute([uniqid(), $postVerisi['full_name'], $postVerisi['email'], $hashed_password, 'user']);
        }
        
        // --- 3. Doğrulama (Assert) ---
        // Şimdi veritabanına gidip kaydın doğru yapılıp yapılmadığını kontrol ediyoruz.
        $stmt_check = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt_check->execute(['ahmet@yilmaz.com']);
        $user = $stmt_check->fetch(PDO::FETCH_ASSOC);

        // İddia 1: Veritabanında bu e-postaya sahip bir kullanıcı bulundu mu?
        $this->assertNotFalse($user, "Kullanıcı veritabanında bulunamadı.");

        // İddia 2: Bulunan kullanıcının adı, bizim gönderdiğimiz ad ile aynı mı?
        $this->assertEquals('Ahmet Yılmaz', $user['full_name']);

        // İddia 3: Kaydedilen şifre, bizim gönderdiğimiz şifrenin hash'lenmiş hali mi?
        $this->assertTrue(password_verify('güvenli123', $user['password']));
    }
}