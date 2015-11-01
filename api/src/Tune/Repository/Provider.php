<?php
namespace Tune\Repository;

use Tune\Config;

class Provider {
    /**
     * Establishes a database connection
     *
     * @return \PDO
     * @throws \Exception
     */
    public static function pdo() {
        $db_config = Config::get('database');

        $instance = new \PDO(sprintf("%s:host=%s;dbname=%s;charset=utf8",
            $db_config['driver'],
            $db_config['host'],
            $db_config['database']
        ), $db_config['user'], $db_config['pass']);
        $instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $instance->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        return $instance;
    }
}