<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

static $envLoaded = false;

if (!$envLoaded) {
    $envPath = dirname(__DIR__);
    if (is_readable($envPath . '/.env')) {
        Dotenv::createImmutable($envPath)->safeLoad();
    }
    $envLoaded = true;
}
