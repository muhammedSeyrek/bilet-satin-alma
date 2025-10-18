<?php
use PHPUnit\Framework\TestCase;

// Test edeceğimiz fonksiyonları dosyamıza dahil ediyoruz
require_once __DIR__ . '/../fonksiyonlar.php';

class FonksiyonlarTest extends TestCase
{
    public function test_slugify_turkce_karakterleri_dogru_cevirir(): void
    {
        $this->assertEquals("sekerli_cay", slugify("Şekerli Çay"));
    }

    // --- YENİ EKLENEN TEST ---
    public function test_format_turkish_date_tarihi_dogru_formatlar(): void
    {
        // Test senaryosu: Teknik bir tarih girdisi, kullanıcı dostu bir çıktı veriyor mu?
        $giris = "2025-11-11T12:12";
        $beklenen_cikti = "11 Kasım 2025, Salı 12:12";

        $gercek_cikti = format_turkish_date($giris);

        // İddia: Gerçek çıktının beklenen çıktıya eşit olduğunu iddia ediyoruz.
        $this->assertEquals($beklenen_cikti, $gercek_cikti);
    }
}