<?php
include __DIR__. "/../vendor/autoload.php";

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/../database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/../database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => isset($_SERVER["PHINX_DBNAME"]) ? $_SERVER["PHINX_DBNAME"] : DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASSWORD,
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => isset($_SERVER["PHINX_DBNAME"]) ? $_SERVER["PHINX_DBNAME"] : DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASSWORD,
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'auth' => [
            'adapter' => 'mysql',
            'host' => AUTH_DB_HOST,
            'name' => AUTH_DB_NAME,
            'user' => AUTH_DB_USER,
            'pass' => AUTH_DB_PASSWORD,
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'users_log' => [
            'adapter' => 'mysql',
            'host' => LOG_DB_HOST,
            'name' => USERS_LOG_DB_NAME,
            'user' => LOG_DB_USER,
            'pass' => LOG_DB_PASSWORD,
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'robots_log' => [
            'adapter' => 'mysql',
            'host' => LOG_DB_HOST,
            'name' => ROBOTS_LOG_DB_NAME,
            'user' => LOG_DB_USER,
            'pass' => LOG_DB_PASSWORD,
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'api_log' => [
            'adapter' => 'mysql',
            'host' => LOG_DB_HOST,
            'name' => API_LOG_DB_NAME,
            'user' => LOG_DB_USER,
            'pass' => LOG_DB_PASSWORD,
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASSWORD,
            'port' => '3306',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
