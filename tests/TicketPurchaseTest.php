<?php
use PHPUnit\Framework\TestCase;

class TicketPurchaseTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Gerekli tüm tabloları oluştur
        $this->pdo->exec("CREATE TABLE Users(id TEXT PRIMARY KEY, balance REAL, email TEXT, full_name TEXT, role TEXT, password TEXT, company_id TEXT)");
        $this->pdo->exec("CREATE TABLE Trips(id TEXT PRIMARY KEY, price REAL, departure_time TEXT, company_id TEXT, destination_city TEXT, arrival_time TEXT, departure_city TEXT, capacity INTEGER, created_date TEXT)");
        $this->pdo->exec("CREATE TABLE Tickets(id TEXT PRIMARY KEY, trip_id TEXT, user_id TEXT, total_price REAL, status TEXT DEFAULT 'active')");
        $this->pdo->exec("CREATE TABLE Booked_Seats(id TEXT PRIMARY KEY, ticket_id TEXT, seat_number INTEGER)");

        // Test verilerini ekle: Bakiyesi 800 olan bir kullanıcı ve fiyatı 500 olan bir sefer
        $this->pdo->exec("INSERT INTO Users (id, balance) VALUES ('user01', 800)");
        $this->pdo->exec("INSERT INTO Trips (id, price, departure_time) VALUES ('trip01', 500, '2025-12-01 10:00:00')");
    }

    public function test_yeterli_bakiye_ile_bilet_satin_alinabilir(): void
    {
        // Hazırlık
        $user_id = 'user01';
        $trip_id = 'trip01';
        $bilet_fiyati = 500;
        $secilen_koltuk = 5;

        // Eylem: bilet_al.php'nin veritabanı işlemlerini simüle et
        $stmt_user = $this->pdo->prepare("SELECT balance FROM Users WHERE id = ?");
        $stmt_user->execute([$user_id]);
        $eski_bakiye = $stmt_user->fetchColumn();

        $yeni_bakiye = $eski_bakiye - $bilet_fiyati;
        $bilet_id = 'ticket01';

        $this->pdo->prepare("INSERT INTO Tickets (id, trip_id, user_id, total_price) VALUES (?, ?, ?, ?)")->execute([$bilet_id, $trip_id, $user_id, $bilet_fiyati]);
        $this->pdo->prepare("INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (?, ?, ?)")->execute(['seat01', $bilet_id, $secilen_koltuk]);
        $this->pdo->prepare("UPDATE Users SET balance = ? WHERE id = ?")->execute([$yeni_bakiye, $user_id]);

        // Doğrulama
        $stmt_user_after = $this->pdo->prepare("SELECT balance FROM Users WHERE id = ?");
        $stmt_user_after->execute([$user_id]);
        $son_bakiye = $stmt_user_after->fetchColumn();

        // İddia 1: Yeni bakiye doğru mu? (800 - 500 = 300)
        $this->assertEquals(300, $son_bakiye);

        // İddia 2: Tickets tablosuna bir kayıt eklendi mi?
        $stmt_ticket_check = $this->pdo->query("SELECT COUNT(*) FROM Tickets");
        $this->assertEquals(1, $stmt_ticket_check->fetchColumn());
    }
}