<?php
function slugify($text) {
    $turkish = array('ı', 'İ', 'ş', 'Ş', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç');
    $english = array('i', 'i', 's', 's', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c');

    // 1. Türkçe karakterleri İngilizce karşılıkları ile değiştir
    $text = str_replace($turkish, $english, $text);

    // 2. Metni küçük harfe çevir
    $text = strtolower($text);

    // 3. Harf ve rakam olmayan her şeyi boşlukla değiştir
    $text = preg_replace('/[^a-z0-9]+/', ' ', $text);

    // 4. Kenarlardaki boşlukları temizle
    $text = trim($text);

    // 5. Boşlukları alt çizgi (_) ile değiştir
    $text = preg_replace('/\s+/', '_', $text);

    return $text;
}

function format_turkish_date($datetime_string) {
    $timestamp = strtotime($datetime_string);
    
    // İngilizce gün ve ay isimleri dizileri
    $english_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    $turkish_days = array('Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar');
    
    $english_months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $turkish_months = array('Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık');
    
    // Tarihi 'd F Y, l H:i' formatında İngilizce olarak al
    // Örnek Çıktı: 11 November 2025, Tuesday 12:12
    $formatted_date = date('d F Y, l H:i', $timestamp);
    
    // İngilizce gün ve ay isimlerini Türkçe karşılıkları ile değiştir
    $formatted_date = str_replace($english_months, $turkish_months, $formatted_date);
    $formatted_date = str_replace($english_days, $turkish_days, $formatted_date);
    
    return $formatted_date;
}

?>