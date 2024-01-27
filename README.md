# Rest-full Mail

## About Rest-full Mail

Rest-full Mail is a small part of the Rest-Full framework.

You can find the application at: [rest-full/app](https://github.com/rest-full/app) and you can also see the framework skeleton at: [rest-full/rest-full](https://github.com/rest-full/rest-full).

## Installation

* Download [Composer](https://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
* Run `php composer.phar require rest-full/mail` or composer installed globally `compser require rest-full/mail` or composer.json `"rest-full/mail": "1.0.0"` and install or update.

## Usage

This Mail
```
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
        'pass' => ''
    ]
))->addressing(
    ['email' => 'simaowebsolutions@yahoo.com', 'name' => 'Simão Web Solutions'],
    ['email' => 'joselbsimao19@yahoo.com', 'name' => 'José Luis']
)->send('test','teste');
```
## License

The rest-full framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).