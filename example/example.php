<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__.'/../config/pathServer.php';

use Restfull\Mail\Email;


(new Email(
    [
        'linguage' => 'br',
        'html' => true,
        'host' => '',
        'port' => 587,
        'SMTP' => ['auth' => true, 'secure' => false, 'debug' => 0],
        'user' => '',
        'pass' => '',
        'active'=>true
    ]
))->addressing(
    ['email' => 'simaowebsolutions@yahoo.com', 'name' => 'Simão Web Solutions'],
    ['email' => 'joselbsimao19@yahoo.com', 'name' => 'José Luis']
)->send('test','teste');