<?php

namespace Latus\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Latus\Installer\Events\DatabaseDetailsProvided;

class CheckDatabaseDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return DatabaseDetailsProvided::VALIDATION_RULES;
    }
}