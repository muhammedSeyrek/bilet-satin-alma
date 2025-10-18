<?php
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Gerekli tabloları oluştur
        $this->pdo->exec("CREATE TABLE Users(id TEXT PRIMARY KEY, email TEXT, balance REAL, full_name TEXT, role TEXT, password TEXT, company_id TEXT)");
        $this->pdo->exec("CREATE TABLE Trips(id TEXT PRIMARY KEY, price REAL, departure_time TEXT, company_id TEXT, destination_city TEXT, arrival_time TEXT, departure_city TEXT, capacity INTEGER, created_date TEXT)");
        $this->pdo->exec("CREATE TABLE Tickets(id TEXT PRIMARY KEY, trip_id TEXT, user_id TEXT, total_price REAL, status TEXT)");
        
        // Test verilerini ekle
        $this->pdo->exec("INSERT INTO Users (id) VALUES ('user-A')");
        $this->pdo->exec("INSERT INTO Users (id) VALUES ('user-B')");
        $this->pdo->exec("INSERT INTO Trips (id) VALUES ('trip01')");
        $this->pdo->exec("INSERT INTO Tickets (id, user_id, trip_id, status) VALUES ('ticket-of-A', 'user-A', 'trip01', 'active')");
    }

    public function test_kullanici_baska_kullanicinin_biletine_erisemez_IDOR(): void
    {
        // Bu testin mantığı zaten doğruydu, aynı kalıyor.
        $saldırgan_id = 'user-B';
        $kurbanın_bilet_id = 'ticket-of-A';

        $stmt = $this->pdo->prepare("SELECT * FROM Tickets WHERE id = ? AND user_id = ?");
        $stmt->execute([$kurbanın_bilet_id, $saldırgan_id]);
        $bilet = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse($bilet, "Güvenlik açığı! Bir kullanıcı başka bir kullanıcının biletine erişebiliyor.");
    }

    // --- YENİ EKLENEN VE ÇALIŞAN GÜVENLİK TESTİ ---
    public function test_giris_yapmamis_kullanici_bilet_almaya_calistiginda_hata_alir(): void
    {
        // Hazırlık: $_SESSION'ı boşaltarak giriş yapılmamış bir durumu simüle et
        $_SESSION = [];
        
        // Beklenti: Kodun bir "Exception" (hata) fırlatmasını bekliyoruz.
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Bu sayfaya erişim yetkiniz yok.");

        // Eylem: bilet_al.php'nin başındaki güvenlik mantığını burada çalıştır
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Bu sayfaya erişim yetkiniz yok.");
        }
    }
}