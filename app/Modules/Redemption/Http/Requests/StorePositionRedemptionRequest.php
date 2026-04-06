<?php

namespace App\Modules\Redemption\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRedemptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
