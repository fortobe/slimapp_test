<?php
//Этот файл предоставляет возможность использования консольного интерфеса ORM, в целях осуществления миграции в частности.

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use UltraLite\Container\Container;

/** @var Container $container */
$container = require_once __DIR__ . '/bootstrap.php';

return ConsoleRunner::createHelperSet($container->get(EntityManager::class));