<?php

namespace App\Rules;

use App\Domain\Nit\NitVerifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Regla de Laravel que rechaza un NIT con dígito de verificación incorrecto. */
class ValidNit implements ValidationRule
{
    /** Valida el NIT y deja el mensaje de error si no corresponde. */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! (new NitVerifier)->isValid($value)) {
            $fail('El NIT debe incluir un dígito de verificación válido.');
        }
    }
}
