<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'      => '',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => 'MySQLi',   // <-- JANGAN DIKOSONGKAN
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    public function __construct()
    {
        parent::__construct();

        // FORCE override with .env
        $this->default['hostname'] = env('database.default.hostname', '127.0.0.1');
        $this->default['username'] = env('database.default.username', 'root');
        $this->default['password'] = env('database.default.password', '');
        $this->default['database'] = env('database.default.database', '');
        $this->default['DBDriver'] = env('database.default.DBDriver', 'MySQLi');
        $this->default['DBPrefix'] = env('database.default.DBPrefix', '');
        $this->default['port'] = (int) env('database.default.port', 3306);
    }
}
