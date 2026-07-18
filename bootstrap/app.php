<?php

use Fabricate\Core\Machine;

return Machine::configure(basePath: dirname(__DIR__))
    //->withMiddleware(function($middleware): void {})
    //->withExceptions(function($exceptions): void {})
    ->create();
