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
?>