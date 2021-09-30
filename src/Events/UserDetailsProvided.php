<?php

namespace Latus\Installer\Events;

use Illuminate\Foundation\Events\Dispatchable;

class UserDetailsProvided
{
    use Dispatchable;

    public function __construct(
        public array $attributes
    )
    {
    }
}