<?php
declare(strict_types=1);


return [

    'toubilib.db.driver'   => $_ENV['lib.driver'],
    'toubilib.db.host'     => $_ENV['lib.host'],
    'toubilib.db.database' => $_ENV['lib.database'],
    'toubilib.db.username' => $_ENV['lib.username'],
    'toubilib.db.password' => $_ENV['lib.password'],

    'cors.origine'  => '*',
    'cors.methodes' => 'GET, POST, OPTIONS',
    'cors.headers'  => 'Content-Type, Accept, Authorization',

];
