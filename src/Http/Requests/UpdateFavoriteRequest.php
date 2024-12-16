<?php

namespace Ofaws\Favorite\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFavoriteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'position' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
