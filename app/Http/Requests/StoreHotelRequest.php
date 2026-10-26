<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesHotelData;
use Illuminate\Foundation\Http\FormRequest;

/** Valida los datos cuando se crea un hotel. */
class StoreHotelRequest extends FormRequest
{
    use ValidatesHotelData;

    /** La API no exige inicio de sesión. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Usa las reglas comunes de hotel, sin excluir ningún registro.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->hotelRules(null);
    }
}
