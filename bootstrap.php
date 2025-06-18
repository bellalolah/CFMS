<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'globals.php';

use Cfms\Config\DataInitializer;
use Cfms\Repositories\UserRepository;


$userRepo = new UserRepository();
$initializer = new DataInitializer($userRepo);
$initializer->initialize();