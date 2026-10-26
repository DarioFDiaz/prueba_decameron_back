<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesHotelData;
use Illuminate\Foundation\Http\FormRequest;

/** Valida los datos cuando se edita un hotel. */
class UpdateHotelRequest extends FormRequest
{
    use ValidatesHotelData;

    /** La API no exige inicio de sesión. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Usa las reglas comunes y permite conservar el nombre, la dirección y el NIT actuales.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->hotelRules($this->hotelIdFromRoute());
    }
}
