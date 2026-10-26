<?php

namespace Tests\Unit;

use App\Domain\Nit\NitVerifier;
use PHPUnit\Framework\TestCase;

/** Comprueba el dígito de verificación del NIT sin usar base de datos. */
class NitVerifierTest extends TestCase
{
    private NitVerifier $verifier;

    /** Prepara el verificador antes de cada prueba. */
    protected function setUp(): void
    {
        parent::setUp();

        $this->verifier = new NitVerifier;
    }

    /** Acepta un NIT real de nueve dígitos con su dígito correcto. */
    public function test_it_accepts_a_known_nine_digit_nit(): void
    {
        $this->assertTrue($this->verifier->isValid('860324218-1'));
    }

    /** El ejemplo 12345678 tiene dígito 8, no 9. */
    public function test_it_accepts_the_sample_body_with_its_real_check_digit(): void
    {
        $this->assertSame(8, $this->verifier->checkDigit('12345678'));
        $this->assertTrue($this->verifier->isValid('12345678-8'));
    }

    /** Rechaza el NIT cuando el dígito de verificación no coincide. */
    public function test_it_rejects_a_wrong_check_digit(): void
    {
        $this->assertFalse($this->verifier->isValid('12345678-9'));
    }

    /** Rechaza un NIT que no trae el guion antes del dígito. */
    public function test_it_rejects_a_nit_without_the_separator(): void
    {
        $this->assertFalse($this->verifier->isValid('123456788'));
    }
}
