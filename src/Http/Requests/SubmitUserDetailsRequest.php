<?php

namespace Latus\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitUserDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|min:5|max:50',
            'email' => 'required|email',
            'password' => 'required|string|min:8|max:255|confirmed',
            'password_confirmation' => 'required'
        ];
    }
}