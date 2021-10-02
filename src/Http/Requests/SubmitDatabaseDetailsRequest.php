<?php

namespace Latus\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitDatabaseDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'host' => 'required|string|min:5',
            'username' => 'required|string|min:3|max:16',
            'database' => 'required|string|min:3|max:54',
            'password' => 'required|string|min:6|max:32',
            'port' => 'required|integer|min:0|max:65535',
            'driver' => 'required|in:mysql,postgres,sqlite,sqlsrv',
            'prefix' => 'sometimes|string|max:10',
        ];
    }
}