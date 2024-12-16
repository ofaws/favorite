<?php

namespace Ofaws\Favorite\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Ofaws\Favorite\Favorite;

class StoreFavoriteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::when($this->isMethod('POST'), ['exists:users,id']),
                Rule::when($this->isMethod('PUT'), ['exclude']),
            ],
            'asset_id' => ['sometimes', 'integer', Favorite::assetTableNameRule($this->asset_type)],
            'asset_type' => ['sometimes', 'string', 'max:255', Favorite::allowedAssetsRule()],
            'position' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['user_id' => auth()->id()]);
    }
}
