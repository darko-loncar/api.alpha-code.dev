<?php
declare(strict_types=1);

use Core\Db;
use Core\Entity;
use Modules\QueryBuilder;

    $db = new Db(
        $config['connection'] ?? 'mysql',
        $config['host'] ?? 'localhost',
        $config['database'] ?? '',
        $config['username'] ?? '',
        $config['password'] ?? '',
        $config['port'] ?? 3306,
    );

    $pdo = $db->getPDO();

    QueryBuilder::setConnection($pdo);
    Entity::setConnection($pdo);
