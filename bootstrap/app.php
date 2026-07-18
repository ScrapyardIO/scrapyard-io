<?php

<<<<<<< Updated upstream
use BareMetal\Core\Scrapyard;

return Scrapyard::setup(basePath: dirname(__DIR__))
=======
use Fabricate\Core\Machine;

return Machine::configure(basePath: dirname(__DIR__))
    //->withMiddleware(function($middleware): void {})
    //->withExceptions(function($exceptions): void {})
>>>>>>> Stashed changes
    ->create();
