<?php

namespace Latus\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAppDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:255',
            'url' => 'required|url'
        ];
    }
}