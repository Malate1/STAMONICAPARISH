<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Copy this file to application/config/secrets.php on each environment and
 * replace the value with a cryptographically random key.
 *
 * Example generator:
 *   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
 */
return [
    'encryption_key' => 'REPLACE_WITH_A_RANDOM_64_HEX_CHARACTER_KEY',
];
