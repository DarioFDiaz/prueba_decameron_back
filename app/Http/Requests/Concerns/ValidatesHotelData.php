<?php

namespace App\Http\Requests\Concerns;

use App\Domain\Hotels\AccommodationRules;
use App\Models\Hotel;
use App\Rules\ValidNit;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Reglas compartidas al crear o editar un hotel. */
trait ValidatesHotelData
{
    /** Quita espacios al inicio y al final de los textos antes de validar. */
    protected function prepareForValidation(): void
    {
        $accommodations = $this->input('accommodations');

        $this->merge([
            'name' => $this->trimmed('name'),
            'address' => $this->trimmed('address'),
            'city' => $this->trimmed('city'),
            'nit' => $this->trimmed('nit'),
            'accommodations' => is_array($accommodations) ? array_values($accommodations) : $accommodations,
        ]);
    }

    /**
     * Reglas de los datos del hotel. En edición ignora el propio registro.
     *
     * @return array<string, mixed>
     */
    protected function hotelRules(?int $hotelId): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('hotels', 'name')->ignore($hotelId)],
            'address' => ['required', 'string', 'max:255', Rule::unique('hotels', 'address')->ignore($hotelId)],
            'city' => ['required', 'string', 'max:255'],
            'nit' => ['required', 'string', 'max:20', Rule::unique('hotels', 'nit')->ignore($hotelId), new ValidNit],
            'room_count' => ['required', 'integer', 'min:1'],
            'accommodations' => ['required', 'array', 'min:1'],
            'accommodations.*.room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'accommodations.*.accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'accommodations.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Mensajes de error que ve el usuario.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar :max caracteres.',
            'name.unique' => 'Ya existe un hotel con este nombre.',
            'address.required' => 'La dirección es obligatoria.',
            'address.max' => 'La dirección no puede superar :max caracteres.',
            'address.unique' => 'Ya existe un hotel con esta dirección.',
            'city.required' => 'La ciudad es obligatoria.',
            'city.max' => 'La ciudad no puede superar :max caracteres.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.max' => 'El NIT no puede superar :max caracteres.',
            'nit.unique' => 'Ya existe un hotel con este NIT.',
            'room_count.required' => 'El número de habitaciones es obligatorio.',
            'room_count.integer' => 'El número de habitaciones debe ser un entero.',
            'room_count.min' => 'El número de habitaciones debe ser al menos :min.',
            'accommodations.required' => 'Debes asignar al menos una acomodación.',
            'accommodations.array' => 'Las acomodaciones no tienen un formato válido.',
            'accommodations.min' => 'Debes asignar al menos una acomodación.',
            'accommodations.*.room_type_id.required' => 'El tipo de habitación es obligatorio.',
            'accommodations.*.room_type_id.integer' => 'El tipo de habitación no es válido.',
            'accommodations.*.room_type_id.exists' => 'El tipo de habitación no existe.',
            'accommodations.*.accommodation_id.required' => 'La acomodación es obligatoria.',
            'accommodations.*.accommodation_id.integer' => 'La acomodación no es válida.',
            'accommodations.*.accommodation_id.exists' => 'La acomodación no existe.',
            'accommodations.*.quantity.required' => 'La cantidad es obligatoria.',
            'accommodations.*.quantity.integer' => 'La cantidad debe ser un entero.',
            'accommodations.*.quantity.min' => 'La cantidad debe ser al menos :min.',
        ];
    }

    /** Revisa combinaciones repetidas, acomodaciones no permitidas y el máximo del hotel. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty() || ! is_array($this->input('accommodations'))) {
                return;
            }

            app(AccommodationRules::class)->apply(
                $validator,
                $this->input('accommodations'),
                (int) $this->input('room_count')
            );
        });
    }

    /** Obtiene el id del hotel que se está editando. */
    protected function hotelIdFromRoute(): ?int
    {
        $hotel = $this->route('hotel');

        if ($hotel instanceof Hotel) {
            return $hotel->id;
        }

        return $hotel ? (int) $hotel : null;
    }

    /** Recorta un campo de texto si llegó como cadena. */
    private function trimmed(string $key): mixed
    {
        $value = $this->input($key);

        return is_string($value) ? trim($value) : $value;
    }
}
