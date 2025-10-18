<?php
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    // Bu test, basitçe 'true'nun 'true' olup olmadığını kontrol eder.
    // Amacı, test altyapımızın doğru çalışıp çalışmadığını doğrulamaktır.
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}