<?php
namespace Tune\Repository\PDO;

use Tune\Repository\AdapterInterface;

abstract class Adapter implements AdapterInterface {
    /** @var $connection \PDO */
    protected $connection;
    /** @var $resource \PDOStatement */
    protected $resource; // iterable

    public function __construct(\PDO $connection) {
        if (is_null($connection)) {
            throw new \Exception('Unable to initialize an adapter without a connection!');
        }

        $this->connection = $connection;
    }

    /**
     * @param string $query
     */
    public function get($query) {
        $stmt = $this->connection->prepare($query);
        $stmt->execute();

        $this->resource = $stmt;
    }

    /**
     * @return array
     */
    public function fetchAll() {
        return $this->resource->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public function fetch() {
        return $this->resource->fetch(\PDO::FETCH_ASSOC);
    }
}