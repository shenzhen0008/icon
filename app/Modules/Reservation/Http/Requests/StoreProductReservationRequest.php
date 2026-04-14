<?php

namespace App\Modules\Reservation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
