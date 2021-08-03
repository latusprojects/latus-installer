<?php


namespace Latus\Installer\Providers\Traits;


use Latus\Installer\Database\SeederRepository;

trait RegistersSeeders
{
    public function registerSeeders(array $classes)
    {
        (new SeederRepository())->register($classes);
    }
}