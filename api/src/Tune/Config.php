<?php
namespace Tune;

class Config {
    /** @var $instance Config */
    public static $instance;
    protected $params;

    public function __construct(array $params = null) {
        $this->params = $params;
    }

    /**
     * Slurps up a json file and attempts to initialize a Config singleton
     *
     * @param $file
     * @throws \Exception
     */
    public function load($file) {
        if (!file_exists($file)) {
            throw new \Exception('Invalid config file path.');
        }

        $config_data = file_get_contents($file);
        $this->init(json_decode($config_data, true));
    }

    /**
     * Initializes the Config singleton, for use throughout the application lifecycle.
     *
     * @param array $params
     * @throws \Exception
     */
    protected function init(array $params) {
        if (is_object(static::$instance)) {
            throw new \Exception('This singleton may only be loaded once.');
        }

        if (empty($params)) {
            throw new \Exception('This method requires an array of configuration params to initialize.');
        }

        $this->params = $params;

        static::$instance = $this;
    }

    /**
     * Static interface to the Config singleton
     *
     * @param string $param
     * @return array
     * @throws \Exception
     */
    public static function get($param = '') {
        if (!is_object(static::$instance)) {
            throw new \Exception('This method requires the Config singleton to be initialized.');
        }

        $instance = static::$instance;

        return $instance->getConfig($param);
    }

    /**
     * Looks like a getter to me.
     *
     * @param $param
     * @return array
     */
    protected function getConfig($param) {
        return empty($param) ? $this->params : $this->params[$param];
    }
}