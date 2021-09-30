<?php

namespace Latus\Installer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Latus\Permissions\Models\User;

class DefaultUserCreated
{
    use Dispatchable;

    public function __construct(
        public User $user
    )
    {
    }

}