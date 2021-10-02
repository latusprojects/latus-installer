<?php

namespace Latus\Installer\Listeners;

use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Latus\Installer\Events\AppDetailsProvided;

class UpdateAppDetailsInEnvFile
{
    public function handle(AppDetailsProvided $event)
    {
        DotenvEditor::setKeys([
            'APP_NAME' => $event->details['name'],
            'APP_URL' => $event->details['url'],
        ]);

        DotenvEditor::save();
    }
}