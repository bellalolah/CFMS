<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'globals.php';

use Cfms\Config\DataInitializer;


$userRepo = new \Cfms\Repositories\UserRepository();
$initializer = new DataInitializer($userRepo);
$initializer->initialize();