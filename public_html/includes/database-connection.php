<?php

$type     = 'mysql';
$server   = '192.185.2.183';
$db       = 'matthewb_csc436';
$port     = '3306';
$charset  = 'utf8mb4';

$username = 'matthewb_user';
$password = 'xujci0-datgoz-Censab';

// PDO options
$options  = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$dsn = "$type:host=$server;dbname=$db;port=$port;charset=$charset";

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), $e->getCode());
}

/**
 * Executes an SQL query using PDO, optionally binding parameters.
 *
 * @param PDO $pdo                      PDO instance.
 * @param string $sql                   SQL query.
 * @param array|null $arguments         Optional query parameters.
 * @return PDOStatement                 PDOStatement result.
 */
function pdo(PDO $pdo, string $sql, array $arguments = null)
{
    if (!$arguments) {
        return $pdo->query($sql);
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($arguments);
    return $statement;
}