<?php
/** @var Composer\Autoload\ClassLoader $loader */
chdir(__DIR__ .'/../');
$loader = require('vendor/autoload.php');
if (!$loader) {
    throw new Exception('No Autoloading setup');
}
