<?php

namespace Latus\Installer\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AppDetailsProvided
{
    use Dispatchable;

    public const VALIDATION_RULES = [
        'name' => 'required|string|min:3|max:255',
        'url' => 'required|url'
    ];

    public function __construct(
        public array $attributes
    )
    {
    }
}