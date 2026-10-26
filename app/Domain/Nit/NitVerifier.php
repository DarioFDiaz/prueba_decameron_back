<?php

namespace App\Domain\Nit;

/** Calcula y verifica el dígito de verificación del NIT. */
class NitVerifier
{
    /**
     * Pesos de la Orden Administrativa 4 de 1989 de la DIAN, aplicados de derecha a izquierda.
     *
     * @var list<int>
     */
    private const WEIGHTS = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];

    /** Indica si el NIT tiene formato válido y el dígito correcto. */
    public function isValid(string $nit): bool
    {
        if (! preg_match('/^\d{5,15}-\d$/', $nit)) {
            return false;
        }

        [$body, $checkDigit] = explode('-', $nit);

        return $this->checkDigit($body) === (int) $checkDigit;
    }

    /** Calcula el dígito de verificación a partir del número, sin el guion. */
    public function checkDigit(string $body): int
    {
        $sum = 0;

        foreach (array_reverse(str_split($body)) as $index => $digit) {
            $sum += (int) $digit * self::WEIGHTS[$index];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? $remainder : 11 - $remainder;
    }
}
