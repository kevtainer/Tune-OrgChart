<?php
namespace Tune\Repository;

class Factory {
    protected $provider = null;
    protected $connection = null;

    public function __construct(callable $provider) {
        $this->provider = $provider;
    }

    /**
     * Builds an Adapter with a connection for use by the data modelers.
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function create($name) {
        if ($this->connection === null) {
            if (!is_callable($this->provider)) {
                throw new \Exception("Unable to instantiate provider.");
            }

            $this->connection = call_user_func($this->provider);
        }

        return new $name($this->connection);
    }
}
