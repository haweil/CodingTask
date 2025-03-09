<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShortenUrlRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'original_url' => 'required|url',
            'alias' => 'nullable|alpha_num|max:10|unique:short_urls,alias',
        ];
    }
    public function messages(): array
    {
        return [
            'original_url.required' => 'The original URL is required.',
            'original_url.invalid' => 'The original URL is invalid. Please enter a valid URL.',
            'alias_alpha.num' => 'The alias must only contain letters and numbers.',
            'alias.max' => 'The alias may not be greater than 10 characters.',
            'alias.unique' => 'The alias has already been taken. Please choose another one.',
        ];
    }
}
